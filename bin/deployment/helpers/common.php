<?php
function redirectToProject()
{
    $uri = $_SERVER['REQUEST_URI'];
    $path = preg_replace('~\?.*$~', '', $uri);
    $initPathRegex = '~/bin/deployment/[a-z]+-project\.php$~';
    if (preg_match($initPathRegex, $path)) {
        $uri = preg_replace($initPathRegex, '/www/', $path);
    }
    header('location: ' . $uri);
}
