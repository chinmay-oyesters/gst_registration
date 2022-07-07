<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

        // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    // retrieve required variables
    $form_id = $_POST['form_id'];


    $sql = "SELECT form_id, form_name, form_image, form_fields FROM form_table WHERE form_id = :form_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);

    if($query->execute()){

        $forms = $query->fetchAll(PDO::FETCH_ASSOC);

        $status = 200;
        $response = [
            "msg" => "Forms fetched successfully",
            "forms" => $forms
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "Internal server error: Forms can't be fetched"
        ];
    }

} 