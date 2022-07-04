<?php

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
    $user_id = $_POST['user_id'];
    $form_id = $_POST['form_id'];

    $sql = "SELECT field_id, field_frontend_id, form_response_answer as answer FROM form_response_table
    WHERE form_id=:form_id AND user_id=:user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() === 0){
        $status = 203;
        $response = [
            "msg" => "No responses  found"
        ]; 
    }else{
        $response = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $status = 200;
        $response = [
            "msg" => "Responses fetched successsfully",
            "response" => $response
        ];
    }

}