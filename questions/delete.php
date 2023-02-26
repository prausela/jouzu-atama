<?php

header("Access-Control-Allow-Methods: POST");

require_once 'db.php';
require_once 'jwt_utils.php';
require_once 'url_utils.php';

$bearer_token = get_bearer_token();

if(!$bearer_token){
    
    http_response_code(401);
    exit();
}

$is_jwt_valid = is_jwt_valid($bearer_token);

if (!$is_jwt_valid){
    
    http_response_code(401);
    exit();
}

if(!has_url_param($params, 'categoryId')) {
    closeConn($dbConn);
    http_response_code(400);
    exit();
}

if(!has_url_param($params, 'setId')) {
    closeConn($dbConn);
    http_response_code(400);
    exit();
}

if(!has_url_param($params, 'id')) {
    closeConn($dbConn);
    http_response_code(400);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $categoryId   = trim(mysqli_real_escape_string($dbConn, $params['categoryId']));

    if ($categoryId === ""){
        closeConn($dbConn);
        http_response_code(400);
        exit();
    }

    $setId        = trim(mysqli_real_escape_string($dbConn, $params['setId']));

    if ($setId === ""){
        closeConn($dbConn);
        http_response_code(400);
        exit();
    }

    $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

    if ($id === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    /* 
        Delete category query
     */
	
	$sql = "DELETE FROM `set` WHERE id = '" . $id . "' AND categoryId = '" . $categoryId . "'";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(404);
        exit();
    }

    closeConn($dbConn);

    http_response_code(204);
    exit();
}

?>