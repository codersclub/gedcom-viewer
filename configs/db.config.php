<?php

function database_connect() {
    global $obj_db;

    // get calendar database
    if (isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] == 'localhost') {
        // local webserver on your computer, like XAMPP
        DEFINE('DBHOST', 'localhost');
        DEFINE('DBUSER', 'root');
        DEFINE('DBPASS', '');
        DEFINE('DBNAME', 'gedcom_viewer');
    } else if(isset($_SERVER["HTTP_HOST"]) && is_null($_SERVER["HTTP_HOST"])) {
        // in certain rare cases like virtual machine
        DEFINE('DBHOST', '127.0.0.1');
        DEFINE('DBUSER', 'root');
        DEFINE('DBPASS', '');
        DEFINE('DBNAME', 'gedcom_viewer');
    } else {
        // online use, so when you have the calendar on the online website and used from reminders.php
        DEFINE('DBHOST', '127.0.0.1');
        DEFINE('DBUSER', 'root');
        DEFINE('DBPASS', '');
        DEFINE('DBNAME', 'gedcom_viewer');
        //DEFINE('DBPORT', '');
    }

    if(defined('DBPORT')) {
        $obj_db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME, DBPORT);
        mysqli_set_charset($obj_db, 'utf8');
    } else {
        if(function_exists('mysqli_connect')) {
            $obj_db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
            mysqli_set_charset($obj_db, 'utf8');
        }
        
    }
    
    
    if ($obj_db === FALSE) {
        $error = "Database connection failed";
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    mysqli_set_charset($obj_db, 'utf8');
}

function database_close() {
    global $obj_db;

    if (!is_null($obj_db)) {
        mysqli_close($obj_db);
        unset($obj_db);
    }
}