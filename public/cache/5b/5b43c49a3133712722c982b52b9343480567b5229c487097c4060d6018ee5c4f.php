<?php

/* welcome.html */
class __TwigTemplate_aff1e2445ade3407036e3ed513147074ffc963523ac602654d4dba274a8b6f00 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'user' => array($this, 'block_user'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<?php\\n ?>\\n<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <meta charset=\"utf-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">

        <title>Jaculus</title>

        <!-- Fonts -->
        <link href=\"https://fonts.googleapis.com/css?family=Raleway:100,600\" rel=\"stylesheet\" type=\"text/css\">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class=\"flex-center position-ref full-height\">
                <div class=\"top-right links\">
                    ";
        // line 70
        $phpkitty_permission_value = Jaculus\UserPermissions::getCurrent();
        if($phpkitty_permission_value >= 1) {
            echo "You are logged in";
        }

        // line 71
        echo "                </div>

            <div class=\"content\">
                <div class=\"title m-b-md\">
                    Jaculus ";
        // line 75
        echo twig_escape_filter($this->env, ($context["url"] ?? null), "html", null, true);
        echo "
                </div>

                <div class=\"links\">
                    <a href=\"https://github.com/Kickupx/Jaculus\">GitHub</a>
                </div>
            </div>
        </div>
    </body>
</html>
";
    }

    // line 70
    public function block_user($context, array $blocks = array())
    {
        echo "You are logged in";
    }

    public function getTemplateName()
    {
        return "welcome.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  118 => 70,  103 => 75,  97 => 71,  91 => 70,  20 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "welcome.html", "/home/bot/app/templates/welcome.html");
    }
}
