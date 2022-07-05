<?php

// import db connection
require("./dbcon.php");
require_once('../vendor/autoload.php');
use Firebase\JWT\JWT;
use Dotenv\Dotenv;

// Looing for .env at the root directory
$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

//Retrive env variable
$SECRET_KEY = $_ENV['SECRET_KEY'];

// retrieve request data
$_POST = json_decode(file_get_contents("php://input"), true);

// retrieve required variables
$admin_email = $_POST['admin_email'];
$admin_password = md5($_POST['admin_password']);

// looking for the user in database
$sql = "SELECT * FROM admin_user_table WHERE admin_user_table.admin_email=:admin_email";
$query = $con -> prepare($sql);
$query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
$query->execute();

if($query->rowCount() === 0){

    $status = 203;
    $response = [
        "msg" => "Bad Request - User does not exist"
    ];

}else{

    // fetching row of that user
    $admin = $query->fetchAll(PDO::FETCH_OBJ)[0];

    if($admin->admin_password === $admin_password){

        // if admin is authenticated then generate a token with JWT
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+30 days')->getTimestamp();             // Add 30 days
        $serverName = "http://localhost/gst_registration/backend/user_signin";     // Retrieved from filtered POST data

        $data = [
            'iat'  => $issuedAt->getTimestamp(),                        
            'iss'  => $serverName,                                      
            'nbf'  => $issuedAt->getTimestamp(),                        
            'exp'  => $expire,                                          
            'admin_user_id' =>  $admin->admin_user_id,                               
            'admin_email' => $admin->admin_email,                          
            'admin_phonenumber' => $admin->admin_phonenumber          
        ];

        $jwt =  JWT::encode(
            $data,
            $SECRET_KEY,
            'HS512'
        );
        // sending jwt token to frontend with cookies
        setcookie("admin_jwt", $jwt, time()+ (86400 * 30), "/","", 0); //86400*7 expiry time to 30 days

        $sql = "SELECT aut.admin_user_id, aut.admin_fullname, rt.role_name, rt.role_permissions, aut.admin_profileimage
        FROM admin_user_table as aut 
        LEFT JOIN roles_table as rt 
        ON rt.role_id = aut.role_id
        WHERE admin_email=:admin_email";
        $query = $con -> prepare($sql);
        $query->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);

        if($query->execute()){
            $admin_details = $query->fetchAll(PDO::FETCH_ASSOC)[0];
            $status = 200;
            $response = [
                "msg" => "User authenticated successfully",
                "admin_details" => [
                    "admin_user_id" => $admin_details['admin_user_id'],
                    "admin_fullname" => $admin_details['admin_fullname'],
                    "role_name" => $admin_details['role_name'],
                    "role_permissions" => json_decode($admin_details['role_permissions']),
                    "admin_profileimage" => $admin_details['admin_profileimage']
                ]
            ];
        }else{
            $status = 203;
            $response = [
                "msg" => "Admin profile can't be fetched"
            ];
        }

    }else{
        $status = 203;
        $response = [
            "msg" => "Unauthorized - Password does not match"
        ];
    }

}