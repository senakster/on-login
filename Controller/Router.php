<?php
namespace Controller;
use Config;
use Config\Endpoints;
require_once PROJECT_ROOT_PATH . "Config/Endpoints.php";
require_once PROJECT_ROOT_PATH . "Controller/BaseController.php";

/**
 * Assumes Filenames /Controller/[controller name]Controller.php and class names [Controller name]Controller 
 */
class Router extends BaseController{
    var $endPoints;
    var $uri;
    var $controller;
    
    function __construct() {
        $this->endPoints = (new Endpoints)->valid_paths; 
        $this->uri = explode( '/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            if (($key = array_search(Config\APP_NAME, $this->uri)) !== false) {
            unset($this->uri[$key]);
            $this->uri = array_values($this->uri);
            }

        $this->Route();
    }


    private function Route(){
        // if ($this->validateRequest()) {
            $this->controller = $this->loadController($this->uri[1]);
            if (isset($this->uri[2]) && $this->uri[2] !== null) {
                $method = $this->uri[2] . 'Action';
                // var_dump($method);
                $this->controller->$method();
            }
        // }
    }

    /**
     * Is the request a valid endpoint 
     */
    private function validateRequest() {
        $valid = true;
        // var_dump($this->uri);
        foreach($this->uri as $k => $r) {
            if (!in_array($r, $this->endPoints[$k])) {
                $valid = false;
            }
        }
        return $valid;
    } 

}