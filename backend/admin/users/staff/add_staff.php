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

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){
    $admin_email = $_POST['admin_email'];
    $role_id = $_POST['role_id'];
    $admin_fullname = $_POST['admin_fullname'];
    $admin_phonenumber = $_POST['admin_phonenumber'];
    $admin_password = md5($_POST['admin_password']);
    $datetime = date("Y-m-d H:i:s");

    //fetch details from market
    $sql = "INSERT INTO admin_user_table (admin_email, role_id, admin_fullname, admin_phonenumber, admin_password, created_at, updated_at) 
    VALUES (:admin_email, :role_id, :admin_fullname, :admin_phonenumber, :admin_password, :created_at, :updated_at)";
    $query = $con -> prepare($sql);
    $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
    $query->bindParam(':admin_fullname', $admin_fullname, PDO::PARAM_STR);
    $query->bindParam(':role_id', $role_id, PDO::PARAM_STR);
    $query->bindParam(':admin_phonenumber', $admin_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':admin_password', $admin_password, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
    if($query->execute()){
        $status = 200;
        $response = [
            "msg" => "New staff added successfully"
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error : New staff not added"
        ];       
    }

}