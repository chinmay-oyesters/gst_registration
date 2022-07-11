<?php

// import db connection
require("./dbcon.php");

$sql = "SELECT form_id, form_name FROM form_table";
$query = $con -> prepare($sql);
$query->execute();

if($query->rowCount() === 0){

    $status = 203;
    $response = [
        "msg" => "Internal server error"
    ];

}else{

    $form_dropdown = $query->fetchAll(PDO::FETCH_OBJ);

    $status = 200;
    $response = [
        "msg" => "Form details fetched successfully",
        "form_dropdown" => $form_dropdown
    ];

}
