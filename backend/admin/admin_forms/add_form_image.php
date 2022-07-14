<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    //reading values from query parameters
    $query_param = $_GET[0];

    $form_id = $query_param['form_id'];

    // Filetype is correct. Check size
    if($_FILES["choosefile"]["size"] < 2000000) {
        //extracting data from uploaded file
        $filename = $_FILES["choosefile"]["name"];
        $tempname = $_FILES["choosefile"]["tmp_name"];
        $image = base64_encode(file_get_contents($tempname));
    
    
        // query to insert the submitted data
        $sql = "UPDATE form_table SET form_image = :form_image WHERE form_id = :form_id";
        $query = $con -> prepare($sql);
        $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
        $query->bindParam(':form_image', $image, PDO::PARAM_LOB);
        if($query->execute()){

            // to find is there a entry available in subscription table for these particular form id
            $sql = "SELECT * FROM user_subscription_details WHERE form_id = :form_id";
            $query = $con -> prepare($sql);
            $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
            $query->execute();

            if($query->rowCount() === 0){
                $status = 200;
                $response = [
                    "msg" => "Image saved in forms table",
                    "image" => $image
                ];
            }else{
                // query to insert the submitted data
                $sql = "UPDATE user_subscription_details SET form_image = :form_image WHERE form_id = :form_id";
                $query = $con -> prepare($sql);
                $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
                $query->bindParam(':form_image', $image, PDO::PARAM_LOB);    
                if($query->execute()){
                    $status = 200;
                    $response = [
                        "msg" => "Image saved in forms and subscription table",
                        "image" => $image
                    ];
                }else{
                    $status = 203;
                    $response = [
                        "msg" => "Image can't be saved in subscription details"
                    ];
                }
            }
        }else{
            $status = 203;
            $response = [
                "msg" => "Image can't be saved to forms table"
            ];
        }
    }else{
        $status  = 203;
        $response = [
            "msg" => "Reduce file size to 2 MB."
        ];
    }
}