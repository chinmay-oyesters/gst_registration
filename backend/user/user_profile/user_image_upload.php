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

    //extracting payload from jwt
    // $secret_key = "bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=";
    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    
    //Checking files specifcation
    //checking extension of file
    $temp = explode('.', $_FILES["choosefile"]["name"]);
    $extn = strtolower(end($temp));
    if(($extn == "jpg") || ($extn == "png") ) {
        // Filetype is correct. Check size
        if($_FILES["choosefile"]["size"] < 2000000) {
            //extracting data from uploaded file
            $filename = $_FILES["choosefile"]["name"];
            $tempname = $_FILES["choosefile"]["tmp_name"];
            $image = base64_encode(file_get_contents($tempname));
        
        
            // query to insert the submitted data
            $sql = "UPDATE user_table SET profile_image = :profile_image WHERE user_id = :user_id";
            $query = $con -> prepare($sql);
            $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
            $query->bindParam(':profile_image', $image, PDO::PARAM_LOB);
            if($query->execute()){
                $status = 200;
                $response = [
                    "msg" => "Image Uploaded Succesfully"
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
    }else{
        $status = 203;
        $response = [
            "msg" => "File is not in specified format."
        ];
    }
}