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
    $username = $_POST['username'];
    $entity_name = $_POST['entity_name'];
    $entity_pan = $_POST['entity_pan'];
    $entity_phonenumber = $_POST['entity_phonenumber'];
    $entity_email = $_POST['entity_email'];
    $person_name = $_POST['person_name'];
    $person_email = $_POST['person_email'];
    $person_phonenumber = $_POST['person_phonenumber'];

    //fetch details from market
    $sql = "UPDATE user_table SET 
        username = :username,
        entity_name = :entity_name,
        entity_pan = :entity_pan,
        entity_phonenumber=:entity_phonenumber,
        entity_email = :entity_email,
        person_name = :person_name,
        person_email = :person_email,
        person_phonenumber = :person_phonenumber
        WHERE user_id=:user_id";
    $query = $con -> prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':entity_name', $entity_name, PDO::PARAM_STR);
    $query->bindParam(':entity_pan', $entity_pan, PDO::PARAM_STR);
    $query->bindParam(':entity_phonenumber', $entity_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':entity_email', $entity_email, PDO::PARAM_STR);
    $query->bindParam(':person_name', $person_name, PDO::PARAM_STR);
    $query->bindParam(':person_email', $person_email, PDO::PARAM_STR);
    $query->bindParam(':person_phonenumber', $person_phonenumber, PDO::PARAM_STR);
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