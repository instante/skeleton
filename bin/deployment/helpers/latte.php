<?php
function latte($template, $params = [])
{
    (new \Latte\Engine())
        ->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../templates'))
        ->render($template . '.latte', $params);
}
