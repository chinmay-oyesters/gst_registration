<?php

// assign endpoints to their respective file location and allowed methods

//all endpoints specified for user
// authorization routes
$router->endpoint('user_signin', 'user/auth/user_signin', ['POST'], FALSE, ['user_email', 'user_password']);
$router->endpoint('user_signup', 'user/auth/user_signup', ['POST'], FALSE, ['user_password', 'entity_fullname', 'entity_pan', 'entity_phonenumber', 'entity_email', 'user_fullname', 'user_email', 'user_phonenumber']);
$router->endpoint('user_logout', 'user/auth/user_logout', ['POST'], FALSE, []);
$router->endpoint('user_reset_password', 'user/auth/user_reset_password', ['POST'], FALSE, ['old_password', 'new_password']);

//forgot password routes
$router->endpoint('user_send_otp', 'user/auth/forgot_password/user_send_otp', ['POST'], FALSE, ['user_email']);
$router->endpoint('user_verify_otp', 'user/auth/forgot_password/user_verify_otp', ['POST'], FALSE, ['user_email', 'otp', 'new_password']);

//profile routes
$router->endpoint('user_save_profile', 'user/user_profile/user_save_profile', ['POST'], FALSE, []);
$router->endpoint('user_fetch_profile', 'user/user_profile/user_fetch_profile', ['GET'], FALSE, []);
$router->endpoint('user_image_upload', 'user/user_profile/user_image_upload', ['POST'], FALSE, []);

//home page
$router->endpoint('home', 'user/home', ['GET'], FALSE, []);

//payments
$router->endpoint('user_fetch_payments', 'user/user_paymentsuser_fetch_payments', ['GET'], FALSE, []);


//all endpoints specified for admin
// authorization routes
$router->endpoint('admin_signin', 'admin/auth/admin_signin', ['POST'], FALSE, ['admin_email', 'admin_password']);
$router->endpoint('admin_signup', 'admin/auth/admin_signup', ['POST'], FALSE, ['admin_password', 'admin_fullname', 'admin_phonenumber', 'admin_email']);
$router->endpoint('admin_logout', 'admin/auth/admin_logout', ['POST'], FALSE, []);
$router->endpoint('admin_reset_password', 'admin/auth/admin_reset_password', ['POST'], FALSE, ['old_password', 'new_password']);

//forgot password routes
$router->endpoint('admin_send_otp', 'admin/auth/forgot_password/admin_send_otp', ['POST'], FALSE, ['admin_email']);
$router->endpoint('admin_verify_otp', 'admin/auth/forgot_password/admin_verify_otp', ['POST'], FALSE, ['admin_email', 'otp', 'new_password']);

//profile routes
$router->endpoint('admin_save_profile', 'admin/admin_profile/admin_save_profile', ['POST'], FALSE, []);
$router->endpoint('admin_fetch_profile', 'admin/admin_profile/admin_fetch_profile', ['GET'], FALSE, []);
$router->endpoint('admin_image_upload', 'admin/admin_profile/admin_image_upload', ['POST'], FALSE, []);

//payments
$router->endpoint('admin_fetch_payments', 'admin/admin_payments/admin_fetch_payments', ['GET'], FALSE, []);

// users/customers
$router->endpoint('fetch_customers', 'admin/users/customers/fetch_customers', ['GET'], FALSE, []);
$router->endpoint('edit_customers', 'admin/users/customers/edit_customers', ['POST'], FALSE, []);
$router->endpoint('delete_customers', 'admin/users/customers/delete_customers', ['POST'], FALSE, []);

// all endpoints specified for form related operations
// creation of form
$router->endpoint('create_form', 'form/create_form', ['POST'], FALSE, ['form_name', 'form_fields']);
// creation of form fields
$router->endpoint('create_field', 'form/create_field', ['POST'], FALSE, []);
// fetch form
$router->endpoint('fetch_form', 'form/fetch_form', ['POST'], FALSE, ['form_id']);
// save form
$router->endpoint('save_form', 'form/save_form', ['POST'], FALSE, ['form_values']);
// fetch dependencies
$router->endpoint('fetch_dependencies', 'form/fetch_dependencies', ['POST'], FALSE, ['fetch_field']);