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

    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    //fetch details from market
    $sql = "SELECT aut.admin_user_id, rt.role_id, rt.role_name, aut.admin_fullname, aut.admin_phonenumber, aut.admin_email FROM admin_user_table as aut 
    LEFT JOIN roles_table as rt 
    ON aut.role_id = rt.role_id
    WHERE aut.admin_user_id != :admin_user_id
    ORDER BY admin_user_id DESC";

    $query = $con -> prepare($sql);
    $query->bindParam(':admin_user_id', $payload->admin_user_id, PDO::PARAM_STR);

    if($query->execute()){
        
        $staff = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $status = 200;
        $response = [
            "msg" => "Staff Fetched Successfully",
            "staff" => $staff
        ];
        
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Staff can't be fetched"
        ];
        
    }
}