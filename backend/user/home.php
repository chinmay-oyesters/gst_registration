<?php

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

    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));
    $user_id = $payload->user_id;
    $user_subscriptions = [];
    $home_data = [];
    
    $sql = "SELECT purchase_form_name, form_status, form_progress FROM user_subscription_details WHERE user_id=:user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    
    if($query->execute()){

        $subscribed_forms = $query->fetchAll(PDO::FETCH_ASSOC);

        //storing the for details for user and storing them in user_subscription array 
        foreach($subscribed_forms as $subscribed_form){
            array_push($user_subscriptions, $subscribed_form['purchase_form_name']);
            array_push($home_data, $subscribed_form);
        }

        // check which subscription user has not availed
        $sql = "SELECT form_name FROM forms_table ";
        $query = $con -> prepare($sql);

        if($query->execute()){
            $not_subscribed_forms = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach($not_subscribed_forms as $not_subscribed_form){
                if(!in_array($not_subscribed_form['form_name'], $user_subscriptions)){
                    
                    $form_data = [];
                    $form_data['form_name'] = $not_subscribed_form['form_name'];
                    $form_data['form_status'] = "Pay to Avail the Service";
                    $form_data['form_progress'] = "NULL";
                    
                    array_push($home_data, $form_data);
                }
                $status = 200;
                $response = [
                    "msg" => "Home data fetched successfully",
                    "home_data" => $home_data
                ];                   
            }
        }else{
            $status = 203;
            $response = [
                "msg" => "Internal Server Error: Can't fetch not subscribed forms data"
            ]; 
        }
    
    }else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error: Can't fetch subscribed forms data"
        ];       
    }
}
