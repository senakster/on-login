<?php 
declare(strict_types=1);

define("PROJECT_ROOT_PATH", __DIR__."/");
require PROJECT_ROOT_PATH.'Config/Config.php';
use Firebase\JWT\JWT;
require PROJECT_ROOT_PATH.'vendor/autoload.php';
use Dotenv\Dotenv;

if (!file_exists(PROJECT_ROOT_PATH.'.env')) {
    generateNewSecret();
}

$dotenv = new DotEnv(PROJECT_ROOT_PATH);
$dotenv->load();

$secret = getenv('SECRET');
if (!$secret || $secret === 'mysecret') {
    generateNewSecret();
}

function generateNewSecret () {
    $env = fopen(PROJECT_ROOT_PATH.".env", "w") or die("Unable to open file!");
    $txt = "SECRET=";
    fwrite($env, $txt);
    $secret = bin2hex(random_bytes(32));
    fwrite($env, $secret. PHP_EOL);
    $txt = "RSECRET=";
    fwrite($env, $txt);
    $secret = bin2hex(random_bytes(32));
    fwrite($env, $secret);
    fclose($env);
}
/**
 * BOOTSTRAP
 */
require_once PROJECT_ROOT_PATH . "Controller/Router.php";
require_once PROJECT_ROOT_PATH . "Model/Database.php";
