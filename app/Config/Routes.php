<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');

// Auth Routes
$routes->post('auth/validate', 'Auth::authenticate');
$routes->get('auth/google-sign-in', 'Auth::google_signin');
$routes->get('auth/google/callback', 'Auth::google_callback');

$routes->get('force-logout-choice', 'Auth::forceLogout');
$routes->post('force-logout', 'Auth::forceLogout/logout-other-device');

// Forgot Password
$routes->get('forgot-password', 'Home::forgot_password');
$routes->post('forgot-password/send-link', 'Home::forgot_password/send-link');
$routes->get('reset-password', 'Home::forgot_password/reset-password');
$routes->post('forgot-password/change-password', 'Home::forgot_password/change-password');

// Signup
$routes->get('sign-up', 'Home::sign_up');
$routes->get('sign-up/account-detail', 'Home::sign_up/account-detail');
$routes->get('sign-up/subscription-plan', 'Home::sign_up/subscription-plan');
$routes->post('sign-up/create-account', 'Home::sign_up/create-account');
$routes->post('sign-up/submit-account-detail', 'Home::sign_up/submit-account-detail');

// Payment
$routes->get('braintree/getClientToken', 'BraintreeController::getClientToken');
$routes->post('braintree/subscribe', 'BraintreeController::subscribe');

// CSRF
$routes->get('get-csrf-token', 'Home::get_token');

/* ===============================
|  API ROUTES
================================*/
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {

    $routes->post('login', 'Auth::login');

    $routes->group('', ['filter' => 'bearer'], function($routes) {
        $routes->resource('users', ['controller' => 'Users']);
    });

});

$routes->get('api/documents', 'Api\DocumentController::index', ['filter' => 'bearer']);
$routes->get('api/document/(:num)', 'Api\DocumentController::show/$1', ['filter' => 'bearer']);

$routes->get('api/comments/(:any)', 'Api\DocumentController::getComments/$1');

$routes->post('api/documents/upload', 'Api\DocumentController::upload');

$routes->post('api/documents/autosave', 'Api\DocumentController::autosave');



/* ===============================
| PANEL ROUTES (Protected)
================================*/
$routes->group("dpanel", ["filter" => "myauth"], function($routes) {

    // Dashboard
    $routes->get("dashboard", "Panel::index");

    /* USER */
    $routes->post("user/get-info", "User::get_user_info");
    $routes->get("users/get-li-list", "User::get_li_list");
    $routes->get("favorites", "User::favorites");
    $routes->post("favorites/get-json-data", "User::favorites/get-json-data");
    $routes->post("user/favorites/add-remove", "User::favorites/add-remove");
    $routes->get("user/get-invited-lists/(:any)", "User::event/get-history/$1");
    $routes->post("user/update-roles/(:any)", "User::event/update-roles/$1");

    $routes->get("user/password", "User::event/password");
    $routes->post('user/change-password', 'User::event/change-password');
    
    $routes->get("user/profile", "User::event/profile");
    $routes->post("user/upload-avatar", "User::event/upload-avatar");
    $routes->post("user/update-profile", "User::event/update-profile");

    $routes->get('user/avatar/(:any)', 'FileController::avatar/$1');
    $routes->get("user/team-members", "User::event/team-members");


    /* PROJECT */
    $routes->get("projects", "Project::event");
    $routes->get("project/detail/(:any)", "Project::event/detail/$1");
    $routes->post("project/submit", "Project::event/submit");
    $routes->post("project/submit/(:any)", "Project::event/submit/$1");
    $routes->post("project/archive/(:any)", "Project::event/archive/$1");
    $routes->post("projects/bulk-delete", "Project::event/bulk-delete");
    $routes->delete("project/(:any)", "Project::event/delete/$1");
    $routes->post("project/share/(:any)", "Project::event/share/$1");
    $routes->post("project/invite-members", "Project::event/invite-members");
    $routes->post("projects/get-json-data", "Project::event/get-json-data");
    $routes->get("project/get-json-detail/(:any)", "Project::event/get-json-detail/$1");
    $routes->get("projects/populate", "Project::event/populate");
    $routes->post("project/attach-file", "Project::event/attach-file");
    $routes->post("project/create-folder/(:any)", "Project::event/create-folder/$1");
    $routes->post("project/rename-folder/(:any)", "Project::event/rename-folder/$1");
    $routes->delete("project/delete-folder/(:any)", "Project::event/delete-folder/$1");
    $routes->get("project/folder/(:any)", "Project::event/folder-detail/$1");


    /* FILES */
    $routes->get("files", "Files::index");
    $routes->get("storage", "Files::index");
    $routes->post("files/get-json-data", "Files::event/get-json-data");
    $routes->post("files/upload", "Files::upload");
    $routes->post("file/upload", "Files::process");
    $routes->delete("file/revert", "Files::revert");
    $routes->post("file/rename/(:any)", "Files::event/rename/$1");
    $routes->get('files/view/(:any)', 'FileController::view/$1');
    $routes->delete("file/(:any)", "Files::event/delete/$1");
    $routes->post("files/bulk-delete", "Files::event/bulk-delete");


    /* JOBS */
    $routes->get("import-document-data", "Jobs::import_document_data");


    /* SEARCH */
    $routes->post("search", "Panel::search/autocomplete");
    $routes->get("search", "Panel::search");
    $routes->post("search/get-json-data", "Panel::search/get-json-data");


    /* DOCUMENTS */
    $routes->get("documents", "Documents::index");
    $routes->get("document/add-new", "Documents::event/add-new");
    $routes->get("document/detail/(:any)", "Documents::event/detail/$1");
    $routes->get("document/version-history/(:any)", "Documents::event/version-history/$1");
    $routes->post("document/submit", "Documents::event/submit");
    $routes->post("document/submit/(:any)", "Documents::event/submit/$1");
    $routes->post("document/send-for-review/(:any)", "Documents::event/send-for-review/$1");
    $routes->post("document/send-for-review", "Documents::event/send-for-review");
    $routes->post("document/share/(:any)", "Documents::event/share/$1");
    $routes->get("document/get-json-detail/(:any)", "Documents::event/get-json-detail/$1");
    $routes->put("document/return-to-draft/(:any)", "Documents::event/return-to-draft/$1");
    $routes->put("document/accept-and-sign/(:any)", "Documents::event/accept-and-sign/$1");
    $routes->put("document/duplicate/(:any)", "Documents::event/duplicate/$1");
    $routes->get("document/getVersionContent/(:any)", "Documents::getVersionContent/$1");
    $routes->delete("document/archive/(:any)", "Documents::event/archive/$1");


    /* MISC */
    $routes->get("get-more-avatars/(:any)/(:any)", "User::moreAvatars/$1/$2");

    $routes->get("logout", "Panel::logout");
});
