<?php


$database_tables = array(
    "
    CREATE TABLE IF NOT EXISTS `user_table` (
        `user_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `username` VARCHAR(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `person_name` VARCHAR(255) NOT NULL,
        `person_email` VARCHAR(255) NOT NULL,
        `person_phonenumber` VARCHAR(255) NOT NULL,
        `entity_email` VARCHAR(255) NOT NULL,
        `entity_pan` VARCHAR(255) NOT NULL,
        `entity_name` VARCHAR(255) NOT NULL,
        `entity_phonenumber` VARCHAR(255) NOT NULL,
        `profile_image` BLOB,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "
    CREATE TABLE IF NOT EXISTS `admin_user_table` (
        `admin_user_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `admin_username` VARCHAR(255) NOT NULL,
        `admin_fullname` VARCHAR(255) NOT NULL,
        `admin_password` VARCHAR(255) NOT NULL,
        `admin_phonenumber` VARCHAR(255) NOT NULL,
        `admin_email` VARCHAR(255) NOT NULL,
        `admin_profileimage` BLOB,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "
    CREATE TABLE IF NOT EXISTS `payments_table` (
        `payment_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT(255),
        `payment_amount` VARCHAR(255) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) 
    		REFERENCES user_table(user_id) 
    		ON DELETE CASCADE
    )"
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