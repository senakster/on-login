<?php
namespace Model;
use \Model\Database;
use Config;
use PDO;
// use DateTimeImutable;
use Firebase\JWT\JWT;
// use Carbon\Carbon;

class AccessModel {
    var $db;
    
    function __construct () {
        $this->db = new Database();
    }

    public function validateAccessToken($access_token) {
        if ($access_token) {
            $secret = getenv('SECRET');
            try {
                $decoded = JWT::decode($access_token, $secret, array('HS256'));
            } catch (\Firebase\JWT\ExpiredException $e) {
                return 'Access Violation: Expired';
            } catch (\Firebase\JWT\UnexpectedValueException $e) {
                return 'Access Violation: ' . $e;
            } catch (\Firebase\JWT\SignatureInvalidException $e) {
                return 'Access Violation: Bad Signature';
            } catch (Exception $e) {
                return 'Access Violation: Exception';
            }
            return $decoded;
        } else {
            return 'No Access Token';
        }
    }

    public function getUserPermissions($user_id = 0, $app_id = Config\APP_ID) {

        // function filterP($p){
        //     return $p['permissionid'] ? $p['permissionid'] : 0;
        // }

        $permissionIds = $this->getPermissionIds($user_id);

        if (count($permissionIds) > 0) {
            $idArr = [];

            foreach(array_values($permissionIds) as $p){
                $idArr[] = $p['permissionid'] ? $p['permissionid'] : 0;
            }

            $ids = implode($idArr);

            try {
                $query = "SELECT `appid`,`role`,`permissionname` from `permissions` WHERE `permissionid` = (:permissionids) AND `appid` = :appid";
                $sth = $this->db->con->prepare($query) or die(var_dump($this->connection->errorInfo()));
                $sth->bindParam(':permissionids', $ids, PDO::PARAM_STR, 48);
                $sth->bindParam(':appid', $app_id, PDO::PARAM_INT, 48);
                $sth->execute();
                $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } catch (PDOException $e) {
                return 'FAILED - ' . $e->getMessage();
            }

        }
        
    }

    private function getPermissionIds($user_id = 0){
        if ($user_id) {
            try {
                $query = "SELECT `permissionid` from `roles` WHERE `userid` = :userid";
                $sth = $this->db->con->prepare($query) or die(var_dump($this->connection->errorInfo()));
                $sth->bindParam(':userid', $user_id, PDO::PARAM_STR, 48);
                $sth->execute();
                $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } catch (PDOException $e) {
                return 'FAILED - ' . $e->getMessage();
            }
        }
    }

}