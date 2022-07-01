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
$username = $_POST['username'];
$user_password = md5($_POST['user_password']);

// looking for the user in database
$sql = "SELECT * FROM user_table WHERE user_table.username=:username";
$query = $con -> prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
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
        $expire     = $issuedAt->modify('+60 minutes')->getTimestamp();             // Add 60 seconds
        $serverName = "http://localhost/gst_registration/backend/user_signin";     // Retrieved from filtered POST data

        $data = [
            'iat'  => $issuedAt->getTimestamp(),                        
            'iss'  => $serverName,                                      
            'nbf'  => $issuedAt->getTimestamp(),                        
            'exp'  => $expire,                                          
            'user_id' =>  $user->user_id,                               
            'username' => $user->username,                          
            'user_phonenumber' => $user->user_phonenumber          
        ];

        $jwt =  JWT::encode(
            $data,
            $SECRET_KEY,
            'HS512'
        );
        // sending jwt token to frontend with cookies
        setcookie("user_jwt", $jwt, time()+ (86400 * 30), "/","", 0); //86400*7 expiry time to 30 days

        $status = 200;
        $response = [
            "msg" => "User authenticated successfully"
        ];

    }else{
        $status = 203;
        $response = [
            "msg" => "Unauthorized - Password does not match"
        ];
    }

}