<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Routing\Route;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {

        //https://doc.nette.org/cs/3.1/routing

        $router = new RouteList;
        $router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
        $router->addRoute('<presenter>/about<action>[/<id>]', 'About:default');
        $router->addRoute('<presenter>/portfolio<action>[/<id>]', 'Portfolio:default');
        $router->addRoute('<presenter>/blog<action>[/<id>]', 'Blog:default');
        $router->addRoute('<presenter>/contact<action>[/<id>]', 'Contact:default');
        $router->addRoute('<presenter>/setting<action>[/<id>]', 'Setting:default');
        //Auth
        $router->addRoute('<presenter>/login<action>[/<id>]', 'Login:default');
        $router->addRoute('<presenter>/register<action>[/<id>]', 'Register:default');

        return $router;
    }
}
