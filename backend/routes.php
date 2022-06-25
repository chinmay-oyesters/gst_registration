<?php

// assign endpoints to their respective file location and allowed methods

//all endpoints specified for user
// authorization routes
$router->endpoint('user_signin', 'user/auth/user_signin', ['POST'], FALSE, ['username', 'password']);
$router->endpoint('user_signup', 'user/auth/user_signup', ['POST'], FALSE, ['username', 'password', 'entity_name', 'entity_pan', 'entity_phonenumber', 'entity_email', 'person_name', 'person_email', 'person_phonenumber']);
$router->endpoint('user_logout', 'user/auth/user_logout', ['POST'], FALSE, []);
$router->endpoint('user_forgot_password', 'user/auth/user_forgot_password', ['POST'], FALSE, ['old_password', 'new_password', 'phone_number']);

//profile routes
$router->endpoint('user_save_profile', 'user/user_profile/user_save_profile', ['POST'], FALSE, []);
$router->endpoint('user_fetch_profile', 'user/user_profile/user_fetch_profile', ['GET'], FALSE, []);
$router->endpoint('user_image_upload', 'user/user_profile/user_image_upload', ['POST'], FALSE, []);

//payments
$router->endpoint('user_fetch_payments', 'user/user_payments/user_fetch_payments', ['GET'], FALSE, []);


//all endpoints specified for admin
// authorization routes
$router->endpoint('admin_signin', 'admin/auth/admin_signin', ['POST'], FALSE, ['admin_username', 'admin_password']);
$router->endpoint('admin_signup', 'admin/auth/admin_signup', ['POST'], FALSE, ['admin_username', 'admin_password', 'admin_fullname', 'admin_phonenumber', 'admin_email']);
$router->endpoint('admin_logout', 'admin/auth/admin_logout', ['POST'], FALSE, []);
$router->endpoint('admin_forgot_password', 'admin/auth/admin_forgot_password', ['POST'], FALSE, ['old_password', 'new_password', 'phone_number']);

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