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


function getFieldsArray($field, $fields, $field_ids_done, $con){
    
    $field_array = [
        "field_id" => $field->form_field_id,
        "field_title" => $field->form_field_title,
        "field_type" => $field->form_field_type,
        "field_required" => $field->form_field_required,
        "field_associated_to" => $field->form_field_associated_to
    ];

    if(strtolower($field->form_field_type) === "dropdown"){
        $field_array["field_values"] = json_decode(str_replace("'", "\"", $field->form_field_values));
    }
    
    $values = $field->form_field_values;
    while(1){
        $x_occurence = strpos($values, "x");
        if(!$x_occurence){
            break;
        }

        $values = substr($values, $x_occurence + 1);

        $required_field = substr(
            $values,
            0,
            strpos($values, "'")
        );

        if(in_array($required_field, $field_ids_done)){
            continue;
        }
        
        $sql2 = "SELECT
            form_field_id,
            form_field_title,
            form_field_type,
            form_field_required,
            form_field_values,
            form_field_associated_to
            FROM form_field_table WHERE form_field_id = $required_field";

        $query = $con -> prepare($sql2);
        $query->execute();

        $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

        foreach ($fields_result as $inner_field) {
            $get_fields_result = getFieldsArray(
                $inner_field,
                $fields,
                $field_ids_done,
                $con
            );
            $fields = $get_fields_result[0];
            $field_ids_done = $get_fields_result[1];
        }
    }

    array_push($fields, $field_array);

    $form_field_associated_to = json_decode($field_array['field_associated_to']);

    if($form_field_associated_to != NULL){
        foreach ($form_field_associated_to as $form_field_associated_to_id) {
            $sql = "SELECT
                form_field_id,
                form_field_title,
                form_field_type,
                form_field_required,
                form_field_values,
                form_field_associated_to
                FROM form_field_table WHERE form_field_id = $form_field_associated_to_id";
            
            $query = $con -> prepare($sql);
            $query->execute();

            $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

            foreach ($fields_result as $inner_field) {
                $get_fields_result = getFieldsArray(
                    $inner_field,
                    $fields,
                    $field_ids_done,
                    $con
                );
                $fields = $get_fields_result[0];
                $field_ids_done = $get_fields_result[1];
            }
        }
    }
    
    if(!in_array($field->form_field_id, $field_ids_done)){
        array_push($field_ids_done, $field->form_field_id);
    }

    return [
        0 => $fields,
        1 => $field_ids_done
    ];
}

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
        form_field_required,
        form_field_values,
        form_field_associated_to
        FROM form_field_table WHERE form_field_id IN $form_fields";

    $query = $con -> prepare($sql);
    $query->execute();

    $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

    $fields = array();
    $field_ids_done = array();
    $field_oc = array();
    
    foreach ($fields_result as $field) {
        $get_fields_result = getFieldsArray(
            $field,
            $fields,
            $field_ids_done,
            $con
        );

        $fields = $get_fields_result[0];
        $field_ids_done = $get_fields_result[1];
    }

    $status = 200;
    $response = [
        "msg" => "Fields Fetched Successfully",
        "fields" => $fields
    ];
}