<?php

namespace App\Routing;

use Nette\Application\IRouter;
use Nette\Application\Responses\TextResponse;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{

    /**
     * @param bool $isConsoleMode
     * @param bool $useSecureRoutes
     * @param bool $isDebugMode
     * @return IRouter
     */
    public function createRouter($isConsoleMode = FALSE, $useSecureRoutes = TRUE, $isDebugMode = FALSE)
    {
        $router = new RouteList();

        if (!$isConsoleMode) {
            $secureFlag = $useSecureRoutes ? Route::SECURED : 0;

            $router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);

            $router[] = new Route('example/<presenter>/<action>[/<id>]', array(
                'module' => 'Example',
                'presenter' => 'Homepage',
                'action' => 'default',
                'id' => NULL,
            ), $secureFlag);

            $router[] = new Route('<presenter>/<action>[/<id>]', array(
                'presenter' => 'Homepage',
                'action' => 'default',
                'id' => NULL,
            ), $secureFlag);
        }

        return $router;
    }

}
