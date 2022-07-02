<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    //fetch details from market
    $sql = "SELECT role_id, role_name, role_description, role_permissions FROM roles_table ORDER BY role_id DESC";
    $query = $con -> prepare($sql);

    if($query->execute()){
        
        $roles = $query->fetchAll(PDO::FETCH_OBJ);
        
        $status = 200;
        $response = [
            "msg" => "Roles Fetched",
            "roles" => $roles
        ];
        
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Roles can't be fetched"
        ];
        
    }
    

}