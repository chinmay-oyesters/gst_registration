<?php

// import db connection
require("dbcon.php");
require('middleware.php');

use PHPMailer\PHPMailer\PHPMailer;
require_once './PHPMailer/vendor/autoload.php';
require "./PHPMailer/vendor/autoload.php";
require_once "./PHPMailer/PHPMailer/PHPMailer.php";
require_once "./PHPMailer/PHPMailer/SMTP.php";

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $send_to = $_POST['send_to'];
    $mail_body = $_POST['mail_body'];
    $mail_subject = $_POST['mail_subject'];
    $form_id = $_POST['form_id'];
    $datetime = date("Y-m-d H:i:s");

    $form_status = "Correct Discrepancy";
    
    $sql = "SELECT user_id FROM user_table WHERE user_email=:user_email";
    $query = $con -> prepare($sql);
    $query->bindParam(':user_email', $send_to, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() === 0){
        $status = 203;
        $response = [
            "msg" => "Email not registered with us"
        ];
    }else{
    
        $sql = "SELECT sender_name, send_from, hostname, password, port FROM integrations_table";
        $query = $con -> prepare($sql);
    
        if($query->execute()){
            $integration_details = $query->fetchAll(PDO::FETCH_ASSOC)[0];
            $body = $mail_body;
        
            $sendfrom = $integration_details['send_from'];
            $sendername = $integration_details['sender_name'];
            $sendto = $send_to;
            $subject = $mail_subject;
        
            //Sending  
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = $integration_details['hostname'];
            $mail->SMTPAuth = true;
            $mail->Username = $integration_details['send_from'];
            $mail->Password = $integration_details['password'];
            $mail->Port = $integration_details['port'];
            $mail->SMTPSecure = 'ssl';
        
            $mail->isHTML(true);
            $mail->setFrom("$sendfrom", "$sendername");
            $mail->addAddress($sendto);
            $mail->Body="$body";
            $mail->Subject="$subject";
            if(!$mail->Send()) {
                $status = 203;
                $response = [
                    "msg" => "Mail not sent"
                ];				
                
            } else {
                $sql = "UPDATE user_subscription_details SET 
                form_status = :form_status,
                updated_at = :updated_at
                WHERE form_id = :form_id";
                $query = $con->prepare($sql);
                $query->bindParam(':form_status', $form_status, PDO::PARAM_STR);
                $query->bindParam(':form_id', $form_id, PDO::PARAM_STR);
                $query->bindParam(':updated_at', $datetime, PDO::PARAM_STR);
                if($query->execute()){
                    $status = 200;
                    $response = [
                        "msg" => "Mail sent successfully"
                    ];			
                }else{
                    $status = 203;
                    $response = [
                        "msg" => "Internal Server Error: Form status not changed"
                    ];
                }
            }
        }else{
            $status = 203;
            $response = [
                "msg" => "Internal Server Error: SMTP details not fetched"
            ];            
        }
    
    } 
}