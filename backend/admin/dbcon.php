<?php 

// connect to database
$DATABASE_HOSTNAME = "testdb.cm6ahp8bvbm1.ap-south-1.rds.amazonaws.com";
$DATABASE_USERNAME = "admin";
$DATABASE_PASSWORD = "admin1234";
$DATABASE_DBNAME = "betting_application";

//code to connect with local php myadmin
// $DATABASE_HOSTNAME = "localhost";
// $DATABASE_USERNAME = "root";
// $DATABASE_PASSWORD = "";
// $DATABASE_DBNAME = "satta_app_db";

try {
    $con = new PDO("mysql:host=$DATABASE_HOSTNAME;dbname=$DATABASE_DBNAME", $DATABASE_USERNAME, $DATABASE_PASSWORD);
    // set the PDO error mode to exception
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

if(!isset($TIMEZONE_SET)){
    // SYNCHRONIZE TIMEZONE WITH PHP-MYSQL
    define('TIMEZONE', 'Asia/Kolkata');
    date_default_timezone_set(TIMEZONE);

    $now = new DateTime();

    // Get timezone
    $mins = $now->getOffset() / 60;
    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;
    $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);

    // Synchronize PHP timezone with that of Database
    $con->exec("SET time_zone='$offset';");
}