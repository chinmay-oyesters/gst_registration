<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    try{
        $dashboard = [];
        
        //todays_biddings
        $sql = "SELECT COUNT(user_id) as total_users FROM user_table";
        $query = $con -> prepare($sql);

        if($query->execute()){
            $total_users = $query->fetchAll(PDO::FETCH_ASSOC)[0]['total_users'];
            array_push($dashboard, $total_users);
        }else{
            $status = 203;
            $response = [
                "msg" => "Today's users can't be fetched"
            ];
        }

        $status = 200;
        $response = [
            "msg" => $dashboard
        ];
    }catch(Exception $e){
        $status = 203;
        $response = [
            "msg" => "Dashboard data can't be fetched.",
            "error" => $e->getMessage()
        ];
    }
}
