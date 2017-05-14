<?php

/**
 * TODO:
 * Add your own modules here
 */

 class OutputVariable extends Jaculus\Module {
     public $lazy = false;

     public function process(array $input) {
         return $input[0];
     }
 }
 
 class Test extends Jaculus\Module {
	public function process(array $input) {
		$perm = Jaculus\DI::get(Jaculus\UserPermissions::class);
		return $perm->getCurrentName();
	}
 }

 class FailingModule extends Jaculus\Module {
     public function process(array $input) {
         throw new Exception("From failing module");
     }
 }

 $m->add('output_var', new OutputVariable());
 $m->add('current_permission', new Test());
 $m->add('failing_module', new FailingModule());