<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $role_name = $_POST['role_name'];
    $role_description = $_POST['role_description'];
    $role_permissions = $_POST['role_permissions'];
    $datetime = date("Y-m-d H:i:s");

    //array to string conversion of role permissions
    $role_permissions = implode(",", $role_permissions);

    // print_r($role_permissions);

    $sql = "INSERT INTO roles_table (role_name, role_description, role_permissions, created_at, updated_at) 
    VALUES (:role_name, :role_description, :role_permissions, :created_at, :updated_at)";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':role_name', $role_name, PDO::PARAM_STR);
    $query->bindParam(':role_description', $role_description, PDO::PARAM_STR);
    $query->bindParam(':role_permissions', $role_permissions, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);

    // success
    if($query->execute()){
        $form = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Role added successfully"
        ];
    }
    // failure
    else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Role creation failed"
        ];
    }



}