<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $role_id = $_POST['role_id'];
    $role_name = $_POST['role_name'];
    $role_description = $_POST['role_description'];
    $role_permissions = $_POST['role_permissions'];
    $datetime = date("Y-m-d H:i:s");

    //array to string conversion of role permissions
    $role_permissions = implode(",", $role_permissions);

    $sql = "UPDATE roles_table SET 
    role_name = :role_name, 
    role_description = :role_description, 
    role_permissions = :role_permissions, 
    updated_at = :updated_at 
    WHERE role_id = :role_id";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':role_id', $role_id, PDO::PARAM_STR);
    $query->bindParam(':role_name', $role_name, PDO::PARAM_STR);
    $query->bindParam(':role_description', $role_description, PDO::PARAM_STR);
    $query->bindParam(':role_permissions', $role_permissions, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);

    // success
    if($query->execute()){
        $form = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Role edited successfully"
        ];
    }
    // failure
    else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Role can't be edited"
        ];
    }



}