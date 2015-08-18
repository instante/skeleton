<?php

namespace App\Routing;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{

    /**
     * @return \Nette\Application\IRouter
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
                $router[] = new Route('[<? .*>/]log/<filename [a-z0-9-]+>.html', function ($presenter, $filename) {
                    $path = realpath(__DIR__ . '/../../log/' . $filename . '.html');
                    if (file_exists($path)) {
                        return new \Nette\Application\Responses\TextResponse(file_get_contents($path));
                    }
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
