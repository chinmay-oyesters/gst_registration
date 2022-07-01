<?php

// import db connection
require("dbcon.php");
// require('middleware.php');

// getting token from cookie
// $token = $_COOKIE["user_jwt"];

// checking is the user authorized 
// if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $fullname = $_POST['fullname'];
    $phonenumber = $_POST['phonenumber'];
    $role_id = $_POST['role_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    //fetch details from market
    $sql = "INSERT INTO admin_user_table (admin_username, admin_fullname, admin_phonenumber, ) VALUES (:form_id, :form_name, :form_fields)";


// }