<?php

// getting token from cookie
$token = $_COOKIE["admin_jwt"];


setcookie("admin_jwt", $token, time()-3600);    

$status = 200;

$response = [
    "msg" => "Logged out successfully"
];


