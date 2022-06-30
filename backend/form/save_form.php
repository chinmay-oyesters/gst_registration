<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);
$form_values = $_POST['form_values'];

// preparing the sql to insert multiple values in form response table
$sql = "INSERT INTO form_response_table (form_response_form_id, form_response_form_field_id, form_response_user_id, form_response_answer) VALUES ";

// looping in the form_values to get the response values
foreach ($form_values as $form_value) {
    // appending the response-value block into the sql query
    $sql .= "($form_value[form_id], $form_value[field_id], $form_value[user_id], '$form_value[response]'), ";
}
// removing the last ',' from the sql string
$sql = substr($sql, 0, strlen($sql)-2);
$query = $con -> prepare($sql);

// success
if($query->execute()){
    $form = $query->fetchAll(PDO::FETCH_OBJ);
    $status = 200;
    $response = [
        "msg" => "Form saved successfully"
    ];
}
// failure
else{
    $status = 203;
    $response = [
        "msg" => "Internal Server Error - Saving Form failed"
    ];
}