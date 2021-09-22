<?php
namespace Config;
//CASE API
const DB_PATH = 'https://omstilmig.nu/api';
const DB_USER_PATH = DB_PATH . '/gnf/get';

// CASE DB
// const DB_TYPE = 'mysql';
// const DB_HOST = 'omstilmig.nu.mysql';
// const DB_NAME = 'omstilmig_nugnf';
// const DB_USER = 'omstilmig_nugnf';
// const DB_PASS = "m_W%2`Re>#j.Be=;Zv8RCmyey{9K-j\$bGFW\bL<-xyed@WDd}%X~',zU8+5$`}r(";

const DB_TYPE = 'mysql';
const DB_HOST = 'localhost';
const DB_NAME = 'gnf';
const DB_USER = 'emil';
const DB_PASS = "1337Superlime";

function DB_TABLES () { 
/**
 * DATABASE DEFAULT
 * CREATE IF NOT EXISTS
 */
$qusers = "(
 `userid` int unsigned NOT NULL AUTO_INCREMENT UNIQUE,
 `email` varchar(64) NOT NULL UNIQUE, 
 `username` varchar(48) NULL, 
 `userrole` varchar(32) DEFAULT 'user',
 `firstname` varchar(32) NULL,
 `lastname` varchar(48) NULL,
 `hash` varchar(60) NOT NULL,
 `refresh_token` varchar(256) NULL,
 PRIMARY KEY (`userid`),
 `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
 `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB";
$qroles = "(
 `roleid` int unsigned NOT NULL AUTO_INCREMENT UNIQUE,
 `userid` int unsigned NOT NULL,
 `permissionid` int unsigned NOT NULL,
 `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`roleid`),
 FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE,
 FOREIGN KEY (`permissionid`) REFERENCES `permissions` (`permissionid`) ON DELETE CASCADE
) ENGINE=InnoDB";
$qpermissions = "(
 `permissionid` int unsigned NOT NULL AUTO_INCREMENT,
 `permissionname` varchar(48) NULL,
 `appid` int unsigned NOT NULL,
 `role` varchar(32) NOT NULL DEFAULT 'user',
 PRIMARY KEY (`permissionid`),
 `updated_at` TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
 `created_at` TIMESTAMP NOT NULL
) ENGINE=InnoDB";
$quserdata = "(
 `detailsid` int unsigned NOT NULL AUTO_INCREMENT UNIQUE,
 `userid` int unsigned NOT NULL,
 `imgdata` MEDIUMBLOB NOT NULL,
 `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`detailsid`),
 FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB";

    return (object) array(
    'users' => $qusers, 
    'permissions' => $qpermissions,
    'roles' => $qroles,
    'userdetails' => $quserdata
    );
};

function DB_SCHEMAS () { 
    return (object) array(
        "user" => (object) array(
            "create" => ['email', 'password','username','firstname','lastname'],
            "update" => ['username','firstname','lastname'],
            "login" => ['email', 'password'],
        ),
        "userFetch" => ['userid','email','username','userrole','firstname','lastname','hash'],
        "userRequest" => ['email','username','firstname','lastname','password'],
    );
}; 