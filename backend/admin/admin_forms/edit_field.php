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
    $field_id = $_POST['field_id'];
    $field_tag1 = $_POST['field_tag1'];
    $field_title = $_POST['field_title'];
    $field_type = $_POST['field_type'];
    $field_required = $_POST['field_required'];
    $datetime = date("Y-m-d H:i:s");

    $field_values = isset($_POST['field_values']) ? json_encode($_POST['field_values']) : NULL;
    $field_validation = isset($_POST['field_validation']) ? json_encode($_POST['field_validation']) : NULL;
    $field_associated_to = isset($_POST['field_associated_to']) ? json_encode($_POST['field_associated_to']) : NULL;

    // query to add a new form field in the table
    $sql = "UPDATE form_field_table SET
            form_field_tag1 = :field_tag1,
            form_field_title = :field_title,
            form_field_type = :field_type,
            form_field_associated_to = :field_associated_to,
            form_field_values = :field_values,
            form_field_validation = :field_validation,
            form_field_required = :field_required,
            updated_at = :updated_at
            WHERE form_field_id = :field_id";

    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':field_id', $field_id, PDO::PARAM_STR);
    $query->bindParam(':field_tag1', $field_tag1, PDO::PARAM_STR);
    $query->bindParam(':field_title', $field_title, PDO::PARAM_STR);
    $query->bindParam(':field_type', $field_type, PDO::PARAM_STR);
    $query->bindParam(':field_associated_to', $field_associated_to, PDO::PARAM_STR);
    $query->bindParam(':field_values', $field_values, PDO::PARAM_STR);
    $query->bindParam(':field_validation', $field_validation, PDO::PARAM_STR);
    $query->bindParam(':field_required', $field_required, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);

    // success
    if($query->execute()){
        $form = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Form Field edited successfully"
        ];
    }
    // failure
    else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - Form Field can't be edited"
        ];
    }

}
