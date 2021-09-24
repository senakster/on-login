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
                $user = $this->userModel->getUserSQL($data['email'], ['hash','refresh_token']);
                if ($this->userModel->validateUserPass($data)) 
                {
                    $access_token = $this->issueToken($user);
                    /**
                     * provide userdata and access token to frontend
                     */
                    $this->output->data = ['user' => $user, 'access_token' => $access_token, 'redirect' => \Config\REDIRECT];
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

    public function updateAction() {
        $access = $this->accessValidation(1, 'author'); // ['user' => $user, 'granted' => boolean]
        if ($access['granted']) {
            $data = $this->getParams(Config\DB_SCHEMAS()->userUpdate);
            $updated_user = $this->userModel->updateUser($access['user'], $data);
            $this->output->data = isset($updated_user['userid']) ? ['user' => $updated_user] : null;
            $this->output->status = isset($updated_user['userid']) ? 'ok' : 'fail';
            $this->output->message = isset($updated_user['userid']) ? 'User Updated Succesfully' : 'User Update Failed';
        }
        $this->serveJSON($this->output);
    }
}