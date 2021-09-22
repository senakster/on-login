<?php
namespace Model;
use Config;
use PDO;
require_once PROJECT_ROOT_PATH . "Config/DB.php";
class Database {
    var $endpoint;
    var $con;
    var $tables;
    
    function __construct(){
        
        $this->endpoint = Config\DB_USER_PATH;
        try {
            $this->con = new PDO(Config\DB_TYPE. ":host=" . Config\DB_HOST, Config\DB_USER, Config\DB_PASS);
            // set the PDO error mode to exception
            $this->con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $dbname = "`" . str_replace("`", "``", Config\DB_NAME) . "`";

            /**
             * UNCOMMENT TO RESET DB
             */
            // $this->con->query("DROP DATABASE $dbname");

            
            $this->con->query("CREATE DATABASE IF NOT EXISTS $dbname"); 
            $this->con->query("use $dbname");
            $this->tables = Config\DB_TABLES();
            
            foreach($this->tables as $t => $q) {
                $this->con->query("CREATE TABLE IF NOT EXISTS `$t` $q"); 
            };
            return $this->con;
            } catch (PDOException $e) {
                die(var_dump($e->getMessage()));
        }

    }

    // public function getUserREST($user, $password) {
    //     // building array of variables
    //         $content = http_build_query(array(
    //         'user' => $user,
    //         'password' => $password
    //         ));
    //     // creating the context change POST to GET if that is relevant 
    //     $context = stream_context_create(array(
    //         'http' => array(
    //             'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
    //             'method' => 'POST',
    //             'content' => $content, )));

    //     $result = file_get_contents($this->endpoint, null, $context);
    //     return $result;
    // }





}