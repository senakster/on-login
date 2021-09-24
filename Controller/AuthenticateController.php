<?php
namespace Controller;
use Carbon\Carbon;
use DateTimeImutable;
use Config;

class AuthenticateController extends BaseController{
   
    function __construct(){
        parent::__construct();
    }

    public function schemaAction() {
        $this->output->data = Config\DB_SCHEMAS()->user;
        if ($this->output->data) {
            $this->output->status = 'success';
        }        
        $this->serveJSON($this->output);
    }

    function validateAction() {
        $jwt = $this->getToken();
        // return $jwt;
        if (!$jwt) {
            $this->refreshAction();
        } else {
            $this->validateToken($jwt);
        }
        $this->serveJSON($this->output);
    }

    function loginAction() {
        $date = new \DateTimeImmutable();
        $this->userModel = $this->loadModel('user');
        $data = $this->getParams(Config\DB_SCHEMAS()->user->login);
        $valid = $this->userModel->validateUserPass($data);
        if ($valid) {
            $this->output->status = 'ok';
            $this->output->message = 'Login Succeeded';
            $user = $this->userModel->getUserSQL($data['email'], ['hash', 'refresh_token']);
            $access_token = $this->issueToken($user);
            $redirect = ['success' => '//omstilling.nu', 'failure' => '/Error'];
            $this->output->data = ['user' => $user, 'access_token' => $access_token, 'redirect' => \Config\REDIRECT];
        } else {
            $this->unsetCookie();
            $this->output->data = ['user' => null];
            $this->output->status = 'fail';
            $this->output->message = 'Login Fail';
        }
        $this->serveJSON($this->output);
    }

    function logoutAction() {
        $date = new \DateTimeImmutable();
        $this->unsetCookie();
        $this->output->message = 'Logging Out';
        $this->serveJSON($this->output);
    }

    private function validateToken ($jwt) {
        $message = 'Authentication failed';
        $valid = false;

        $data = $this->loadModel('token')->validateToken($jwt);
        if ($data) {

            /**
             * OUTPUT & SERVE
             */
            $message = $data['message'] ? $data['message'] : $message; 
            $status = 'ok';
            
            $this->output->data = $data;
            $this->output->status = $status;
            $this->output->message = $message;
        }
        return $valid;

    } 



}