<?php

function loadComposer($baseDir)
{
    $autoloadPath = $baseDir . '/vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        header('content-type:text/plain;charset=utf-8');
        die("\nComposer was not installed, you need to run \"composer install\" first\n\n");
    }

    require_once $autoloadPath;
}
