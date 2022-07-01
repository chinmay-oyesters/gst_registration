<?php

// import db connection
require("./dbcon.php");

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$user_password = md5($_POST['user_password']);
$entity_fullname = $_POST['entity_fullname'];
$entity_pan = $_POST['entity_pan'];
$entity_phonenumber = $_POST['entity_phonenumber'];
$entity_email = $_POST['entity_email'];
$user_fullname = $_POST['user_fullname'];
$user_email = $_POST['user_email'];
$user_phonenumber = $_POST['user_phonenumber'];
$datetime = date("Y-m-d H:i:s");


//Finding is the particular user already signed up
$sql = "SELECT * FROM user_table WHERE user_email=:user_email";
$query = $con -> prepare($sql);
$query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
$query->execute();

if($query->rowCount() === 0){

    $sql = "INSERT INTO 
    user_table (user_password, entity_fullname, entity_pan, entity_phonenumber, entity_email, user_fullname, user_email, user_phonenumber, created_at, updated_at) VALUES 
    (:user_password, :entity_fullname, :entity_pan, :entity_phonenumber, :entity_email, :user_fullname, :user_email, :user_phonenumber, :created_at, :updated_at)";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_password', $user_password, PDO::PARAM_STR);
    $query->bindParam(':entity_fullname', $entity_fullname, PDO::PARAM_STR);
    $query->bindParam(':entity_pan', $entity_pan, PDO::PARAM_STR);
    $query->bindParam(':entity_phonenumber', $entity_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':entity_email', $entity_email, PDO::PARAM_STR);
    $query->bindParam(':user_fullname', $user_fullname, PDO::PARAM_STR);
    $query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
    $query->bindParam(':user_phonenumber', $user_phonenumber, PDO::PARAM_STR);
    $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
    $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
    

    if($query->execute()){
        $user = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "User created successfully"
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
        "msg" => "Bad Request - Email Id already exists"
    ];

}