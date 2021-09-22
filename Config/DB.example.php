<?php
namespace Config;
const DB_PATH = 'Database End Point';
const DB_USER_PATH = DB_PATH . 'users endpoint';

const DB_TYPE = 'ex. mysql';
const DB_HOST = 'ex. localhost';
const DB_NAME = 'ex. mydb';
const DB_USER = 'ex. username';
const DB_PASS = "ex. password1234"; // <- if you like living on the edge

function DB_TABLES () { 
/**
 * DATABASE DEFAULT
 * CREATE IF NOT EXISTS
 */
$qusers = "(
 `userid` int NOT NULL AUTO_INCREMENT,
 `email` varchar(64) NOT NULL UNIQUE, 
 `username` varchar(48) NULL, 
 `userrole` varchar(32) NOT NULL DEFAULT `user`,
 `hash` varchar(60) NOT NULL,
 PRIMARY KEY (`userid`)
) ENGINE=InnoDB";

return (object) array('users' => $qusers, 'roles' => $qroles, 'permissions' => $qpermissions);
}
