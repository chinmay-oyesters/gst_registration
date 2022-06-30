<?php

require("dbcon.php");

use PHPMailer\PHPMailer\PHPMailer;
require_once './PHPMailer/vendor/autoload.php';
require "./PHPMailer/vendor/autoload.php";
require_once "./PHPMailer/PHPMailer/PHPMailer.php";
require_once "./PHPMailer/PHPMailer/SMTP.php";
require_once "./PHPMailer/PHPMailer/Exception.php";

$_POST = json_decode(file_get_contents("php://input"), true);

$user_email = $_POST['user_email'];
$datetime = date("Y-m-d H:i:s");

$sql = "SELECT user_id FROM user_table WHERE user_table.user_email=:user_email";
$query = $con -> prepare($sql);
$query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
$query->execute();

if($query->rowCount() === 0){
	$status = 203;
	$response = [
		"msg" => "You are not registered with us"
	];
}else{
	$otp = rand(100000, 999999);
	$body = "Dear costumer,

	Your One Time Password (OTP) for Forgot Password recovery on Betting Application is ". $otp . 
	". Please note, this OTP is valid only for 15 minutes. Please do not share this One Time Password with anyone.";

	$sendfrom = "krishnpal@oyesters.in";
	$sendername = "Krishnpal Parmar";
	$sendto = $user_email;
	$subject = "One Time Password (OTP) for Forgot Password recovery on Betting Application";

	//Sending  
	$mail = new PHPMailer();
	$mail->isSMTP();
	$mail->Host = "mail.oyesters.in";
	$mail->SMTPAuth = true;
	$mail->Username = "krishnpal@oyesters.in";
	$mail->Password = "Krishnpal@1234";
	$mail->Port = 465;
	$mail->SMTPSecure = 'ssl';

	$mail->isHTML(true);
	$mail->setFrom("$sendfrom", "$sendername");
	$mail->addAddress($sendto);
	$mail->Body="$body";
	$mail->Subject="$subject";
	if(!$mail->Send()) {
		$status = 203;
        $response = [
            "msg" => "OTP not sent to mail"
        ];				
		
	} else {
		$sql = "UPDATE user_table SET 
		otp = :otp,
		otp_created_at = :otp_created_at,
		updated_at = :updated_at
		WHERE user_email = :user_email";
		$query = $con->prepare($sql);
		$query->bindParam(':otp', $otp, PDO::PARAM_STR);
		$query->bindParam(':user_email', $user_email, PDO::PARAM_STR);
		$query->bindParam(':otp_created_at', $datetime, PDO::PARAM_STR);
		$query->bindParam(':updated_at', $datetime, PDO::PARAM_STR);
		if($query->execute()){
			$status = 200;
			$response = [
				"msg" => "OTP Successfully sent to :- "."$sendto"."<br>"
			];			
		}else{
			$status = 203;
			$response = [
				"msg" => "Internal Server Error"
			];
		}
	}

}



