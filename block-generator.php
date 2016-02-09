<?php

echo "\nYou are trying to generate a new block.\n\n";
echo "Block name (should be camelCase and end with 'Block') : ";

$name = trim(str_replace(' ', '', fgets(STDIN)));
$controllerName = $name . 'Controller';

if (mkdir(__DIR__ . '/src/' . $name, 0774, true)) {
    mkdir(__DIR__ . '/src/' . $name . '/controllers', 0774, true);
    mkdir(__DIR__ . '/src/' . $name . '/views', 0774, true);

    touch(__DIR__ . '/src/' . $name . '/routing.yml');
    $controllerContent = 
"<?php

namespace Eiron\\$name\\controllers;

use Eiron\\framework\\controllers\\baseController;

class $controllerName extends baseController {

    public function __construct() {
        \$this->directory = __DIR__;
    }
}";
    $controller = fopen(__DIR__ . '/src/' . $name . '/controllers/' . $name . 'Controller.php', 'x');
    fwrite($controller, $controllerContent);
    fclose($controller);
    echo "\n\nThe block has been successfully created.";
} else {
    echo "\n\nThis block already exists.";
}

/*! \file block-generator.php
    \brief Call this script to generate a new block.

    Use "php block-generator.php" to start the block creation. You will have to provide a name for it, which should be camelCased and ended by "Block". It will create a directory in the /src, containing an empty controller and a routing.yml file, used in the same way as the global one /config/routing.yml.
*/