<?php

require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    //find the form to delete
    $form_id = $_POST['form_id'];

    try{
        $sql = "DELETE FROM form_table WHERE form_id = :form_id";
        $query = $con -> prepare($sql);
        $query->bindparam("form_id", $form_id, PDO::PARAM_STR);
    
        if(!$query->execute()){
            $status = 203;
            $response = [
                "msg" => "Bad request: Form can't be deleted"
            ];
        }

        $sql = "DELETE FROM user_subscription_details WHERE form_id = :form_id";
        $query = $con -> prepare($sql);
        $query->bindparam("form_id", $form_id, PDO::PARAM_STR);
    
        if(!$query->execute()){
            $status = 203;
            $response = [
                "msg" => "Bad request: Form can't be deleted for subscribed user"
            ];
        }

        $status = 200;
        $response = [
            "msg" => "Form got deleted."
        ];
    
    }catch(Exception $e){
        $status = 203;
        $response = [
            "msg" => "Error: Form can't be deleted",
            "error" => $e->getMessage()
        ];
    }
    
}
