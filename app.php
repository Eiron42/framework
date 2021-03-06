<?php
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('log_errors',TRUE);
ini_set('html_errors',FALSE);
ini_set('error_log', __DIR__ . '/logs/error.log');
//ini_set('display_errors',FALSE);

session_start();

$request = print_r($_REQUEST, true);
$access = fopen(__DIR__ . '/logs/access.log', 'a');
$date = date('Y-m-d H:i:s');
fwrite($access, "[$date] " . $_SERVER['REQUEST_URI'] . ' : ' . $request);
fclose($access);

// Composer autoload
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$path = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '/';
$path = (substr($path, -1) === '/') ? substr($path, 0, -1) : $path;

$currentBlock = null;
$currentRoute = null;

// Getting global routing and list of blocks' routing to include
$globalRouting = Yaml::parse(file_get_contents(__DIR__ . '/config/routing.yml'));

foreach ($globalRouting['blocksRouting'] as $block => $prefix) {
    $blockRoutes = Yaml::parse(file_get_contents(__DIR__ . '/src/' . $block . '/routing.yml'));
    foreach ($blockRoutes as $route => $data) {
        $data['path'] = $prefix . $data['path'];
        $data['path'] = (substr($data['path'], -1) === '/') ? substr($data['path'], 0, -1) : $data['path'];
        $blockRoutes[$route] = $data;
        if ($path === $data['path']) {
            $currentBlock = $block;
            $currentRoute = $data;
            break;
        }
    }
}
if ($currentRoute === null) {
    $currentRoute = 404;
    echo '404 not found';
} else {
    $controller = 'Eiron\\' . $currentBlock . '\controllers\\' . $currentBlock . 'Controller';
    $controller = new $controller();
    $controller = $controller->$currentRoute['controller'](); 
}

//var_dump($currentRoute);
