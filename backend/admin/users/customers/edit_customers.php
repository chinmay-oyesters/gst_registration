<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    //fetch details from query parameter
    $user_id = $_POST['user_id'];
    $entity_fullname = $_POST['entity_name'];
    $entity_pan = $_POST['entity_pan'];
    $entity_phonenumber = $_POST['entity_phonenumber'];
    $entity_email = $_POST['entity_email'];
    $user_fullname = $_POST['person_name'];
    $user_email = $_POST['person_email'];
    $user_phonenumber = $_POST['person_phonenumber'];

    //fetch details from market
    $sql = "UPDATE user_table SET 
        entity_fullname = :entity_fullname,
        entity_pan = :entity_pan,
        entity_phonenumber=:entity_phonenumber,
        entity_email = :entity_email,
        user_fullname = :user_fullname,
        user_email = :user_email,
        user_phonenumber = :user_phonenumber
        WHERE user_id=:user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':entity_fullname', $entity_fullname, PDO::PARAM_STR);
    $query->bindParam(':entity_pan', $entity_pan, PDO::PARAM_STR);
    $query->bindParam(':entity_phonenumber', $entity_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':entity_email', $entity_email, PDO::PARAM_STR);
    $query->bindParam(':user_fullname', $user_fullname, PDO::PARAM_STR);
    $query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $query->bindParam(':user_phonenumber', $user_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    if($query->execute()){
        $status = 200;
        $response = [
            "msg" => "User data got updated."
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "Data can't be updated now."
        ];
    }
    

}