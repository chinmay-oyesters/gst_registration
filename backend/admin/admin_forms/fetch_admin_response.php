<?php

// import db connection
require("./dbcon.php");
require("./middleware.php");
require_once('../vendor/autoload.php');
use Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;

// Looing for .env at the root directory
$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    //extracting payload from jwt
    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    // retrieve required variables
    $user_id = $_POST['user_id'];
    $form_id = $_POST['form_id'];

    $sql = "SELECT field_id, field_frontend_id, form_response_answer as answer, upload_image FROM form_response_table
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
        $responses = $query->fetchAll(PDO::FETCH_OBJ);

        $responses_array = array();

        foreach ($responses as $response) {
            if($response->answer === NULL || $response->answer === " " || $response->answer === ""){
                $answer = $response->upload_image;
            }else{
                $answer = $response->answer;
            }

            array_push($responses_array,
                [
                    "filed_id" => $response->field_id,
                    "field_frontend_id" => $response->field_frontend_id,
                    "answer" => $answer
                ]
            );
        }
        
        $status = 200;
        $response = [
            "msg" => "Responses fetched successsfully",
            "response" => $responses_array
        ];
    }

}