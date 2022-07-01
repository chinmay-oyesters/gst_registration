<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$user_id = $_POST['user_id'];

// looking for the user in database
$sql = "SELECT * FROM user_subscription_details WHERE user_id = :user_id";
$query = $con -> prepare($sql);

// binding the parameters to the sql query
$query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
$query->execute();

$forms = array();
$forms_found = "(";

foreach ($query->fetchAll(PDO::FETCH_OBJ) as $form) {
    $forms_found .= $form->form_id . ",";

    array_push($forms,
        [
            "form_name" => $form->purchase_form_name,
            "form_status" => $form->form_status,
            "progress_percentage" => $form->form_progress
        ]
    );
    
}

$forms_found = substr($forms_found, 0, strlen($forms_found) - 1) . ")";
$sql = "SELECT form_name FROM forms_table WHERE form_id not in $forms_found";

$query = $con -> prepare($sql);
$query->execute();

foreach ($query->fetchAll(PDO::FETCH_OBJ) as $form) {
    array_push($forms,
        [
            "form_name" => $form->form_name,
            "form_status" => "Pay to Avail the Service",
            "progress_percentage" => NULL
        ]
    );
    
}

// sending response
$status = 200;
$response = [
    "msg" => "Fields Fetched Successfully",
    "forms" => $forms
];