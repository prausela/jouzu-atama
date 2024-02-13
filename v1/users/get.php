<?php

header("Access-Control-Allow-Methods: GET");

require_once 'v1/db.php';
require_once 'v1/jwt_utils.php';
require_once 'v1/url_utils.php';

$bearer_token = get_bearer_token();

if(!$bearer_token){
    closeConn($dbConn);
    http_response_code(401);
    exit();
}

$is_jwt_valid = is_jwt_valid($bearer_token);

if (!$is_jwt_valid){
    closeConn($dbConn);
    http_response_code(401);
    exit();
}

$user = get_user_from_token($bearer_token);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "";
    if(!has_url_param($params, 'username')){
        if ($user !== "hika") {
            closeConn($dbConn);
            http_response_code(401);
            exit();
        }

        $sql = "SELECT username FROM users";
    } else {
        $username   = trim(mysqli_real_escape_string($dbConn, $params['username']));
        
        if ($username === ""){
            closeConn($dbConn);
            http_response_code(400);
            exit();
        }

        if ($user !== $username && $user !== "hika") {
            closeConn($dbConn);
            http_response_code(401);
            exit();
        }

        $sql = "SELECT username FROM users WHERE id = '" . $id . "' LIMIT 1";
    }
    
    $rows = dbSelect($dbConn, $sql);

    if ($rows === false){
        closeConn($dbConn);
        http_response_code(404);
        exit();
    }    

    if(has_url_param($params, 'username')){
        if (count($rows) < 1) {
            closeConn($dbConn);
            http_response_code(404);
            exit();
        } else {
            $rows = $rows[0];
        }
    }

    closeConn($dbConn);

    http_response_code(200);

    echo json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    exit();
} 
?>