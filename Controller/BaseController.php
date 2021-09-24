<?php
namespace Controller;
use Config;
class BaseController {
    var $output;
    function __construct() {
        $this->output = (object) [
            "data" => null,
            "status" => "none",
            "message" => ""
        ];
    }


    /**
     * __call magic method.
     */
    public function __call($name, $arguments)
    {
        $this->output->status = 'fail';
        $this->output->message = 'Cannot Process Request: ' . $name;
        $this->serveJSON($this->output);
    }

    /**
    *  Encode URL-friendly string
    *
    *  @return string
    */
    protected function base64UrlEncode($text)
    {
       return str_replace(
           ['+', '/', '='],
           ['-', '_', ''],
           base64_encode($text)
       );
    }

    protected function loadController($name = false) {
        if ($name) {
            try {
                $c = ucwords($name).'Controller';
                if (file_exists(PROJECT_ROOT_PATH . 'Controller/' . $c. ".php")) {
                    require_once PROJECT_ROOT_PATH . 'Controller/' . $c. ".php";
                    $cls = '\\Controller\\'.$c;
                    return new $cls();
                }
            } catch (Error $e) {
                throw new Error('Cannot Load Controller: ' . $e->getMessage());
            }
        }
    }
    protected function loadModel($name = false) {
        if ($name) {
            try {
                $m = ucwords($name).'Model';
                if (file_exists(PROJECT_ROOT_PATH . 'Model/' . $m. ".php")) {
                require_once PROJECT_ROOT_PATH . 'Model/' . $m. ".php";
                $mdl = '\\Model\\'.$m;
                return new $mdl();
                }
            } catch (Error $e) {
                throw new Error('Cannot Load Model:' . $e->getMessage());
            }
        }
    }

    /**
     * @param {params} array
     * @return {selectedParams} array
     *
     */
    protected function getParams($params = []){
        $selectedParams = [];
        $raw = json_decode(file_get_contents("php://input"));
        foreach($params as $p) {
            if ($_POST && isset($_POST[$p])) {
            $selectedParams[$p] = $_POST[$p];
            } else if (isset($raw->$p)) {
                $selectedParams[$p] = $raw->$p;
            }

        }
        return $selectedParams;
    }

    protected function getAccessToken($refresh = false) {
        $token = $this->getBearerToken();
        if (!$token) {
            $headerCookies = explode('; ', getallheaders()['Cookie']);
            $cookies = array();
            foreach($headerCookies as $itm) {
                list($key, $val) = explode('=', $itm, 2);
                $cookies[$key] = $val;
            }
            $token = $refresh ? $cookies['jwt_refresh'] : $cookies['jwt'];
        }
        return $token;
    }
    protected function getRefreshToken() {
            $headerCookies = explode('; ', getallheaders()['Cookie']);
            $cookies = array();
            foreach($headerCookies as $itm) {
                list($key, $val) = explode('=', $itm, 2);
                $cookies[$key] = $val;
            }
            $token = $cookies['jwt_refresh'];
            return $token;
    }
    /**
     * Get header Authorization
     * */
    protected function getAuthorizationHeader(){
            $headers = null;
            if (isset($_SERVER['Authorization'])) {
                $headers = trim($_SERVER["Authorization"]);
            }
            else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                //print_r($requestHeaders);
                if (isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
            return $headers;
        }
    /**
     * get access token from header
     * */
    protected function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    protected function serveJSON($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        die();
    }

    protected function unsetCookie() {
        $date = new \DateTimeImmutable();
        $cookie_options = array(
            'expires' => $date->modify('+10 minutes')->getTimestamp(),
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'], // Not needed for unsetting
            'secure' => true,
            'httponly' => true, // Not needed for unsetting
            'samesite' => 'None' // Not needed for unsetting
          );
        setcookie("jwt", '', $cookie_options);
        setcookie("jwt_refresh", '', $cookie_options);
    }

    protected function issueToken($user) {
        $date = new \DateTimeImmutable();
        $token_pair = $this->loadModel('token')->createToken($user);
        $cookie_options = array(
            'expires' => $date->modify('+15 minutes')->getTimestamp(), //6minutes
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'], // leading dot for compatibility or use subdomain
            'secure' => true, // or false
            'httponly' => true, // or false
            'samesite' => 'None' // None || Lax || Strict
          );
        // setcookie("jwt", $token_pair['access_token'], $cookie_options);
        $cookie_options = array(
            'expires' => $date->modify('+7200 minutes')->getTimestamp(), //6minutes
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'], // leading dot for compatibility or use subdomain
            'secure' => true, // or false
            'httponly' => true, // or false
            'samesite' => 'None' // None || Lax || Strict
          );
        setcookie("jwt_refresh", $token_pair['refresh_token'], $cookie_options);
        $this->loadModel('user')->setRefreshToken($token_pair['refresh_token'], $user['userid']);
        return $token_pair['access_token'];
    }

    /**
     * JWT ACCESS TOKEN VALIDATION FOR ACTION
     */
    protected function accessValidation($app_id = Config\APP_ID, $permission_level = 'admin'){
        $access_token = $this->getBearerToken();
        // $this->output->data = $access_token;
        // return;
        if ($access_token) {
            $access_model = $this->loadModel('access');
            $data = $access_model->validateAccessToken($access_token);
            // $this->output->data = $data;
        }

        if($data === 'Access Violation: Expired') {
            $this->expiredAccessToken();
        }

        if (isset($data->usr->userid) && intval($data->usr->userid) > 0) {
            $user_id = $data->usr->userid;
            $permissions = $access_model->getUserPermissions($user_id);
            return ['user' => $data->usr, 'granted' => $this->grantPermission($app_id, $permission_level, $permissions)];
        }

        // $this->output->status = 'ok';
        // $this->output->message = 'Access Validation';
        return false;
    }

    private function expiredAccessToken() {
        $data = $this->refreshToken();
        if (isset($data['access_token'])) {
            $access_token = $data['access_token'];
            $access_model = $this->loadModel('access');
            $decoded = $access_model->validateAccessToken($access_token);
            if ($decoded && $decoded->usr) {
                $this->output->data = ['user' => $decoded->usr, 'access_token' => $access_token];
                return;
            } 
        }

        $this->output->data = ['user' => null , 'access_token' => '', 'refreshAction' => $data];
        return;
    }

    private function grantPermission($app_id, $permission_level, $permissions){
        $granted = false;
        foreach($permissions as $p) {
            if ($p['appid'] == "$app_id" && $p['role'] === $permission_level) {
                $granted = true;
            }
        }
        return $granted;
    }

    protected function refreshToken() {
        $refresh_token = $this->getRefreshToken();

        $data = false;
        if ($refresh_token) {
            $data = $this->loadModel('token')->validateRefresh($refresh_token); // => ['user' => $user, 'access_token' => $access_token]
            if (isset($data['user']) && $data['user']) {
                $access_token = $this->issueToken($data['user']);
                $data['access_token'] = $access_token;
            }
        }
        return $data; // => ['user' => $user, 'access_token' => $access_token] || false
    }


}
