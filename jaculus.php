<?php

require 'vendor/autoload.php';

use Jaculus\IModule;
use Jaculus\RouteDispatcher;
use Jaculus\Template\FileSystemTemplate;
use Jaculus\UserPermissions;
use Jaculus\ModuleHelper;
use Jaculus\DI;
use Jaculus\Module;
use Jaculus\ModuleInstruction;
use Jaculus\ArrayWrapper;
use Jaculus\WantedIndicesStore;
use Jaculus\LazyModuleProcessorStore;
use Jaculus\LazyModuleProcessor;
use Jaculus\LazyModuleLoaderGenerator;
use Jaculus\ModuleListBuilder;
use Jaculus\SessionArrayWrapper;
use Jaculus\AppUpdateChecker;
use Jaculus\ErrorHandlerStore;
use Jaculus\Request;

use DI\ContainerBuilder;
use DI\Container;


$module_list = includeModules();
$permissions = includePermissions();
$global_instructions = includeGloballyAffectingModules();

DI::setupJaculus(__DIR__, $permissions);
$wanted_url_vars = new WantedIndicesStore();
$r = includeRoutes($wanted_url_vars);

$error_handlers = includeErrorHandlers();

final_dispatch($r, $module_list, $global_instructions, $wanted_url_vars, $error_handlers);




