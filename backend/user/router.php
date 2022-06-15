<?php

//This is the Router File, Check if anything that can be added
// Router Class for making endpoints
class Router{
    private $request;
    private $root_url = "backend/"; // Root URL to trim from requests

    public function __construct($request){
        $this->request = str_replace( substr($request, 0, strpos($request, $this->root_url) ) , "", $request);
        $this->request = preg_replace('/'.$this->root_url, "", $this->request, 1);
        $this->request = trim( $this->request, "/" );
    }

    public function endpoint($route, $file, $methods, $authorization_required=FALSE, $params_to_check=[]){
        // Trim trailing slash and map extended endpoint arguments
        $uri = trim( $this->request, "/" );
        $uri = explode("/", $uri);

        $this_route = trim($route, "/");
        
        if(
            $uri[0] == $this_route
            OR
            (strpos($uri[0], $this_route)===0 AND substr($uri[0], strlen($this_route))[0]==="?")
        ){

            $get_url_params_array = explode("?", $_SERVER['REQUEST_URI']);
            $get_url_params = array();
            if(isset($get_url_params_array[1])){
                $get_url_params_array = explode("&", $get_url_params_array[1]);
                foreach ($get_url_params_array as $key => $value) {
                    $value = explode("=", $value);
                    $get_url_params[$value[0]] = urldecode($value[1]);
                }
            }

            // Check Authorization
            // if($authorization_required == TRUE){
            //     require_once("authenticate.php");
            // }

            // Assign arguments
            array_shift($uri);
            $args = $uri;

            //Check Request Method and render respective file
            if (in_array($_SERVER['REQUEST_METHOD'], $methods)){

                // Check if parameters are provided in the request or not
                $_POST = json_decode(file_get_contents("php://input"), true);
                foreach ($params_to_check as $param_to_check) {
                    if(!isset($_POST[$param_to_check])){
                        http_response_code(203);
                        echo json_encode(["msg" => "Bad Request -'$param_to_check' argument not provided"]);
                        exit();
                    }
                }

                $file = str_replace(".php", "", $file);
                $TIMEZONE_SET = TRUE;
                require_once("$file.php");

                if(!isset($status)){
                    $status = 200;
                }
                
                if(!isset($response)){
                    $status = 203;
                    $response = [
                        "msg" => "Bad Request - No response returned"
                    ];
                }

                $response["status"] = $status;

                header('Content-Type: application/json');
                http_response_code($status);
                echo json_encode($response);
                
            }else{

                http_response_code(405);
                echo json_encode(["msg" => "Method Not Allowed"]);

            }
        }
    }
}