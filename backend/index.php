<?php 

// import router
require("router.php");

// declare router variable
$request = $_SERVER['REQUEST_URI'];
$router = new Router($request);

require("sequelize.php");

// import routes
require("routes.php");