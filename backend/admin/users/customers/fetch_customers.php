<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    //fetch details from market
    $sql = "SELECT username, entity_name, entity_pan, entity_phonenumber, entity_email, person_name, person_email, person_phonenumber FROM user_table ORDER BY user_id DESC";
    $query = $con -> prepare($sql);
    if($query->execute()){
        $customers = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => $customers
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "Customers can't be fetched"
        ];
    }
    

}