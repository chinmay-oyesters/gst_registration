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
    $staff = (object)[];

    try{
        //fetch details from market
        $sql = "SELECT aut.admin_user_id, rt.role_id, rt.role_name, aut.admin_fullname, aut.admin_phonenumber, aut.admin_email FROM admin_user_table as aut 
        LEFT JOIN roles_table as rt 
        ON aut.role_id = rt.role_id
        WHERE aut.admin_user_id != :admin_user_id
        ORDER BY admin_user_id DESC";

        $query = $con -> prepare($sql);
        $query->bindParam(':admin_user_id', $payload->admin_user_id, PDO::PARAM_STR);

        if($query->execute()){
            
            $staff_details = $query->fetchAll(PDO::FETCH_ASSOC);

            $staff->staff_details = $staff_details;
        }else{
            $status = 203;
            $response = [
                "msg" => "Staff details can't be fetched now."
            ];            
        }
        
        $sql = "SELECT role_id, role_name FROM roles_table ORDER BY role_id DESC";
        $query = $con -> prepare($sql);

        if($query->execute()){
            
            $role_details = $query->fetchAll(PDO::FETCH_OBJ);
            
            $staff->role_details = $role_details;
            
        }else{
            
            $status = 203;
            $response = [
                "msg" => "Role details can't be fetched"
            ];
            
        }

        $status = 200;
        $response = [
            "msg" => "Staff fetched successfully",
            "staff" => $staff
        ];

    }catch(Exception $e){
        $status = 203;
        $response = [
            "msg" => "Staff can't be fetched.",
            "error" => $e->getMessage()
        ];
    }
}