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

    $_POST = json_decode(file_get_contents("php://input"), true);

    //extracting payload from jwt
    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    $user_id = $payload->user_id;
    $order_id = $_POST['order_id'];
    $form_id = $_POST['form_id'];
    $signature = $_POST['signature'];
    $order_amount = $_POST['order_amount'];
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $datetime = date("Y-m-d H:i:s");

    $sql = "INSERT INTO payments_table (user_id, order_id, form_id, signature, order_amount, razorpay_payment_id, created_at, updated_at) VALUES 
    (:user_id, :order_id, :form_id, :signature, :order_amount, :razorpay_payment_id, :created_at, :updated_at)";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $query->bindParam(':order_id', $order_id, PDO::PARAM_STR);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':signature', $signature, PDO::PARAM_STR);
    $query->bindParam(':order_amount', $order_amount, PDO::PARAM_STR);
    $query->bindParam(':razorpay_payment_id', $razorpay_payment_id, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
    

    if($query->execute()){

        $form_status = "Already paid";
        $form_progress = "0";
        $sql = "SELECT MAX(payment_id) as payment_id from payments_table";
        $query = $con -> prepare($sql);
        $query->execute();
        $payment_id = $query->fetchAll(PDO::FETCH_ASSOC)[0]['payment_id'];

        $sql = "SELECT form_name from form_table WHERE form_id = :form_id ";
        $query = $con -> prepare($sql);
        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->execute();

        $purchase_form_name = $query->fetchAll(PDO::FETCH_ASSOC)[0]['form_name'];

        $sql = "INSERT INTO user_subscription_details (user_id, payment_id, form_id, purchase_form_name, form_progress, form_status, created_at, updated_at) VALUES 
        (:user_id, :payment_id, :form_id, :purchase_form_name, :form_progress, :form_status, :created_at, :updated_at)";
        $query = $con -> prepare($sql);
        $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $query->bindParam(':payment_id', $payment_id, PDO::PARAM_STR);
        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->bindParam(':purchase_form_name', $purchase_form_name, PDO::PARAM_STR);
        $query->bindParam(':form_progress', $form_progress, PDO::PARAM_STR);
        $query->bindParam(':form_status', $form_status, PDO::PARAM_STR);
        $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
        $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);

        if($query->execute()){

            $status = 200;
            $response = [
                "msg" => "Subscription saved successfully"
            ];
    
        }else{
    
            $status = 203;
            $response = [
                "msg" => "Internal server error : Subscription not saved"
            ];            
    
        }

    }else{
        $status = 203;
        $response = [
            "msg" => "Payments success status can't be saved"
        ];
    }
    
    
}