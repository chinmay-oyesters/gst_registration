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
    
    //extrating param from files variable
    $temp = explode('.', $_FILES["choosefile"]["name"]);
    $extn = strtolower(end($temp));
    // Filetype is correct. Check size
    if($_FILES["choosefile"]["size"] < 2000000) {
        
        //extracting data from uploaded file
        $filename = $_FILES["choosefile"]["name"];
        $tempname = $_FILES["choosefile"]["tmp_name"];
        $upload_image = base64_encode(file_get_contents($tempname));

        //taking param name from query parameters

        $query_param = $_GET[0];

        $form_id = $query_param['form_id'];
        $field_id = $query_param['field_id'];
        $field_frontend_id = $query_param['field_frontend_id'];
        $user_id = $payload->user_id;
        
    
        // query to insert the submitted data
        $sql = "UPDATE form_response_table SET 
        upload_image = :upload_image WHERE 
        user_id = :user_id AND
        form_id = :form_id AND
        field_id = :field_id AND
        field_frontend_id = :field_frontend_id";
        $query = $con -> prepare($sql);
        $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $query->bindParam(':upload_image', $upload_image, PDO::PARAM_LOB);
        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->bindParam(':field_id', $field_id, PDO::PARAM_STR);
        $query->bindParam(':field_frontend_id', $field_frontend_id, PDO::PARAM_STR);
        if($query->execute()){
            $status = 200;
            $response = [
                "msg" => "Image Uploaded Succesfully",
                "profile_image" => $upload_image
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "Image can't be saved to database"
            ];
        }
    }else{
        $status  = 203;
        $response = [
            "msg" => "Reduce file size to 2 MB."
        ];
    }
}