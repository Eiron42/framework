<?php

// Composer autoload
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$path = $_SERVER['PATH_INFO'];
$currentBlock = null;
$currentRoute = null;

// Getting global routing and list of blocks' routing to include
$globalRouting = Yaml::parse(file_get_contents(__DIR__ . '/config/routing.yml'));
//$routes['global'] = $globalRouting['routing'];

foreach ($globalRouting['blocksRouting'] as $block => $prefix) {
    $blockRoutes = Yaml::parse(file_get_contents(__DIR__ . '/src/'. $block .'/routing.yml'));
    foreach ($blockRoutes as $route => $data) {
        $data['path'] = $prefix . $data['path'];
        $blockRoutes[$route] = $data;
        if ($path === $data['path']) {
            $currentBlock = $block;
            $currentRoute = $data;
            break;
        }
    }
    if ($currentRoute === null) {
        $currentRoute = 404;
    }
}

var_dump($currentRoute);