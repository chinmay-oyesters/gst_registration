<?php

// import db connection
require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    // retrieve required variables
    $form_id = $_POST['form_id'];
    $form_name = $_POST['form_name'];
    $form_fields = json_encode($_POST['form_fields']);
    $datetime = date("Y-m-d H:i:s");

    // query to edit the form field in the table
    $sql = "UPDATE form_table SET  
    form_name =  :form_name,
    form_fields = :form_fields,
    updated_at = :updated_at
    WHERE form_id = :form_id";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':form_name', $form_name, PDO::PARAM_STR);
    $query->bindParam(':form_fields', $form_fields, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);


    // success
    if($query->execute()){
        $form = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Form created successfully",
            "form" => [
                "form_id" => $form_id,
                "form_name" => $form_name
            ]
        ];
    }
    // failure
    else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Form creation failed"
        ];
    }

}