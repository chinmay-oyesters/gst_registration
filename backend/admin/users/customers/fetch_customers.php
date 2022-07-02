<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    //fetch details from market
    $sql = "SELECT
        entity_fullname,
        entity_pan,
        entity_phonenumber,
        entity_email,
        user_fullname,
        user_email,
        user_phonenumber
        FROM user_table ORDER BY user_id DESC";
    $query = $con -> prepare($sql);

    if($query->execute()){
        
        $customers = $query->fetchAll(PDO::FETCH_OBJ);

        $customers_array = array();

        foreach ($customers as $customer) {
            array_push($customers_array,
                [
                    "entity_name" => $customer->entity_fullname,
                    "entity_pan" => $customer->entity_pan,
                    "entity_mobilenumber" => $customer->entity_phonenumber,
                    "entity_email" => $customer->entity_email,
                    "user_fullname" => $customer->user_fullname,
                    "user_email" => $customer->user_email,
                    "user_mobilenumber" => $customer->user_phonenumber
                ]
            );
        }
        
        $status = 200;
        $response = [
            "msg" => "Customers Fetched",
            "customers" => $customers_array
        ];
        
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Customers can't be fetched"
        ];
        
    }
    

}