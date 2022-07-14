<?php


$database_tables = array(
    "
    CREATE TABLE IF NOT EXISTS `user_table` (
        `user_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `user_password` varchar(255) NOT NULL,
        `user_fullname` VARCHAR(255) NOT NULL,
        `user_email` VARCHAR(255) NOT NULL,
        `user_phonenumber` VARCHAR(255) NOT NULL,
        `entity_email` VARCHAR(255) NOT NULL,
        `entity_pan` VARCHAR(255) NOT NULL,
        `entity_fullname` VARCHAR(255) NOT NULL,
        `entity_phonenumber` VARCHAR(255) NOT NULL,
        `profile_image` BLOB,
        `register_for` VARCHAR(255) NOT NULL,
        `otp` INTEGER(10),
        `otp_created_at` VARCHAR(255),
        `created_at` DATETIME,
        `updated_at` DATETIME 
    )",
    "
    CREATE TABLE IF NOT EXISTS `user_subscription_details` (
        `subscription_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT(11) NOT NULL,
        `payment_id` INT(11) NOT NULL,
        `form_id` INT(11) NOT NULL,
        `purchase_form_name` VARCHAR(255) NOT NULL,
        `form_status` VARCHAR(255) NOT NULL,
        `form_progress` VARCHAR(255) NOT NULL,
        `form_image` BLOB,
        `created_at` DATETIME,
        `updated_at` DATETIME,
        FOREIGN KEY (user_id) 
    		REFERENCES user_table(user_id) 
    		ON DELETE CASCADE,
        FOREIGN KEY (payment_id)
    		REFERENCES payments_table(payment_id) 
    		ON DELETE CASCADE,
        FOREIGN KEY (form_id)
    		REFERENCES form_table(form_id) 
    		ON DELETE CASCADE
    )",
    "
    CREATE TABLE IF NOT EXISTS `form_table` (
        `form_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `form_name` VARCHAR(255) NOT NULL,
        `form_fields` VARCHAR(255) NOT NULL,
        `form_amount` VARCHAR(255) NOT NULL,
        `form_image` BLOB,
        `created_at` DATETIME,
        `updated_at` DATETIME 
    )",
    "
    CREATE TABLE IF NOT EXISTS `admin_user_table` (
        `admin_user_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `role_id` INT(11),
        `admin_fullname` VARCHAR(255) NOT NULL,
        `admin_password` VARCHAR(255) NOT NULL,
        `admin_phonenumber` VARCHAR(255) NOT NULL,
        `admin_email` VARCHAR(255) NOT NULL,
        `admin_profileimage` BLOB,
        `otp` INTEGER(10),
        `otp_created_at` VARCHAR(255),
        `created_at` DATETIME,
        `updated_at` DATETIME,
        FOREIGN KEY (role_id) 
          REFERENCES roles_table(role_id) 
          ON DELETE CASCADE
    )",
    "
    CREATE TABLE IF NOT EXISTS `payments_table` (
        `payment_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT(255),
        `form_id` INT(255),
        `order_id` VARCHAR(255) NOT NULL,
        `razorpay_payment_id` VARCHAR(255) NOT NULL,
        `signature` VARCHAR(255) NOT NULL,
        `order_amount` VARCHAR(255) NOT NULL,
        `created_at` DATETIME,
        `updated_at` DATETIME,
        FOREIGN KEY (user_id) 
          REFERENCES user_table(user_id) 
          ON DELETE CASCADE,
        FOREIGN KEY (form_id) 
          REFERENCES form_table(form_id) 
          ON DELETE CASCADE
    )",
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
        `created_at` DATETIME,
        `updated_at` DATETIME 
    )",
    "
    CREATE TABLE IF NOT EXISTS `form_response_table` (
        `response_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `form_id` INT(255) NOT NULL,
        `field_id` INT(255) NOT NULL,
        `user_id` INT(255) NOT NULL,
        `form_response_answer` VARCHAR(255) DEFAULT NULL,
        `field_frontend_id` INT(255),
        `upload_image` BLOB,
        `created_at` DATETIME,
        `updated_at` DATETIME,
        FOREIGN KEY (form_id)
          REFERENCES form_table(form_id)
          ON DELETE CASCADE,
        FOREIGN KEY (field_id)
          REFERENCES form_field_table(form_field_id)
          ON DELETE CASCADE,
        FOREIGN KEY (user_id)
          REFERENCES user_table(user_id) 
          ON DELETE CASCADE
    )",
    "
    CREATE TABLE IF NOT EXISTS `payments_table` (
        `payment_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `payment_user_id` INT(255),
        `payment_amount` INT(11),
        `created_at` DATETIME ,
        `updated_at` DATETIME ,
        FOREIGN KEY (payment_user_id)
    		REFERENCES user_table(user_id) 
    		ON DELETE CASCADE
    )",
    "
    CREATE TABLE IF NOT EXISTS `roles_table` (
        `role_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `role_name` VARCHAR(255),
        `role_description` VARCHAR(500),
        `role_permissions` VARCHAR(500),
        `created_at` DATETIME ,
        `updated_at` DATETIME
    )",
    "
    CREATE TABLE IF NOT EXISTS `integrations_table` (
        `integration_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
        `razorpay_key` VARCHAR(255) DEFAULT NULL,
        `razorpay_secret` VARCHAR(255) DEFAULT NULL,
        `send_from` VARCHAR(255) DEFAULT NULL,
        `sender_name` VARCHAR(255) DEFAULT NULL,
        `hostname` VARCHAR(255) DEFAULT NULL,
        `password` VARCHAR(255) DEFAULT NULL,
        `port` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME,
        `updated_at` DATETIME 
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