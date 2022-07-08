<?php

// import db connection
require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;
use Razorpay\Api\Api;

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
    $user_id = $payload->user_id;
    $form_id = $_POST['form_id'];
    
    
    // $sql = "SELECT form_amount FROM form_table WHERE form_id=:form_id";
    // $query = $con -> prepare($sql);
    // $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    // $query->execute();

    // if($query->rowCount() === 0){

    //     $status = 203;
    //     $response = [
    //         "msg" => "Internal Server Error: Order ID creation failed"
    //     ];       

    // }else{

        // $form_amount = $query->fetchAll(PDO::FETCH_ASSOC)[0]['form_amount'];
        $form_amount = 100;

        //extracting razorpay details
        $sql = "SELECT razorpay_key, razorpay_secret FROM integrations_table";
        $query = $con -> prepare($sql);

        if($query->execute()){

            $razorpay = $query->fetchAll(PDO::FETCH_ASSOC)[0];
            
            $razorpay_key = $razorpay['razorpay_key'];
            $razorpay_secret = $razorpay['razorpay_secret'];
            // print_r($razorpay_secret);

            $api = new Api($razorpay_key, $razorpay_secret);
            $order_details = $api->order->create(array('amount' => $form_amount, 'currency' => 'INR'));
            
            

            $status = 200;
            $response = [
                "msg" => "Order ID created successfully",
                "order_id" => $order_details['id']
            ];           
        }else{
            
            $status = 203;
            $response = [
                "msg" => "Internal Server Error"
            ];
            
        }


        

    // }
}