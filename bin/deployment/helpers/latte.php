<?php
function latte($template, $params = [])
{
    (new \Latte\Engine())
        ->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../templates'))
        ->render($template . '.latte', [
                'colorPrLight' => '#4dbbe5',
                'colorPrDark' => '#182f3c',
            ] + $params);
}
