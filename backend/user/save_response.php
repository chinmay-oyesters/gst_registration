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

    // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    // retrieve required variables
    $user_id = $payload->user_id;
    $field_id = $_POST['field_id'];
    $form_id = $_POST['form_id'];
    $field_frontend_id = $_POST['field_frontend_id'];
    $form_response_answer = $_POST['form_response_answer'];
    $datetime = date("Y-m-d H:i:s");

    $sql = "INSERT INTO form_response_table (user_id, form_id, field_id, field_frontend_id, form_response_answer, created_at, updated_at) VALUES
    (:user_id, :form_id, :field_id, :field_frontend_id, :form_response_answer, :created_at, :updated_at)";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':field_id', $field_id, PDO::PARAM_STR);
    $query->bindParam(':field_frontend_id', $field_frontend_id, PDO::PARAM_STR);
    $query->bindParam(':form_response_answer', $form_response_answer, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
    

    if($query->execute()){
        $status = 200;
        $response = [
            "msg" => "Responses saved successsfully"
        ]; 
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Responses could not be saved"
        ];
    }

}