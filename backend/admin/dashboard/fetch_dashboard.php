<?php

// import db connection
require("dbcon.php");
require('middleware.php');

// getting token from cookie
$token = $_COOKIE["admin_jwt"];

// checking is the user authorized 
if(auth($token)){

    $dashboard = [];
    
    //todays_biddings
    $sql = "SELECT form_id, form_name  FROM form_table";
    $query = $con -> prepare($sql);

    if($query->execute()){

        $forms = $query->fetchAll(PDO::FETCH_ASSOC);
        // print_r($forms);
        foreach($forms as $form){
            $form_info = (object)[];
            $form_name = $form['form_name'];
            $users_converted = 0;
            $users_registered = 0;
            $application_in_progress_user = 0;
            $application_in_progress_admin = 0;
            $reg_completed = 0;
            $docs_uploaded = 0;

            //calulating registered users
            $sql = "SELECT COUNT(user_id) as users_registered FROM user_table 
            WHERE register_for = :form_id";
            $query = $con -> prepare($sql);      
            $query->bindParam(':form_id', $form['form_id'], PDO::PARAM_STR);

            if($query->execute()){
                $users_registered = $query->fetchAll(PDO::FETCH_ASSOC)[0]['users_registered'];
                // print_r($users_registered);
            }else{
                $status = 203;
                $response = [
                    "msg" => "Users Registered could be known"
                ];
            }


            //calculating users converted
            $sql = "SELECT user_id FROM user_table";
            $query = $con -> prepare($sql);      

            if($query->execute()){
                $user_ids = $query->fetchAll(PDO::FETCH_ASSOC);
                // print_r($user_ids);

                foreach($user_ids as $user_id){
                    $sql = "SELECT COUNT(subscription_id) as users_converted FROM user_subscription_details 
                    WHERE form_id = :form_id
                    AND user_id = :user_id";
                    $query = $con -> prepare($sql);      
                    $query->bindParam(':form_id', $form['form_id'], PDO::PARAM_STR);
                    $query->bindParam(':user_id', $user_id['user_id'], PDO::PARAM_STR);      
                    
                    if($query->execute()){
                        $users_converted += $query->fetchAll(PDO::FETCH_ASSOC)[0]['users_converted'];
                        // print_r($users_converted); 
                    }else{
                        $status = 203;
                        $response = [
                            "msg" => "Users Converted could be known"
                        ];                        
                    }
                }
                // print_r($users_converted);
                // echo("\n");

                

            }else{
                $status = 203;
                $response = [
                    "msg" => "Users Registered could be known"
                ];
            }


            //calculating application in progress at user end
            $sql = "SELECT COUNT(subscription_id) as application_in_progress_user FROM user_subscription_details
            WHERE form_id = :form_id
            AND form_status = 'In Progress'
            OR form_status = 'Correct Discrepancy'";
            $query = $con -> prepare($sql);      
            $query->bindParam(':form_id', $form['form_id'], PDO::PARAM_STR);

            if($query->execute()){
                $application_in_progress_user = $query->fetchAll(PDO::FETCH_ASSOC)[0]['application_in_progress_user'];
                // print_r($application_in_progress_user);
            }else{
                $status = 203;
                $response = [
                    "msg" => "Application in progress at user end could not be known"
                ];
            }


            ////calculating application in progress at admin end
            $sql = "SELECT COUNT(subscription_id) as application_in_progress_admin FROM user_subscription_details
            WHERE form_id = :form_id
            AND form_status = 'Completed'
            OR form_status = 'In Progress at concern department'";
            $query = $con -> prepare($sql);      
            $query->bindParam(':form_id', $form['form_id'], PDO::PARAM_STR);

            if($query->execute()){
                $application_in_progress_admin = $query->fetchAll(PDO::FETCH_ASSOC)[0]['application_in_progress_admin'];
                // print_r($application_in_progress_admin);
            }else{
                $status = 203;
                $response = [
                    "msg" => "Application in progress at admin end could not be known"
                ];
            }


            //users whose registration is completed
            $sql = "SELECT COUNT(subscription_id) as reg_completed FROM user_subscription_details
            WHERE form_id = :form_id
            AND form_status = 'Completed'";
            $query = $con -> prepare($sql);      
            $query->bindParam(':form_id', $form['form_id'], PDO::PARAM_STR);

            if($query->execute()){
                $reg_completed = $query->fetchAll(PDO::FETCH_ASSOC)[0]['reg_completed'];
                // print_r($reg_completed);
            }else{
                $status = 203;
                $response = [
                    "msg" => "Users whose registration is completed could not be known"
                ];
            }



            //users whose docs also uploaded
            $sql = "SELECT COUNT(subscription_id) as docs_uploaded FROM user_subscription_details
            WHERE form_id = :form_id
            AND form_status = 'In Progress'
            OR form_status = 'Correct Discrepancy'";
            $query = $con -> prepare($sql);      
            $query->bindParam(':form_id', $form['form_id'], PDO::PARAM_STR);

            if($query->execute()){
                $docs_uploaded = $query->fetchAll(PDO::FETCH_ASSOC)[0]['docs_uploaded'];
                // print_r($docs_uploaded);
            }else{
                $status = 203;
                $response = [
                    "msg" => "Users whose registration is completed could not be known"
                ];
            }

            $form_info->form_name = $form_name;
            $form_info->users_registered = $users_registered;
            $form_info->users_converted = $users_converted;
            $form_info->application_in_progress_user = $application_in_progress_user;
            $form_info->application_in_progress_admin = $application_in_progress_admin;
            $form_info->reg_completed = $reg_completed;
            $form_info->docs_uploaded = $docs_uploaded;

            array_push($dashboard, $form_info);

        }

        $status = 200;
        $response = [
            "msg" => "Dashboard data fetched successfully",
            "dashboard" => $dashboard
        ];

    }else{
        $status = 203;
        $response = [
            "msg" => "Form details can't be fetched"
        ];
    }

        
}
