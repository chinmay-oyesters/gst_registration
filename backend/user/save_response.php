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

    $response_list = $_POST['response_list'];
    $user_id = $payload->user_id;
    $field_ids = [];

    foreach($response_list as $response){
        // retrieve required variables
        $field_id = $response['field_id'];
        $form_id = $response['form_id'];
        $field_frontend_id = $response['field_frontend_id'];
        $form_response_answer = $response['form_response_answer'];
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
            array_push($field_ids, intval($field_id));
        }else{
            
            $status = 203;
            $response = [
                "msg" => "Responses could not be saved"
            ];
        }
    }

    // save form status in user subscription details

    $sql = "SELECT form_fields FROM form_table WHERE form_id=:form_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);

    //updating form status and status in user subscription details table
    if($query->execute()){
        $form_status = "";
        $form_fields = $query->fetchAll(PDO::FETCH_ASSOC)[0]['form_fields'];
        $form_fields_count = count(json_decode($form_fields));
        $common_fields = array_intersect(json_decode($form_fields), $field_ids);
        $common_fields_count = count($common_fields);
        $form_progress = intval(($common_fields_count/$form_fields_count)*100);
        if($form_progress<100){
            $form_status = "In Progress";
        }else{
            $form_status = "Completed";
        }

        $sql = "UPDATE user_subscription_details SET 
        form_status = :form_status,
        form_progress = :form_progress,
        updated_at = :updated_at
        WHERE user_id=:user_id AND form_id=:form_id";
        $query = $con -> prepare($sql);
        $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->bindParam(':form_status', $form_status, PDO::PARAM_STR);
        $query->bindParam(':form_progress', $form_progress, PDO::PARAM_STR);
        $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
        if($query->execute()){
            $status = 200;
            $response = [
                "msg" => "User response saved successfully"
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "Form status and progress could not be saved"
            ];
        }
    }else{
        $status = 203;
        $response = [
            "msg" => "Form fields can't be fetched"
        ];      
    }
}
