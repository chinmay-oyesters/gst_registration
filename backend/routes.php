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

// Save responses route to save response of user
$router->endpoint('save_response', 'user/save_response', ['POST'], FALSE, []);

// fetching user forms
$router->endpoint('fetch_user_forms', 'user/fetch_user_forms', ['POST'], FALSE, []);

// fetch user dependencies
$router->endpoint('fetch_user_dependencies', 'user/fetch_user_dependencies', ['POST'], FALSE, ['fetch_field']);

//payments
$router->endpoint('user_fetch_payments', 'user/user_payments', ['GET'], FALSE, []);


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

// add new staff
$router->endpoint('fetch_staff', 'admin/users/staff/fetch_staff', ['GET'], FALSE, []);
$router->endpoint('add_staff', 'admin/users/staff/add_staff', ['POST'], FALSE, ['admin_fullname', 'admin_phonenumber', 'role_id', 'admin_email', 'admin_password']);
$router->endpoint('edit_staff', 'admin/users/staff/edit_staff', ['POST'], FALSE, ['admin_user_id', 'admin_fullname', 'admin_phonenumber', 'role_id', 'admin_email']);

// role
$router->endpoint('fetch_roles', 'admin/roles/fetch_roles', ['GET'], FALSE, []);
$router->endpoint('add_role', 'admin/roles/add_role', ['POST'], FALSE, ['role_name', 'role_description', 'role_permissions']);
$router->endpoint('edit_role', 'admin/roles/edit_role', ['POST'], FALSE, ['role_name', 'role_description', 'role_permissions']);

// all endpoints specified for form related operations

// creation of form
$router->endpoint('add_form', 'form/add_form', ['POST'], FALSE, ['form_name', 'form_fields']);
// creation of form fields
$router->endpoint('add_field', 'form/add_field', ['POST'], FALSE, []);
// fetch form
$router->endpoint('fetch_form', 'form/fetch_form', ['POST'], FALSE, ['form_id']);
// save form
$router->endpoint('save_form', 'form/save_form', ['POST'], FALSE, ['form_values']);
// fetch dependencies
$router->endpoint('fetch_dependencies', 'form/fetch_dependencies', ['POST'], FALSE, ['fetch_field']);

//  Admin Form related routes

$router->endpoint('fetch_admin_forms', 'admin/admin_forms/fetch_admin_forms', ['GET'], FALSE, []);
$router->endpoint('fetch_admin_form_fields', 'admin/admin_forms/fetch_admin_form_fields', ['GET'], FALSE, []);
$router->endpoint('delete_admin_form', 'admin/admin_forms/delete_admin_form', ['POST'], FALSE, ['form_id']);
$router->endpoint('fetch_users', 'admin/admin_forms/fetch_users', ['POST'], FALSE, ['form_id']);
$router->endpoint('fetch_fields', 'admin/admin_forms/fetch_fields', ['GET'], FALSE, []);
$router->endpoint('fetch_response', 'admin/admin_forms/fetch_response', ['POST'], FALSE, ['form_id', 'user_id']);
$router->endpoint('edit_admin_form', 'admin/admin_forms/edit_admin_form', ['POST'], FALSE, ['form_id']);
$router->endpoint('approve_form', 'admin/admin_forms/approve_form', ['POST'], FALSE, ['form_id', 'user_id']);