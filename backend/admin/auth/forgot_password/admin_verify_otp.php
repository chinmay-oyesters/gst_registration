<?php

require("dbcon.php");


$_POST = json_decode(file_get_contents("php://input"), true);

$admin_email = $_POST['admin_email'];
$otp = $_POST['otp'];
$new_password = md5($_POST['new_password']);
$datetime = date("Y-m-d H:i:s");

$sql = "SELECT * FROM admin_user_table WHERE admin_email=:admin_email";
$query = $con -> prepare($sql);
$query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
if($query->execute()){
    $sql = "SELECT otp_created_at FROM admin_user_table WHERE admin_email=:admin_email AND otp = :otp";
    $query = $con -> prepare($sql);
    $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
    $query->bindParam(':otp', $otp, PDO::PARAM_STR);
    $query->execute();
    if($query->rowCount()  === 0){
        $status = 203;
        $response = [
            "msg" => "OTP not valid"
        ];
    }else{

        $otp_created_at = $query->fetchAll(PDO::FETCH_ASSOC)[0]['otp_created_at'];
        $expiry_time= date('Y:m:d h:i:s', strtotime("+15 minutes", strtotime($otp_created_at)));

        if($datetime <= $expiry_time){
            $otp = NULL;
            $sql = "UPDATE admin_user_table SET 
            otp = :otp,
            admin_password = :admin_password,
            updated_at = :updated_at
            WHERE admin_email = :admin_email";  
            $query = $con->prepare($sql);
            $query->bindParam(':otp', $otp, PDO::PARAM_STR);
            $query->bindParam(':admin_password', $new_password, PDO::PARAM_STR);
            $query->bindParam(':updated_at', $datetime, PDO::PARAM_STR);
            $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
            if($query->execute()){
                $status = 200;
                $response = [
                    "msg" => "Password saved successfully"
                ];
            }else{
                $status = 203;
                $response = [
                    "msg" => "Internal Server Error"
                ];
            }
        }else{
            $otp = NULL;
            $sql = "UPDATE admin_user_table SET 
            otp = :otp,
            updated_at = :updated_at
            WHERE admin_email = :admin_email";  
            $query = $con->prepare($sql);
            $query->bindParam(':otp', $otp, PDO::PARAM_STR);
            $query->bindParam(':updated_at', $datetime, PDO::PARAM_STR);
            $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
            if($query->execute()){
                $status = 203;
                $response = [
                    "msg" => "OTP not valid"
                ];
            }
        }    
    }
}else{
    $status = 203;
    $response = [
        "msg" => "Email not found"
    ];
}


