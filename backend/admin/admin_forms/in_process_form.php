<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $form_id = $_POST['form_id'];
    $user_id = $_POST['user_id'];
    $datetime = date("Y-m-d H:i:s");

    $form_status = "In Progress at concern department";
    
    $sql = "UPDATE user_subscription_details SET 
    form_status = :form_status,
    updated_at = :updated_at
    WHERE form_id = :form_id
    AND user_id = :user_id";
    $query = $con->prepare($sql);
    $query->bindParam(':form_status', $form_status, PDO::PARAM_STR);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $query->bindParam(':updated_at', $datetime, PDO::PARAM_STR);
    if($query->execute()){
        $status = 200;
        $response = [
            "msg" => "Form status updated"
        ];			
    }else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error: Form status can't be updated"
        ];
    }
    
}