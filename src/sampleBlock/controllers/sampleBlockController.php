<?php

namespace Eiron\sampleBlock\controllers;

use Eiron\framework\controllers\baseController;

class sampleBlockController extends baseController {

    public function __construct() {
        $this->directory = __DIR__;
    }
    
    public function sampleHomepageController() {
        echo $this->render('sample.html.twig');
    }
}