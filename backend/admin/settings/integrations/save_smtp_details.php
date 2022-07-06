<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $_POST = json_decode(file_get_contents("php://input"), true);

    $sender_name = $_POST['sender_name'];
    $send_from = $_POST['send_from'];
    $hostname = $_POST['hostname'];
    $password = $_POST['password'];
    $port = $_POST['port'];
    $datetime = date("Y-m-d H:i:s");

    //fetch details from integrations to know whether to update or insert
    $sql = "SELECT integration_id FROM integrations_table";
    $query = $con -> prepare($sql);
    $query->execute();

    if($query->rowCount() === 0){
        $sql = "INSERT INTO integrations_table (sender_name, send_from, hostname, password, port, created_at, updated_at) 
        VALUES (:sender_name, :send_from, :hostname, :password, :port, :created_at, :updated_at)";
        $query = $con -> prepare($sql);
    
        // binding the parameters to the sql query in order to insert new data
        $query->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
        $query->bindParam(':send_from', $send_from, PDO::PARAM_STR);
        $query->bindParam(':hostname', $hostname, PDO::PARAM_STR);
        $query->bindParam(':password', $hostname, PDO::PARAM_STR);
        $query->bindParam(':port', $port, PDO::PARAM_STR);
        $query->bindparam(":created_at", $datetime, PDO::PARAM_STR);
        $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
    
        // success
        if($query->execute()){
            $form = $query->fetchAll(PDO::FETCH_OBJ);
            $status = 200;
            $response = [
                "msg" => "SMTP details added successfully"
            ];
        }
        // failure
        else{
            $status = 203;
            $response = [
                "msg" => "Internal Server Error - Details can't be saved"
            ];
        }
    
    }else{

        $integration_id = $query->fetchAll(PDO::FETCH_ASSOC)[0]['integration_id'];

        print_r($integration_id);

        $sql = "UPDATE integrations_table SET 
        sender_name = :sender_name, 
        send_from  = :send_from, 
        hostname  = :hostname, 
        password = :password,
        port = :port, 
        updated_at = :updated_at
        WHERE integration_id = :integration_id";
        $query = $con -> prepare($sql);
    
        // binding the parameters to the sql query in order to insert new data
        $query->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
        $query->bindParam(':send_from', $send_from, PDO::PARAM_STR);
        $query->bindParam(':hostname', $hostname, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':port', $port, PDO::PARAM_STR);
        $query->bindparam(":updated_at", $datetime, PDO::PARAM_STR);
        $query->bindParam(':integration_id', $integration_id, PDO::PARAM_STR);
    
        // success
        if($query->execute()){
            $form = $query->fetchAll(PDO::FETCH_OBJ);
            $status = 200;
            $response = [
                "msg" => "SMTP details edited successfully"
            ];
        }
        // failure
        else{
            $status = 203;
            $response = [
                "msg" => "Internal Server Error - Details can't be saved"
            ];
        }
    
    }

}