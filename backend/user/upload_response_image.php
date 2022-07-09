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


        $sql = "SELECT response_id FROM form_response_table WHERE form_id=:form_id AND field_id=:field_id AND user_id=:user_id AND
        field_frontend_id = :field_frontend_id";
        $query = $con -> prepare($sql);
        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->bindParam(':field_id', $field_id, PDO::PARAM_STR);
        $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $query->bindParam(':field_frontend_id', $field_frontend_id, PDO::PARAM_STR);
        $query->execute();
        if($query->rowCount() === 0){
            $sql = "INSERT INTO form_response_table (user_id, form_id, field_id, field_frontend_id, upload_image, created_at, updated_at) VALUES
            (:user_id, :form_id, :field_id, :field_frontend_id, :upload_image, :created_at, :updated_at)";
            $query = $con -> prepare($sql);
            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->bindParam(':field_id', $field_id, PDO::PARAM_STR);
            $query->bindParam(':field_frontend_id', $field_frontend_id, PDO::PARAM_STR);
            $query->bindParam(':upload_image', $upload_image, PDO::PARAM_STR);
            $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
            $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
            
    
            if($query->execute()){
                $status = 200;
                $response = [
                    "msg" => "Response saved successfully",
                    "upload_image" => $upload_image
                ];           
            }else{
                
                $status = 203;
                $response = [
                    "msg" => "Responses could not be saved"
                ];
            }
        }else{
            $response_id = $query->fetchAll(PDO::FETCH_ASSOC)[0]['response_id'];
            $datetime = date("Y-m-d H:i:s");

            $sql = "UPDATE form_response_table SET 
            upload_image = :upload_image
            WHERE response_id = :response_id AND
            user_id = :user_id AND
            form_id = :form_id AND
            field_id = :field_id AND
            field_frontend_id = :field_frontend_id AND
            updated_at = :updated_at";  
            $query = $con->prepare($sql);
            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->bindParam(':field_id', $field_id, PDO::PARAM_STR);
            $query->bindParam(':field_frontend_id', $field_frontend_id, PDO::PARAM_STR);
            $query->bindParam(':upload_image', $upload_image, PDO::PARAM_LOB);
            $query->bindParam(':updated_at', $updated_at, PDO::PARAM_STR);
            $query->bindParam(':response_id', $response_id, PDO::PARAM_STR);

            if($query->execute()){
                $status = 200;
                $response = [
                    "msg" => "Image uploaded successfully",
                    "upload_image" => $upload_image
                ]; 
            }else{
                
                $status = 203;
                $response = [
                    "msg" => "Image can't be saved to database",
                    "upload_image" => $upload_image
                ];
            }
        }
    }else{
        $status  = 203;
        $response = [
            "msg" => "Reduce file size to 2 MB."
        ];
    }
}