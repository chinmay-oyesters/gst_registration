<?php

// import db connection
require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    $form_id = $_POST['form_id'];
    $subscribed_user = [];

    $sql = "SELECT user_id, form_status, form_progress FROM user_subscription_details WHERE form_id = :form_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() === 0){
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Users not fetched"
        ];

    }else{
        $form_details = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($form_details as $form_detail){
            $user_id = $form_detail['user_id'];
            $sql = "SELECT user_fullname, user_email, user_phonenumber, entity_fullname FROM user_table WHERE user_id = :user_id";
            $query = $con -> prepare($sql);
            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $query->execute();

            $user_details = $query->fetchAll(PDO::FETCH_ASSOC)[0];

            array_push($subscribed_user,
                [
                    "user_fullname" => $user_details['user_fullname'],
                    "user_email" => $user_details['user_email'],
                    "user_phonenumber" => $user_details['user_phonenumber'],
                    "entity_fullname" => $user_details['entity_fullname'],
                    "form_status" => $form_detail['form_status'],
                    "form_progress" => $form_detail['form_progress']
                ]
            );


        }

        $status = 200;
        $response = [
            "msg" => "Users fetched successfully",
            "subscribed_user" => $subscribed_user
        ];
    }


}