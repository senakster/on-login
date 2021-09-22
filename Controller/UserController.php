<?php
namespace Controller;
use Models\UserModel;
use Config;


class UserController extends BaseController{
    var $userModel;

    function __construct(){
        parent::__construct();
        $this->userModel = $this->loadModel('user');
    }
    public function newAction() {
        $data = $this->getParams(Config\DB_SCHEMAS()->userRequest);
        if ($data) {
                        
            $this->output->status = 'ok';
            $this->output->message = $this->userModel->newUser($data);
            if (substr( $this->output->message, 0, 9 ) === "SUCCESS -" || true) {
                $user = $this->userModel->getUserSQL($data['email'], ['hash']);
                $token_pair = $this->loadModel('token')->createToken($user);
                if ($this->userModel->validateUserPass($data) && $token_pair) 
                {
                    $this->issueToken($user);
                    /**
                     * set user to provide userdata to frontend
                     */
                    $this->output->data = ['user' => $user, 'access_token' => $token_pair['access_token'], 'redirect' => \Config\REDIRECT];
                }
            }

        }
        $this->serveJSON($this->output);
        
    }

    public function schemaAction() {
        $this->output->data = Config\DB_SCHEMAS()->user;
        if ($this->output->data) {
            $this->output->status = 'success';
        }        
        $this->serveJSON($this->output);
    }
}