function final_dispatch(RouteDispatcher $dispatcher, ModuleListBuilder $module_list, array $global_instructions, WantedIndicesStore $wanted_url_vars, ErrorHandlerStore $error_handlers) {
    
    //Maintenance mode
    if(DI::get('app.maintenance_mode')) {
        $handler_fun = $dispatcher->get_maintenance();
        if($handler_fun) {
            $dispatch_result = $handler_fun();
            run_route_handler($module_list, $global_instructions, $dispatch_result, $wanted_url_vars);
        } else {
            echo 'Currently under maintenance';
        }
        return;
    }
    
    
    //Dispatch route
    $request = DI::get(Request::class);
    $dispatch = $dispatcher->dispatch($request->method, $request->uri);

    //Check if app php files has changed. 
    //If that is the case then each modules install function will be called
    $app_updater = new AppUpdateChecker(
        __DIR__ . '/cache/jaculus/app_stamps.php',
        [
            __DIR__ . '/app/module_register.php',
            __DIR__ . '/app/permissions.php',
            __DIR__ . '/app/routes.php',
            __DIR__ . '/app/error.php'
        ]
    );
    $app_has_changed = $app_updater->hasChanged();

    //Install module if app has changed
    if($app_has_changed) {
        foreach($module_list->getAll() as $module) {
            if(!($module instanceof Module))
                throw new Exception("Module with name $module is not an instance of class Module");
            
            $module->install();
        }
    }


    try {
        //Switch on success
        switch($dispatch[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                run_error_route_handler(
                    $module_list,
                    $global_instructions,
                    $dispatcher->get_not_found(),
                    $wanted_url_vars,
                    "HTTP status 404."
                );
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                run_error_route_handler(
                    $module_list,
                    $global_instructions,
                    $dispatcher->get_method_not_allowed(),
                    $wanted_url_vars,
                    "HTTP status 405."
                );
                break;
            case FastRoute\Dispatcher::FOUND:
                $route_handler = $dispatch[1];
                $url_vars = $dispatch[2];
                $request->uri_vars = $url_vars;
                
                //Get dispatch template name and module instructions
                $dispatch_result = $route_handler();

                //Execute modules with provided input
                //Collect output and assign it to template variables
                run_route_handler($module_list, $global_instructions, $dispatch_result, $wanted_url_vars, $url_vars);
                break;
        }
    } catch(\Exception $e) {
        run_error_route_handler(
            $module_list,
            $dispatcher->get_server_error(),
            $wanted_url_vars,
            "Internal server error"
        );
        $error_handlers->run_exception_handler($e);
    }
}

function run_error_route_handler(ModuleListBuilder $module_list, array $global_instructions, $handler_fun, WantedIndicesStore $wanted_url_vars, $default_error_msg)
{
    if($handler_fun)
    {
        $dispatch_result = $handler_fun();
        run_route_handler($module_list, $global_instructions, $dispatch_result, $wanted_url_vars);
    }
    else
    {
        throw new Exception($default_error_msg);
    }
}

function run_route_handler(ModuleListBuilder $module_list, array $global_instructions, $dispatch_result, WantedIndicesStore $wanted_url_vars, $url_vars = []) {
    $local_template_vars = interpret_modules($module_list, $dispatch_result->moduleInstructions(), $wanted_url_vars, $url_vars);
    $global_template_vars = interpret_modules($module_list, $global_instructions, $wanted_url_vars, $url_vars);
    $template_vars = array_merge($local_template_vars, $global_template_vars);

    //Render
    $twig = DI::get(Twig_Environment::class);
    echo $twig->render(
        $dispatch_result->templateName(), 
        $template_vars);
}

function interpret_modules(ModuleListBuilder $module_list, array $module_instructions, WantedIndicesStore $wanted_url_vars, $url_vars) {
    $lazy_modules = DI::get(LazyModuleProcessorStore::class);
    $generator = DI::get(LazyModuleLoaderGenerator::class);
    
    $template_vars = [];
    $mods = $module_list->getAll();
    foreach($module_instructions as $variable => $instruction) {
        if(!$instruction instanceof ModuleInstruction)
            throw new Exception("A module instruction is not an instance of ModuleInstruction");
        
        //Get module
        $name = $instruction->name();
        if(!isset($mods[$name]))
            throw new Exception("There are no module named $name");
            
        $module = $mods[$name];
        if(!($module instanceof Module))
            throw new Exception("Module with name \"$module\" is not an instance of class Jaculus\\Module");

        //Transform url objects into actual variables
        $input = $instruction->input();
        $wanted_url_vars->replaceWantedIndices([$url_vars], $input);

        if($module->lazy) {
            //The module is lazy, schedule it then for processing and code generation
            if($lazy_modules->has($variable))
                throw new Exception("Output variable \"$variable\" is defined several times");
            $lazy_modules->add($variable, new LazyModuleProcessor($module, $input));
            $generator->addModule($variable);
        } else {
            //Process module instantly
            $module->beforeProcessing();
            $output = $module->process($input);
            if(!is_string($variable))
                throw new Exception("Output key of module with name $name is not defined even though it spites out output");
                
            if(array_key_exists($variable, $template_vars))
                throw new Exception("Output variable \"$variable\" is defined several times");
            $template_vars[$variable] = $output;
        }
    }

    return $template_vars;
}

function includeModules() {
    $m = new ModuleListBuilder();
    require 'app/module_register.php';
    return $m;
}

function includePermissions() {
    $p = [];
    require 'app/permissions.php';
    return $p;
}

function includeGloballyAffectingModules() {
    $m = new ModuleHelper();
    return require 'app/global_modules.php';
}

function includeRoutes(WantedIndicesStore $urlVar) {
    //Modules and routes
    $m = new ModuleHelper();
    $r = DI::get(RouteDispatcher::class);

    //Super globals
    //TODO: Rename to global_vars, server_vars etc...
    //      Make sure they are synced with Module class
    $globals    = DI::get('$GLOBALS');
    $server     = DI::get('$_SERVER');
    $get        = DI::get('$_GET');
    $post       = DI::get('$_POST');
    $files      = DI::get('$_FILES');
    $cookie     = DI::get('$_COOKIE');
    $session    = DI::get('$_COOKIE');
    $request    = DI::get('$_REQUEST');
    $env        = DI::get('$_ENV');

    require 'app/routes.php';
    require 'app/special_routes.php';
    return $r;
}

function includeErrorHandlers() {
    $error = DI::get(ErrorHandlerStore::class);
    require 'app/error.php';
    $error->setup_fatal_error_handler();
    return $error;
}