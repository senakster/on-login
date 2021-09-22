<?php
// require './../Bootstrap.php';
use Dotenv\Dotenv;
// get the local secret key
$secret = getenv('SECRET');

    /**
    *  Encode URL-friendly string
    *  
    *  @return string
    */
function base64UrlEncode ($text)
    {
       return str_replace(
           ['+', '/', '='],
           ['-', '_', ''],
           base64_encode($text)
       );
    }

// Create the token header
$header = json_encode([
    'typ' => 'JWT',
    'alg' => 'HS256'
]);

// Create the token payload
// 360 s = 6 minutes;
$payload = json_encode([
    'user_id' => 1,
    'role' => 'admin',
    'exp' => (strtotime("now") + 360)
]);

// Encode Header
$base64UrlHeader = base64UrlEncode($header);

// Encode Payload
$base64UrlPayload = base64UrlEncode($payload);

// Create Signature Hash
$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);

// Encode Signature to Base64Url String
$base64UrlSignature = base64UrlEncode($signature);

// Create JWT
$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

echo "Your token:\n" . $jwt . "\n";
?>