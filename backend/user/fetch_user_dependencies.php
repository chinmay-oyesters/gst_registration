<?php

require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){
    // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    // retrieve required variables
    $fetch_field = $_POST['fetch_field'];

    // looking for the user in database
    $sql = "SELECT form_fields AS form_fields FROM form_table WHERE form_id = :form_id";
    $query = $con -> prepare($sql);

    // binding the parameters to the sql query
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->execute();

    $fields = array();
    $ids_not_found = array();

    foreach ($fetch_field as $fetch_field_id) {
        // query to retrieve form field in the table
        $sql = "SELECT
            form_field_id,
            form_field_title,
            form_field_type,
            form_field_required,
            form_field_associated_to,
            form_field_values,
            form_field_validation
            FROM form_field_table WHERE form_field_id = :field_id";
        $query = $con -> prepare($sql);

        // binding the parameters to the sql query
        $query->bindParam(':field_id', $fetch_field_id, PDO::PARAM_STR);
        $query->execute();
        
        // failure
        // when field is not found, append it into not_found array
        if($query->rowCount() === 0){
            array_push($ids_not_found, $fetch_field_id);
        }
        // success
        // when field is found, append it into fields found array
        else{
            $field = $query->fetchAll(PDO::FETCH_OBJ)[0];

            // convert field_required to a boolean value
            if( $field->form_field_required == 1 ){
                $temp_form_field_required = true;
            } else{
                $temp_form_field_required = false;
            }

            // check if field_values exist
            if( $field->form_field_values != NULL){
                $temp_form_field_values = json_decode(str_replace("'", "\"", $field->form_field_values));
            } else {
                $temp_form_field_values = NULL;
            }

            array_push($fields, [
                "field_id" => $field->form_field_id,
                "field_title" => $field->form_field_title,
                "field_type" => $field->form_field_type,
                "field_required" => $temp_form_field_required,
                "field_associated_to" => $field->form_field_associated_to,
                "field_values" => $temp_form_field_values,
                "field_validation" => $field->form_field_validation
            ]);
        }
        
    }

    // sending response
    $status = 200;
    $response = [
        "msg" => "Fields Fetched Successfully",
        "fields" => $fields,
        "field_not_found" => $ids_not_found
    ];
}

