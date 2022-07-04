<?php

// import db connection
require("dbcon.php");
require('middleware.php');
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;

// Looing for .env at the root directory
$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

        // retrieve request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    // retrieve required variables
    $form_id = $_POST['form_id'];

    $sql = "SELECT * FROM user_subscription_details WHERE user_id = :user_id AND form_id = :form_id";
    $query = $con -> prepare($sql);

    $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
    $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() === 0){
        $status = 203;
        $response = [
            "msg" => "You haven't subscribed to any forms",
            "is_redirect" => true
        ];
    }else{

        // looking for the user in database
        $sql = "SELECT form_fields AS form_fields FROM form_table WHERE form_id = :form_id";
        $query = $con -> prepare($sql);

        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->execute();


        // recursive function to return field values
        // recursive function takes care of handling multiple checks in 'field_values' and 'field_associated_to'
        function getFieldsArray($field, $fields, $field_ids_done, $con){
            
            // prepare the field_array for field passed to the function
            $field_array = [
                "field_id" => $field->form_field_id,
                "field_title" => $field->form_field_title,
                "field_type" => $field->form_field_type,
                "field_required" => $field->form_field_required,
                "field_associated_to" => $field->form_field_associated_to,
                "field_validation" => $field->form_field_validation
            ];

            // convert field_required to a boolean value
            if( $field_array['field_required'] == 1 ){
                $field_array['field_required'] = true;
            } else{
                $field_array['field_required'] = false;
            }

            // check if field_values exist
            if( $field->form_field_values != NULL){
                $field_array["field_values"] = json_decode(str_replace("'", "\"", $field->form_field_values));
            }
            
            // field_values check for values like '5x6'
            $values = $field->form_field_values;
            while(1){
                // check if any such value exists
                $x_occurence = strpos($values, "x");
                // break out of the loop if it doesn't exist
                if(!$x_occurence){
                    break;
                }

                // reducing the values string to check further occurence of value like '5x6'
                $values = substr($values, $x_occurence + 1);

                // get the required field, i.e. for a value like '5x6', the required field is '6'
                $required_field = substr(
                    $values,
                    0,
                    strpos($values, "'")
                );

                // continue if the field already exists, cz no repetition allowed for field in field_values
                if(in_array($required_field, $field_ids_done)){
                    continue;
                }
                
                // preparing sql query, i.e. to get the field_array for the field found in current field_values
                $sql2 = "SELECT
                    form_field_id,
                    form_field_title,
                    form_field_type,
                    form_field_required,
                    form_field_values,
                    form_field_associated_to,
                    form_field_validation
                    FROM form_field_table WHERE form_field_id = $required_field";

                $query = $con -> prepare($sql2);
                $query->execute();

                $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

                foreach ($fields_result as $inner_field) {
                    // make a recursive call to the function to get its field_array
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

            // append the field_array to fields_array for final response
            array_push($fields, $field_array);

            // check for fields in field_associated to the current field
            $form_field_associated_to = json_decode($field_array['field_associated_to']);

            if($form_field_associated_to != NULL){
                // loop through the fields in field_associated_to
                foreach ($form_field_associated_to as $form_field_associated_to_id) {
                    // preparing sql query, i.e. to get the field_array for the field found in current field_associated_to
                    $sql = "SELECT
                        form_field_id,
                        form_field_title,
                        form_field_type,
                        form_field_required,
                        form_field_values,
                        form_field_associated_to,
                        form_field_validation
                        FROM form_field_table WHERE form_field_id = $form_field_associated_to_id";
                    
                    $query = $con -> prepare($sql);
                    $query->execute();

                    $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

                    foreach ($fields_result as $inner_field) {
                        // make a recursive call to the function to get its field_array
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
            
            // if current field_id is found for the first time, so append it to the field_ids_done array
            // to keep a check of what all fields are done, for further checks during field_values
            if(!in_array($field->form_field_id, $field_ids_done)){
                array_push($field_ids_done, $field->form_field_id);
            }

            // return the net fields_array, and field_ids_done array
            return [
                0 => $fields,
                1 => $field_ids_done
            ];
        }

        // failure if form_id not found in the db
        if($query->rowCount() === 0){
            $status = 203;
            $response = [
                "msg" => "Not Found - Form with id not found"
            ];
        }
        // success if form_id found in the db
        else{

            $form_fields = $query->fetchAll(PDO::FETCH_OBJ)[0]->form_fields;

            // get the form_fields array
            $form_fields = str_replace("[", "(", $form_fields);
            $form_fields = str_replace("]", ")", $form_fields);

            // get the data for the form_fields array from the db
            $sql = "SELECT
                form_field_id,
                form_field_title,
                form_field_type,
                form_field_required,
                form_field_values,
                form_field_associated_to,
                form_field_validation
                FROM form_field_table WHERE form_field_id IN $form_fields";

            $query = $con -> prepare($sql);
            $query->execute();

            $fields_result = $query->fetchAll(PDO::FETCH_OBJ);

            // set the initial arrays
            $fields = array();
            $field_ids_done = array();
            $field_oc = array();
            
            // loop through the fields_result array to get the net fields_array
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

            // sending the response
            $status = 200;
            $response = [
                "msg" => "Fields Fetched Successfully",
                "fields" => $fields
            ];
        }

    }
}