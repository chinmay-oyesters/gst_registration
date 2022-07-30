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
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    // $secret_key = "bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=";
    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));
    
    // retrieve required variables
    $admin_email = $_POST['admin_email'];
    $admin_fullname = $_POST['admin_fullname'];
    $admin_phonenumber = $_POST['admin_phonenumber'];
    
    
    $sql = "SELECT * FROM admin_user_table WHERE admin_user_id=:admin_user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':admin_user_id', $payload->admin_user_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() != 0){

        $sql = "UPDATE admin_user_table SET 
            admin_fullname = :admin_fullname,
            admin_email = :admin_email,
            admin_phonenumber=:admin_phonenumber, 
            updated_at = :updated_at 
            WHERE admin_user_id=:admin_user_id";
        $query = $con -> prepare($sql);
        $query->bindParam(':admin_fullname', $admin_fullname, PDO::PARAM_STR);
        $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
        $query->bindParam(':admin_phonenumber', $admin_phonenumber, PDO::PARAM_STR);
        $query->bindParam(':admin_user_id', $payload->admin_user_id, PDO::PARAM_STR);
        $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
        if($query->execute()){
            $user_profile = $query->fetchAll(PDO::FETCH_OBJ);
            $status = 200;
            $response = [
                "msg" => "Admin profile updated successfully",
                "admin_name" => $admin_fullname
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "Admin profile can't be updated now"
            ];
        }

    }else{

        $status = 203;
        $response = [
            "msg" => "Internal Server Error"
        ];

    }
}