<?php

// import db connection
require("./dbcon.php");

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$username = $_POST['username'];
$password = md5($_POST['password']);
$entity_name = $_POST['entity_name'];
$entity_pan = $_POST['entity_pan'];
$entity_phonenumber = $_POST['entity_phonenumber'];
$entity_email = $_POST['entity_email'];
$person_name = $_POST['person_name'];
$person_email = $_POST['person_email'];
$person_phonenumber = $_POST['person_phonenumber'];



//Finding is the particular user already signed up
$sql = "SELECT * FROM user_table WHERE user_table.username=:username";
$query = $con -> prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();

if($query->rowCount() === 0){

    $sql = "INSERT INTO 
    user_table (username, password, entity_name, entity_pan, entity_phonenumber, entity_email, person_name, person_email, person_phonenumber) VALUES 
    (:username, :password, :entity_name, :entity_pan, :entity_phonenumber, :entity_email, :person_name, :person_email, :person_phonenumber)";
    $query = $con -> prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':entity_name', $entity_name, PDO::PARAM_STR);
    $query->bindParam(':entity_pan', $entity_pan, PDO::PARAM_STR);
    $query->bindParam(':entity_phonenumber', $entity_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':entity_email', $entity_email, PDO::PARAM_STR);
    $query->bindParam(':person_name', $person_name, PDO::PARAM_STR);
    $query->bindParam(':person_email', $person_email, PDO::PARAM_STR);
    $query->bindParam(':person_phonenumber', $person_phonenumber, PDO::PARAM_STR);
    

    if($query->execute()){
        $user = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "User created successfully",
            "user" => [
                "person_name" => $person_name,
                "person_email" => $person_email,
                "person_phonenumber" => $person_phonenumber
            ]
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "Internal Server Error - User creation failed"
        ];
    }

}else{

    $status = 203;
    $response = [
        "msg" => "Bad Request - Username already exists"
    ];

}