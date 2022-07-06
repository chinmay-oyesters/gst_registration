<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    //fetch details from market
    $sql = "SELECT role_id, role_name, role_description, role_permissions FROM roles_table ORDER BY role_id DESC";
    $query = $con -> prepare($sql);

    if($query->execute()){
        
        $roles = $query->fetchAll(PDO::FETCH_OBJ);

        $roles_array = array();

        foreach ($roles as $role) {
            $role->role_permissions = explode(",", $role->role_permissions);
            array_push($roles_array,
                [
                    "role_id" => $role->role_id,
                    "role_name" => $role->role_name,
                    "role_description" => $role->role_description,
                    "role_permissions" => $role->role_permissions
                ]
            );
        }
        
        $status = 200;
        $response = [
            "msg" => "Roles Fetched",
            "roles" => $roles_array
        ];
        
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Roles can't be fetched"
        ];
        
    }
    

}