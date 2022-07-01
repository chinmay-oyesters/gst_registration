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

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));
    
    $old_password = md5($_POST['old_password']);
    $new_password = $_POST['new_password'];
    $datetime = date("Y-m-d H:i:s");
    
    $sql = "SELECT * FROM admin_user_table WHERE admin_user_id=:admin_user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':admin_user_id', $payload->admin_user_id, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() === 0){
    
        $status = 203;
        $response = [
            "msg" => "Bad Request - User does not exist"
        ];
    
    }else{
    
        $admin = $query->fetchAll(PDO::FETCH_OBJ)[0];
    
        if($admin->admin_password === $old_password ){
            $new_password = md5($new_password);
            $sql = "UPDATE admin_user_table SET admin_password=:new_password,
            updated_at = :updated_at WHERE admin_user_id=:admin_user_id";
            $query = $con -> prepare($sql);
            $query->bindParam(':new_password', $new_password, PDO::PARAM_STR);
            $query->bindParam(':admin_user_id', $payload->admin_user_id, PDO::PARAM_STR);
            $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
            if($query->execute()){
                $status = 200;
                $response = [
                    "msg" => "Password updated successfully"
                ];
            }else{
                $status = 203;
                $response = [
                    "msg" => "Password not updated"
                ];
            }
        }else{
            $status = 203;
            $response = [
                "msg" => "Old password does not match"
            ];
        }
    
    }
}

