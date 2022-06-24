<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$field_tag1 = $_POST['field_tag1'];
$field_title = $_POST['field_title'];
$field_type = $_POST['field_type'];
$field_associated_to = $_POST['field_associated_to'];
$field_values = $_POST['field_values'];
$field_validation = $_POST['field_validation'];
$field_required = $_POST['field_required'];

// looking for the max form field id in database
$sql = "SELECT max(form_field_id) AS form_field_id FROM form_field_table";
$query = $con -> prepare($sql);
$query->execute();

$form_field_id = $query->fetchAll(PDO::FETCH_OBJ)[0]->form_field_id;

if($form_field_id == NULL){
    $form_field_id = 0;
}
$form_field_id += 1;

$sql = "INSERT INTO form_field_table
        (
            form_field_tag1,
            form_field_title,
            form_field_type,
            form_field_associated_to,
            form_field_values,
            form_field_validation,
            form_field_required
        )
        VALUES
        (
            :field_tag1,
            :field_title,
            :field_type,
            :field_associated_to,
            :field_values,
            :field_validation,
            :field_required
        )";
$query = $con -> prepare($sql);

$query->bindParam(':field_tag1', $field_tag1, PDO::PARAM_STR);
$query->bindParam(':field_title', $field_title, PDO::PARAM_STR);
$query->bindParam(':field_type', $field_type, PDO::PARAM_STR);
$query->bindParam(':field_associated_to', $field_associated_to, PDO::PARAM_STR);
$query->bindParam(':field_values', $field_values, PDO::PARAM_STR);
$query->bindParam(':field_validation', $field_validation, PDO::PARAM_STR);
$query->bindParam(':field_required', $field_required, PDO::PARAM_STR);

if($query->execute()){
    $form = $query->fetchAll(PDO::FETCH_OBJ);
    $status = 200;
    $response = [
        "msg" => "Form Field created successfully",
        "form field" => [
            "form_field_id" => $form_field_id
        ]
    ];
}else{
    $status = 203;
    $response = [
        "msg" => "Internal Server Error - Form Field creation failed"
    ];
}