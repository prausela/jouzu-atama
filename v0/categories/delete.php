<?php

header("Access-Control-Allow-Methods: POST");

require_once 'v0/db.php';
require_once 'v0/jwt_utils.php';
require_once 'v0/url_utils.php';

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

if(!has_url_param($params, 'id')) {
    closeConn($dbConn);
    http_response_code(400);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

    if ($id === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    $sql = "SELECT position FROM category WHERE id = '" . $id . "' LIMIT 1";
	$rows = dbSelect($dbConn, $sql);

    if ($rows === false){
        closeConn($dbConn);
        http_response_code(404);
        exit();
    }    

    if (count($rows) < 1) {
        closeConn($dbConn);
        http_response_code(404);
        exit();
    } else {
        $rows = $rows[0];
        $position = ((int) $rows["position"]);
    }

    $sql = "UPDATE category SET position = position - 1 WHERE position > '" . $position . "'";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

    /* 
        Delete category query
     */
	
	$sql = "DELETE FROM category WHERE id = '" . $id . "'";
	
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