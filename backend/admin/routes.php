<?php

// assign endpoints to their respective file location and allowed methods

// authorization routes
$router->endpoint('login', './views/auth/login', ['POST'], FALSE, ['phone_number', 'password']);
$router->endpoint('signup', './views/auth/signup', ['POST'], FALSE, ['phone_number', 'password', 'email', 'full_name']);
$router->endpoint('logout', './views/auth/logout', ['POST'], FALSE, ['phone_number']);
$router->endpoint('reset_password', './views/auth/reset_password', ['POST'], FALSE, ['old_password', 'new_password', 'phone_number']);

//profile routes
$router->endpoint('profile_save', './views/profile/profile_save', ['POST'], FALSE, []);
$router->endpoint('profile_fetch', './views/profile/profile_fetch', ['GET'], FALSE, []);