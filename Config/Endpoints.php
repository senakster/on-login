<?php
namespace Config;

class Endpoints {

    public $valid_paths;
    function __construct (){
        $this->valid_paths = $this->valid_paths();
    }

    private function valid_paths(){
        $vp = [
            '' => [
                'user' => ['new', 'schema'],
                'authenticate' => ['login', 'validate', 'schema'],
                ]
            ];
        $u0 = ['', 'index.php'];
        $u1 = ['user', 'authenticate'];
        $u2 = ['', 'new', 'schema', 'validate', 'login'];
        return [$u0,$u1, $u2];
    }
}