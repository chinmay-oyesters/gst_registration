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

    $payload = JWT::decode($token, new Key($SECRET_KEY, 'HS512'));

    $dashboard_data = [];

    $sql = "SELECT form_id, form_name, form_image FROM form_table";
    $query = $con -> prepare($sql);
    $query->execute();

    if($query->rowCount() === 0){
        $status = 203;
        $response = [
            "msg" => "Cant't fetch forms"
        ]; 
    }else{

        $available_forms = $query->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($available_forms as $form){
            $form_id = $form['form_id'];
            $total_completed = 0;
            // print_r($form_id."\n");

            $sql = "SELECT COUNT(form_id) as total_forms, form_status FROM user_subscription_details WHERE form_id = :form_id AND user_id = :user_id";
            $query = $con -> prepare($sql);
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->bindParam(':user_id', $payload->user_id, PDO::PARAM_STR);
            $query->execute();

            if($query->rowCount() === 0){
                $status = 203;
                $response = [
                    "msg" => "Cant't fetch form count"
                ]; 
            }else{
                $form_counts = $query->fetchAll(PDO::FETCH_ASSOC);
                foreach($form_counts as $form_count){
                    if($form_count['form_status'] === "Applied"){
                        $total_completed++;
                    }      
                }

                array_push($dashboard_data,
                    [
                        "form_id" => $form['form_id'],
                        "form_name" => $form['form_name'],
                        "form_image" => $form['form_image'],
                        "total_forms" => $form_count['total_forms'],
                        "total_completed" => $total_completed
                    ]
                );
            }   

        }
        $status = 200;
        $response = [
            "msg" => "Dashboard data fetched successfully",
            "dashboard_data" => $dashboard_data
        ];

    }

}
