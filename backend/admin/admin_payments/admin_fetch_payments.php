<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){
    
    $sql = "SELECT pt.payment_id, ut.user_id, ut.user_fullname, ut.entity_fullname, ut.user_email, ut.user_phonenumber, 
    pt.order_id, pt.razorpay_payment_id, pt.order_amount, ft.form_name FROM payments_table as pt
    LEFT JOIN user_table as ut
    ON ut.user_id = pt.user_id
    LEFT JOIN form_table as ft
    ON pt.form_id = ft.form_id
    ORDER BY pt.payment_id desc";
    $query = $con -> prepare($sql);

    $query->execute();

    if($query->execute()){
        $payments = $query->fetchAll(PDO::FETCH_OBJ);
        $status = 200;
        $response = [
            "msg" => "Payments fetched successfully",
            "payments" => $payments
        ];
    }else{
        $status = 203;
        $response = [
            "msg" => "Payments can't be fetched now"
        ];
    }
}