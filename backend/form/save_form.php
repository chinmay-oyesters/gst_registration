<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
// $user_id = $_POST['user_id'];
// $field_id = $_POST['field_id'];
// $form_id = $_POST['form_id'];
// $response = $_POST['response'];

$form_values = $_POST['form_values'];

$form_saved = TRUE;

$sql = "INSERT INTO form_response_table (form_response_form_id, form_response_form_field_id, form_response_user_id, form_response_answer) VALUES ";

foreach ($form_values as $form_value) {
    $sql .= "($form_value[form_id], $form_value[field_id], $form_value[user_id], '$form_value[response]'), ";
}

$sql = substr($sql, 0, strlen($sql)-2);

$query = $con -> prepare($sql);

if($query->execute()){
    $form = $query->fetchAll(PDO::FETCH_OBJ);
    $status = 200;
    $response = [
        "msg" => "Form saved successfully"
    ];
}else{
    $status = 203;
    $response = [
        "msg" => "Internal Server Error - Saving Form failed"
    ];
}