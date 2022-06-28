<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$form_name = $_POST['form_name'];
$form_fields = $_POST['form_fields'];

// looking for the max form id in database
$sql = "SELECT max(form_id) AS form_id FROM form_table";
$query = $con -> prepare($sql);
$query->execute();

$form_id = $query->fetchAll(PDO::FETCH_OBJ)[0]->form_id;

if($form_id == NULL){
    $form_id = 0;
}
$form_id += 1;

$sql = "INSERT INTO form_table (form_id, form_name, form_fields) VALUES (:form_id, :form_name, :form_fields)";
$query = $con -> prepare($sql);

$query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
$query->bindParam(':form_name', $form_name, PDO::PARAM_STR);
$query->bindParam(':form_fields', $form_fields, PDO::PARAM_STR);

if($query->execute()){
    $form = $query->fetchAll(PDO::FETCH_OBJ);
    $status = 200;
    $response = [
        "msg" => "Form created successfully",
        "form" => "Test Response"
    ];
}else{
    $status = 203;
    $response = [
        "msg" => "Internal Server Error - Form creation failed"
    ];
}
