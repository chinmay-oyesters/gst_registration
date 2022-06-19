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
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    //extracting payload from jwt
    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    $sql = "SELECT username, entity_name, entity_pan, entity_phonenumber, entity_email, person_name, person_email, person_phonenumber, profile_image FROM user_table WHERE user_id=:user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() === 0){

        $status = 203;
        $response = [
            "msg" => "Internal server error"
        ];

    }else{

        $user_profile = $query->fetchAll(PDO::FETCH_OBJ)[0];

        if($user_profile){
            $status = 200;
            $response = [
                "msg" => "User profile fetched successfully",
                "user_profile" => $user_profile
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "User profile can't be fetched now"
            ];
        }

    }
}