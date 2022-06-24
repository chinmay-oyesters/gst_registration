<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$form_id = $_POST['form_id'];

// looking for the user in database
$sql = "SELECT form_fields AS form_fields FROM form_table WHERE form_id = :form_id";
$query = $con -> prepare($sql);

$query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
$query->execute();


if($query->rowCount() === 0){
    $status = 203;
    $response = [
        "msg" => "Not Found - Form with id not found"
    ];
}else{

    $form_fields = $query->fetchAll(PDO::FETCH_OBJ)[0]->form_fields;

    $form_fields = str_replace("[", "(", $form_fields);
    $form_fields = str_replace("]", ")", $form_fields);

    $sql = "SELECT
        form_field_id,
        form_field_title,
        form_field_type,
        form_field_required
        FROM form_field_table WHERE form_field_id IN $form_fields";

    $query = $con -> prepare($sql);
    $query->execute();

    $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

    $fields = array();

    foreach ($fields_result as $field) {
        array_push($fields, [
            "field_id" => $field->form_field_id,
            "field_title" => $field->form_field_title,
            "field_type" => $field->form_field_type,
            "field_required" => $field->form_field_required
        ]);
    }

    $status = 200;
    $response = [
        "msg" => "Fields Fetched Successfully",
        "fields" => $fields
    ];
}