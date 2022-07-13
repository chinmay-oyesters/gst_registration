<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){
    //find the market to delete
    $role_id = $_POST['role_id'];

    //fetch details from market
    $sql = "SELECT admin_user_id FROM admin_user_table WHERE role_id = :role_id";
    $query = $con -> prepare($sql);
    $query->bindparam("role_id", $role_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() === 0){

        $sql = "DELETE FROM roles_table
        WHERE role_id = :role_id";
        $query = $con -> prepare($sql);
        $query->bindparam("role_id", $role_id, PDO::PARAM_STR);
    
        if($query->execute()){
            $status = 200;
            $response = [
                "msg" => "Role removed successfully."
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "Role can't be removed now."
            ];
        }

    }else{
            $status = 203;
            $response = [
                "msg" => "Bad request: Role assigned to a staff member first withdraw the role"
            ];
    }
}