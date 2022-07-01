<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $role_name = $_POST['role_name'];
    $role_description = $_POST['role_description'];
    $role_permissions = $_POST['role_permissions'];

    $sql = "INSERT INTO roles_table (role_name, role_description, role_permissions ) VALUES (:role_name, :role_description, :role_permissions)";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':role_name', $role_name, PDO::PARAM_STR);
    $query->bindParam(':role_description', $role_description, PDO::PARAM_STR);
    $query->bindParam(':role_permissions', $role_permissions, PDO::PARAM_STR);

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