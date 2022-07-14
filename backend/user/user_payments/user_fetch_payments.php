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
    
    
    $sql = "SELECT ft.form_name, pt.order_id, pt.order_amount, pt.created_at FROM payments_table as pt 
    LEFT JOIN form_table as ft
    ON ft.form_id = pt.form_id
    WHERE user_id=:user_id
    ORDER BY (pt.payment_id) DESC";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
    $query->execute();

    if($query->execute()){
        $user_payments = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "User Payments fetched successfully",
            "user_payments" => $user_payments
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "User payments can't be fetched now"
        ];
    }
}