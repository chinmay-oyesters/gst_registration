<?php


$database_tables = array(
    "
    CREATE TABLE IF NOT EXISTS `user_table` (
        `user_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `username` VARCHAR(255) NOT NULL,
        `user_password` varchar(255) NOT NULL,
        `user_fullname` VARCHAR(255) NOT NULL,
        `user_email` VARCHAR(255) NOT NULL,
        `user_phonenumber` VARCHAR(255) NOT NULL,
        `entity_email` VARCHAR(255) NOT NULL,
        `entity_pan` VARCHAR(255) NOT NULL,
        `entity_fullname` VARCHAR(255) NOT NULL,
        `entity_phonenumber` VARCHAR(255) NOT NULL,
        `profile_image` BLOB,
        `otp` INTEGER(10),
        `otp_created_at` VARCHAR(255),
        `created_at` DATETIME ,
        `updated_at` DATETIME 
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
        `otp` INTEGER(10),
        `otp_created_at` VARCHAR(255),
        `created_at` DATETIME ,
        `updated_at` DATETIME 
    )",
    "
    CREATE TABLE IF NOT EXISTS `payments_table` (
        `payment_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT(255),
        `payment_amount` VARCHAR(255) NOT NULL,
        `created_at` DATETIME ,
        `updated_at` DATETIME ,
        FOREIGN KEY (user_id) 
    		REFERENCES user_table(user_id) 
    		ON DELETE CASCADE
    )"
    ,
    "
    CREATE TABLE IF NOT EXISTS `form_table` (
        `form_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `form_name` VARCHAR(255) NOT NULL,
        `form_fields` VARCHAR(255) NOT NULL,
        `created_at` DATETIME ,
        `updated_at` DATETIME 
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS `form_field_table` (
        `form_field_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `form_field_tag1` VARCHAR(255) DEFAULT NULL,
        `form_field_title` VARCHAR(255) NOT NULL,
        `form_field_type` VARCHAR(255) NOT NULL,
        `form_field_associated_to` VARCHAR(255) DEFAULT NULL,
        `form_field_values` VARCHAR(255) DEFAULT NULL,
        `form_field_validation` VARCHAR(255) DEFAULT NULL,
        `form_field_required` BOOLEAN DEFAULT NULL,
        `created_at` DATETIME ,
        `updated_at` DATETIME 
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS `form_response_table` (
        `form_response_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `form_response_form_id` INT(255) NOT NULL,
        `form_response_form_field_id` INT(255) NOT NULL,
        `form_response_user_id` INT(255) NOT NULL,
        `form_response_answer` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME ,
        `updated_at` DATETIME ,
        FOREIGN KEY (form_response_form_id)
    		REFERENCES form_table(form_id)
    		ON DELETE CASCADE,
        FOREIGN KEY (form_response_form_field_id)
    		REFERENCES form_field_table(form_field_id)
    		ON DELETE CASCADE,
        FOREIGN KEY (form_response_user_id)
    		REFERENCES user_table(user_id) 
    		ON DELETE CASCADE
    )
    "
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