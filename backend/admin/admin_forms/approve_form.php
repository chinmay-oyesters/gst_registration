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

    // retrieve required variables
    $form_id = $_POST['form_id'];
    $user_id = $_POST['user_id'];
    $datetime = date("Y-m-d H:i:s");
    $form_status = "Approved";

    // query to edit the form field in the table
    $sql = "UPDATE user_subscription_details SET  
    form_status =  :form_status,
    updated_at = :updated_at
    WHERE form_id = :form_id 
    AND user_id = :user_id";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $query->bindParam(':form_status', $form_status, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);

    // success
    if($query->execute()){
        $status = 200;
        $response = [
            "msg" => "Approved",
            "form" => [
                "form_id" => $form_id,
                "form_status" => $form_status
            ]
        ];
    }
    // failure
    else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Form not approved"
        ];
    }

}