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
    $form_name = $_POST['form_name'];
    $form_amount = $_POST['form_amount'];
    $form_fields = json_encode($_POST['form_fields']);
    $datetime = date("Y-m-d H:i:s");

    // looking for the max form id in database
    // need to return id for new form that will be created
    // so manually replicating AUTO_INCREMENT functionality of mysql
    $sql = "SELECT max(form_id) AS form_id FROM form_table";
    $query = $con -> prepare($sql);
    $query->execute();

    $form_id = $query->fetchAll(PDO::FETCH_OBJ)[0]->form_id;

    // if the max form field is not defined, make it 0
    if($form_id == NULL){
        $form_id = 0;
    }
    // increment the id for adding a new form
    $form_id += 1;

    // query to add a new form field in the table
    $sql = "INSERT INTO form_table (form_id, form_name, form_amount,  form_fields, created_at, updated_at) VALUES 
    (:form_id, :form_name, :form_amount, :form_fields, :created_at, :updated_at)";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query in order to insert new data
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->bindParam(':form_name', $form_name, PDO::PARAM_STR);
    $query->bindParam(':form_fields', $form_fields, PDO::PARAM_STR);
    $query->bindParam(':form_amount', $form_amount, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);


    // success
    if($query->execute()){
        $form = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Form created successfully",
            "form" => [
                "form_id" => $form_id,
                "form_name" => $form_name,
                "form_amount" => $form_amount
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