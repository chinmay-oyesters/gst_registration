<?php


$database_tables = array(
    "
    CREATE TABLE IF NOT EXISTS `admin_user_table` (
        `admin_user_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `admin_password` VARCHAR(255) NOT NULL,
        `admin_email_id` varchar(255) NOT NULL,
        `admin_phonenumber` VARCHAR(255) NOT NULL,
        `admin_fullname` VARCHAR(255) NOT NULL,
        `profile_image` BLOB,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "
    CREATE TABLE IF NOT EXISTS `win_history` (
        `win_id` INT(255) PRIMARY KEY AUTO_INCREMENT,
        `market_id` INT(255),
        `user_id` INT(255),
        `win_amount` INT(255) NOT NULL,
        `game_total_collection` INT(255) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (market_id) REFERENCES market_table(market_id),
        FOREIGN KEY (user_id) REFERENCES user_table(user_id)
    )",
    "
    CREATE TABLE IF NOT EXISTS `market_results` (
        `result_id` INT(255) PRIMARY KEY AUTO_INCREMENT,
        `market_id` INT(255),
        `results` VARCHAR(255) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (market_id) REFERENCES market_table(market_id)
    )",
 
);

require_once("dbcon.php");

foreach ($database_tables as $database_table) {
    try{

        $con->exec($database_table);
        
    } catch(PDOException $e){

        echo "Error creating table: " . $database_table . "<br>" . $e->error;
        // print_r($e);

    }
}