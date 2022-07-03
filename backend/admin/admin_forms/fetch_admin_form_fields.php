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

    $sql = "SELECT form_field_id as field_id, form_field_title as field_name, form_field_tag1 as field_tag, form_field_type as field_type FROM form_field_table";
    $query = $con -> prepare($sql);
    $query->execute();

    if($query->rowCount() === 0){
        $status = 203;
        $response = [
            "msg" => "No fields found"
        ]; 
    }else{
        $fields = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $status = 200;
        $response = [
            "msg" => "Fields fetched successsfully",
            "fields" => $fields
        ];
    }

}