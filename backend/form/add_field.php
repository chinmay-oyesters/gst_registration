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
    $field_tag1 = $_POST['field_tag1'];
    $field_title = $_POST['field_title'];
    $field_type = $_POST['field_type'];
    $field_associated_to = json_encode($_POST['field_associated_to']);
    $field_required = $_POST['field_required'];
    $datetime = date("Y-m-d H:i:s");

    if(isset($_POST['field_values'])){
        $field_values = json_encode($_POST['field_values']);
    }else{
        $field_values = NULL;
    }

    if(isset($_POST['field_validation'])){
        $field_validation = $_POST['field_validation'];
    }else{
        $field_validation = NULL;
    }

    // looking for the max form field id in database
    // need to return id for new field that will be created
    // so manually replicating AUTO_INCREMENT functionality of mysql
    $sql = "SELECT max(form_field_id) AS form_field_id FROM form_field_table";
    $query = $con -> prepare($sql);
    $query->execute();

    $form_field_id = $query->fetchAll(PDO::FETCH_OBJ)[0]->form_field_id;

    // if the max form field is not defined, make it 0
    if($form_field_id == NULL){
        $form_field_id = 0;
    }
    // increment the id for adding a new field
    $form_field_id += 1;

    // query to add a new form field in the table
    $sql = "INSERT INTO form_field_table
            (
                form_field_tag1,
                form_field_title,
                form_field_type,
                form_field_associated_to,
                form_field_values,
                form_field_validation,
                form_field_required, 
                created_at, 
                updated_at
            )
            VALUES
            (
                :field_tag1,
                :field_title,
                :field_type,
                :field_associated_to,
                :field_values,
                :field_validation,
                :field_required, 
                :created_at, 
                :updated_at
            )";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':field_tag1', $field_tag1, PDO::PARAM_STR);
    $query->bindParam(':field_title', $field_title, PDO::PARAM_STR);
    $query->bindParam(':field_type', $field_type, PDO::PARAM_STR);
    $query->bindParam(':field_associated_to', $field_associated_to, PDO::PARAM_STR);
    $query->bindParam(':field_values', $field_values, PDO::PARAM_STR);
    $query->bindParam(':field_validation', $field_validation, PDO::PARAM_STR);
    $query->bindParam(':field_required', $field_required, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);

    // success
    if($query->execute()){
        $form = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Form Field created successfully",
            "form field" => [
                "form_field_id" => $form_field_id
            ]
        ];
    }
    // failure
    else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Form Field creation failed"
        ];
    }

}
