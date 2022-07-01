<?php

// import db connection
require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;

// Looing for .env at the root directory
$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    // $secret_key = "bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=";
    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));
    
    // retrieve required variables
    $user_email = $_POST['user_email'];
    $entity_fullname = $_POST['entity_fullname'];
    $entity_pan = $_POST['entity_pan'];
    $entity_phonenumber = $_POST['entity_phonenumber'];
    $entity_email = $_POST['entity_email'];
    $user_fullname = $_POST['user_fullname'];
    $user_phonenumber = $_POST['user_phonenumber'];
    
    
    $sql = "SELECT * FROM user_table WHERE user_table.user_id=:user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() != 0){

        $sql = "UPDATE user_table SET 
            user_email = :user_email,
            entity_fullname = :entity_fullname,
            entity_pan = :entity_pan,
            entity_phonenumber=:entity_phonenumber,
            entity_email = :entity_email,
            user_fullname = :user_fullname,
            user_phonenumber = :user_phonenumber
            WHERE user_id=:user_id";
        $query = $con -> prepare($sql);
        $query->bindParam(':entity_fullname', $entity_fullname, PDO::PARAM_STR);
        $query->bindParam(':entity_pan', $entity_pan, PDO::PARAM_STR);
        $query->bindParam(':entity_phonenumber', $entity_phonenumber, PDO::PARAM_STR);
        $query->bindParam(':entity_email', $entity_email, PDO::PARAM_STR);
        $query->bindParam(':user_fullname', $user_fullname, PDO::PARAM_STR);
        $query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
        $query->bindParam(':user_phonenumber', $user_phonenumber, PDO::PARAM_STR);
        $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
        if($query->execute()){
            $user_profile = $query->fetchAll(PDO::FETCH_OBJ);
            $status = 200;
            $response = [
                "msg" => "User profile updated successfully"
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "User profile can't be updated now"
            ];
        }

    }else{

        $status = 203;
        $response = [
            "msg" => "Internal Server Error"
        ];

    }
}