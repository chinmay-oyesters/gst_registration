<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');
use Firebase\JWT\JWT;
use Dotenv\Dotenv;

// Looing for .env at the root directory
$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$user_email = $_POST['user_email'];
$user_password = md5($_POST['user_password']);

// looking for the user in database
$sql = "SELECT * FROM user_table WHERE user_table.user_email=:user_email";
$query = $con -> prepare($sql);
$query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
$query->execute();

if($query->rowCount() === 0){

    $status = 203;
    $response = [
        "msg" => "Bad Request - User does not exist"
    ];

}else{

    // fetching row of that user
    $user = $query->fetchAll(PDO::FETCH_OBJ)[0];

    if($user->user_password === $user_password){

        // if user is authenticated then generate a token with JWT
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+30 days')->getTimestamp();             // Add 30 days
        $serverName = "http://localhost/gst_registration/backend/user_signin";     // Retrieved from filtered POST data

        $data = [
            'iat'  => $issuedAt->getTimestamp(),                        
            'iss'  => $serverName,                                      
            'nbf'  => $issuedAt->getTimestamp(),                        
            'exp'  => $expire,                                          
            'user_id' =>  $user->user_id,                               
            'user_email' => $user->user_email,                          
            'user_phonenumber' => $user->user_phonenumber          
        ];

        $jwt =  JWT::encode(
            $data,
            $SECRET_KEY,
            'HS512'
        );
        // sending jwt token to frontend with cookies
        setcookie("user_jwt", $jwt, time()+ (86400 * 30), "/","", 0); //86400*7 expiry time to 7 days
        

        $status = 200;
        $response = [
            "msg" => "User authenticated successfully",
            "user_profile" => [
                "user_id" => $user->user_id,
                "user_name" => $user->user_fullname,
                "user_image" => $user->profile_image
            ]
        ];

    }else{
        $status = 203;
        $response = [
            "msg" => "Unauthorized - Password does not match"
        ];
    }

}