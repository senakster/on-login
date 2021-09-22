<?php
namespace Model;
use Model\UserModel;
use DateTimeImutable;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Config;
use PDO;

class TokenModel {
    var $db;
    var $schemas;
    function __construct(){
        $this->schemas = Config\DB_SCHEMAS();
        $this->db = new Database();
    }

    public function validateToken($jwt) {
        $valid = true;
        $secret = getenv('SECRET');
        try {
            $decoded = JWT::decode($jwt, $secret, array('HS256'));
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ['user' => null, 'error' => 'Expired Token' . json_encode($e)];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return ['user' => null, 'error' => 'Invalid Signature' . json_encode($e)];
        } catch (Exception $e) {
            return ['user' => null, 'error' => 'Invalid Token' . json_encode($e)];
        } catch (Error $e) {
            return ['user' => null, 'error' => 'Invalid Token' . json_encode($e)];
        }
        $valid = $valid && $decoded && !$expired;
        $message = $valid ? 'Authentication succeeded' : '';
        $user = $valid ? $decoded->usr : null;
        return ['user' => $user, 'message' => $message];
    }

    public function validateRefresh($jwt) {
        $valid = true;
        $rsecret = getenv('RSECRET');
        try {
            $decoded = JWT::decode($jwt, $rsecret, array('HS256'));
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ['user' => null, 'error' => 'Expired Token: ' . json_encode($e)];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return ['user' => null, 'error' => 'Invalid Signature: ' . json_encode($e)];
        } catch (Exception $e) {
            return ['user' => null, 'error' => 'Invalid Token' . json_encode($e)];
        }

        $date = new \DateTimeImmutable();

        $valid = $valid && $decoded;
        $message = $valid ? 'Authentication succeeded' : '';
        $user = $valid ? $this->getUserById($decoded->uid, ['hash']) : null;
        $token_pair = $this->createToken($user);

        return ['user' => $user, 'message' => $token_pair];
    }

    private function getUserById($uid, $without = []) {
        try {
        $fetchSchema = array_diff($this->schemas->userFetch, $without); 
        //FETCH REFRESH TOKEN FOR INVALIDATION
        // $fetchSchema[] = 'refresh_token';
        $fetchSchema = implode(',', $fetchSchema); 
        // return $fetchSchema;
        $query = "SELECT $fetchSchema from `users` WHERE `userid` = :userid LIMIT 1";
        $sth = $this->db->con->prepare($query);
        $sth->bindParam(':userid', $uid, PDO::PARAM_STR, 48);
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

    public function createToken($user) {
        // form payload from userdata
        $payload = $this->formPayload($user);
        $secret = getenv('SECRET');
        $rsecret = getenv('RSECRET');
        $jwt = JWT::encode($payload['access_token'], $secret, 'HS256');
        $jwt_refresh = JWT::encode($payload['refresh_token'], $rsecret, 'HS256');
        return ['access_token' => $jwt, 'refresh_token' => $jwt_refresh];
    }

    private function formPayload($user){
        $secret = getenv('SECRET');

        $issuedAt   = new \DateTimeImmutable();
        $expire     = $issuedAt->modify('+15 minutes')->getTimestamp();      // Add 60 seconds
        $serverName = $_SERVER['SERVER_NAME'];

        $access_token = [
            'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
            'iss'  => $serverName,                       // Issuer
            'nbf'  => $issuedAt->getTimestamp(),         // Not before
            'exp'  => $expire,                           // Expire
            'usr'  => $user
        ]; //INCLUDE PERMISSIONS

        $rsecret = getenv('RSECRET');
        $refresh_token = [
            'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
            'iss'  => $serverName,  
            'nbf'  => $issuedAt->getTimestamp(),         // Not before
            'exp'  => $issuedAt->modify('+7200 minutes')->getTimestamp(),
            'uid'  => $user['userid']
        ];

        $data = [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token
        ];
        return $data;
    }

}