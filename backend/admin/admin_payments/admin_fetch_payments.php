<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){
    
    $sql = "SELECT payment_amount FROM payments_table ";
    $query = $con -> prepare($sql);
    $query->execute();

    if($query->execute()){
        $user_payments = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => $user_payments
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "User payments can't be fetched now"
        ];
    }
}