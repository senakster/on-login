<?php
namespace Model;
use \Model\Database;
use Config;
use PDO;

class UserModel {
    var $db;
    var $schemas;
    function __construct(){
        $this->schemas = Config\DB_SCHEMAS();
        $this->db = new Database();
    }
    /**
     * use without to not fetch from list "userid,email,username,userrole,firstname,lastname,hash"
    */
    public function getUserSQL($email, $without = []) {
        try {
        $fetchSchema = array_diff($this->schemas->userFetch, $without); 
        $fetchSchema = implode(',', $fetchSchema); 
        $query = "SELECT $fetchSchema from `users` WHERE `email` = :email LIMIT 1";
        $sth = $this->db->con->prepare($query);
        $sth->bindParam(':email', $email, PDO::PARAM_STR, 48);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        foreach($without as $u){
            if ($result && $result[$u]) {
                unset($result[$u]);
            }
        }
        return $result;
    } catch (PDOException $e) {
        return 'FAILED - ' . $e->getMessage();
    }
    }

    public function getUserById($id, $without = []) {
        try {
        $fetchSchema = array_diff($this->schemas->userFetch, $without) ;
        $fetchSchema = implode(',', $fetchSchema); // string(54) "userid,email,username,userrole,firstname,lastname,hash"
        $query = "SELECT $fetchSchema from `users` WHERE `userid` = :id LIMIT 1";
        $sth = $this->db->con->prepare($query);
        $sth->bindParam(':id', $id, PDO::PARAM_STR, 48);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        foreach($without as $u){
            if ($result && $result[$u]) {
                unset($result[$u]);
            }
        }
        return $result;
    } catch (PDOException $e) {
        return 'FAILED - ' . $e->getMessage();
    }
    }

    /**
     * PUBLIC
     */
    public function newUser($data) {
        if ($this->requiredFields(['email'], $data)) {
            return 'FAILED - Missing Email';
        }
        if ($this->getUserSQL($data['email'])) {
            return 'FAILED - Email Address not available';
        };
            return $this->newUserSQL($data);
    }

    public function validateUserPass($data) {
        if ($this->requiredFields(['email', 'password'], $data)) {
            return false;
        }
        $user = $this->getUserSQL($data['email']);

        if (!$user || !isset($user['hash']) || $user['hash'] == NULL) {
            return false;
        }
        $validatePassword = password_verify($data['password'] , $user['hash']);

        return $validatePassword;
    }

    /**
     * Create new user in DB
     * @param {array} data
     * @return {string} message
     * 
     */
    public function newUserSQL($data) {
        if ($this->requiredFields('email', 'password')) {
            return 'FAILED - Required Fields Missing';
        }
        try {
            //REPLACE PASSWORD WITH HASH 
            $data['hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);

            // GENERATE SQL DATA 
            $dataArr = array_values(array_filter($data));
            $columnsArr = array_keys(array_filter($data));
            $placeholderArr = preg_filter('/^/', ':', array_keys(array_filter($data)));
            
            $dataStr = implode(', ', $dataArr);
            // var_dump($data);
            $columns = implode(', ',$columnsArr);
            $placeholders = implode(', ',$placeholderArr);
            
            // EXECUTE SQL
            $query = "INSERT INTO `users` ($columns) VALUES ($placeholders)";
            $sth = $this->db->con->prepare($query) or throw new PDOException(var_dump($this->connection->errorInfo()));
            foreach( $placeholderArr as $k => $p) {
                $sth->bindParam($p,$dataArr[$k],PDO::PARAM_STR);
            }
            if ($sth->execute()) {
                return 'SUCCESS - User Succesfully Created';
            } else {
                return 'FAILED - Something went wrong :(';
            }
        } catch (PDOException $e) {
            return 'FAILED - ' . $e->getMessage();
        } 
    }

    public function upateUserSQL($data) {
        $query = "SELECT * from `users` WHERE `email` = :user OR `username` = :user";
        return "FAILED - UPDATE SQL $query"; 
    }
  
    public function setRefreshToken($refresh_token, $uid) {
        try {
        $query = "UPDATE `users` SET `refresh_token` = :refresh_token WHERE `userid` = :userid";
        $sth = $this->db->con->prepare($query) or throw new PDOException($this->db->con->errorInfo());
        $sth->bindParam(':refresh_token', $refresh_token, PDO::PARAM_STR);
        $sth->bindParam(':userid', $uid, PDO::PARAM_STR);
        if ($sth->execute()) {
            return 'SUCCESS - Refreshed Token';
        } else {
            return 'FAILED - Something went wrong :(';
        }
        } catch (PDOException $e) {
            return 'FAILED - ' . $e->getMessage();
        }
    }

    public function requiredFields($fields = [], $data) {
        $valid = true;
        if (!isset($data) || !is_array($data)) {
            $valid = false;
        }
        foreach ($fields as $f) {
            if (!isset($data[$f]) || $data[$f] != NULL) {
                $valid = false;
            }
        }
        return $valid;
    }


}