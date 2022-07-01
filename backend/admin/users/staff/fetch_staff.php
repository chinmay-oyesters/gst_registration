<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["user_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    //fetch details from market
    $sql = "SELECT
        admin_fullname,
        admin_phonenumber,
        admin_email
        FROM admin_user_table ORDER BY admin_user_id DESC";
    $query = $con -> prepare($sql);

    if($query->execute()){
        
        $staffs = $query->fetchAll(PDO::FETCH_OBJ);

        $staff_array = array();

        foreach ($staffs as $staff) {
            array_push($staff_array,
                [
                    $staff->admin_fullname,
                    $staff->admin_phonenumber,
                    $staff->admin_email
                ]
            );
        }
        
        $status = 200;
        $response = [
            "msg" => "Staff Fetched",
            "staff" => $staff_array
        ];
        
    }else{
        
        $status = 203;
        $response = [
            "msg" => "Staff can't be fetched"
        ];
        
    }
    

}