<?php

namespace Eiron\framework\controllers;

/** \brief A class extended by all controllers with all generic methods.
*
* This class gathers all the generic methods used by all the blocks controllers. All block controllers are supposed to extend this class.
*/
class baseController {

    protected $directory; /**< The directory of the child controller. It is setted automatically by the child controller's __construct method. */

    /** \brief Echo the return value of this function to display the specified template.
    *
    * This method returns the template named $templateName, using the $params array to set the template variables, using TWIG. To display the template, echo the value returned by this method.
    */
    public function render($templateName, array $params) {
        $loader = new \Twig_Loader_Filesystem($this->directory . '/../views/');
        $twig = new \Twig_Environment($loader, array(
            'cache' => '/cache/templates/',
        ));
        
        return $twig->render($templateName, $params);
    }
}