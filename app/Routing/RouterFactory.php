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

            //router for live tracy errors
            if ($isDebugMode) {
                /** @noinspection PhpUnusedParameterInspection */
                $router[] = new Route('[<? .*>/]log/<filename [a-z0-9-]+>.html', function ($presenter, $filename) {
                    $path = realpath(__DIR__ . '/../../log/' . $filename . '.html');
                    return new TextResponse(file_exists($path) ? file_get_contents($path) : 'exception file not found');
                });
            }

            $router[] = new Route('<presenter>/<action>[/<id>]', array(
                'presenter' => 'Homepage',
                'action' => 'default',
                'id' => NULL,
            ), $secureFlag);


        }

        return $router;
    }

}
