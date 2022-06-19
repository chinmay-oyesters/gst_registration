<?php

// import db connection
require("./dbcon.php");

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$admin_username = $_POST['admin_username'];
$admin_fullname = $_POST['admin_fullname'];
$admin_password = md5($_POST['admin_password']);
$admin_phonenumber = $_POST['admin_phonenumber'];
$admin_email = $_POST['admin_email'];

//Finding is the particular user already signed up
$sql = "SELECT * FROM admin_user_table WHERE admin_user_table.admin_username=:admin_username";
$query = $con -> prepare($sql);
$query->bindParam(':admin_username', $admin_username, PDO::PARAM_STR);
$query->execute();

if($query->rowCount() === 0){

    $sql = "INSERT INTO 
    admin_user_table (admin_username, admin_password, admin_fullname, admin_phonenumber, admin_email) VALUES 
    (:admin_username, :admin_password, :admin_fullname, :admin_phonenumber, :admin_email)";
    $query = $con -> prepare($sql);
    $query->bindParam(':admin_username', $admin_username, PDO::PARAM_STR);
    $query->bindParam(':admin_password', $admin_password, PDO::PARAM_STR);
    $query->bindParam(':admin_fullname', $admin_fullname, PDO::PARAM_STR);
    $query->bindParam(':admin_phonenumber', $admin_phonenumber, PDO::PARAM_STR);
    $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
    

    if($query->execute()){
        $admin = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "admin created successfully",
            "admin" => [
                "admin_name" => $admin_fullname,
                "admin_email" => $admin_email,
                "admin_phonenumber" => $admin_phonenumber
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