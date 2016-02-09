<?php

namespace Eiron\sampleBlock\controllers;

use Eiron\framework\controllers\baseController;

class sampleBlock\Controller extends baseController {

    public function __construct() {
        $this->directory = __DIR__;
    }
    
    public function testHomepageController() {
        echo "Hello world !\n";
        echo "Now you should try to create your own block and controllers !";
    }
}