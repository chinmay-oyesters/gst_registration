<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){


    //fetch details from roles table
    $sql = "SELECT razorpay_key, razorpay_secret, sender_name, send_from, hostname, password, port FROM integrations_table";
    $query = $con -> prepare($sql);

    if($query->execute()){
        
        $integration_details = $query->fetchAll(PDO::FETCH_OBJ)[0];
        
        $status = 200;
        $response = [
            "msg" => "Integrations Fetched",
            "integration_details" => $integration_details
        ];
        
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Integrations can't be fetched"
        ];
        
    }
    

}