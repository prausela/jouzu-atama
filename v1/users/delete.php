<?php

header("Access-Control-Allow-Methods: POST");

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

if ($user !== "hika") {
    closeConn($dbConn);
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input", true));

    if (!$data || !isset($data->username)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    $username   = trim(mysqli_real_escape_string($dbConn, $data->username));

    if ($username === "" || $username === "hika"){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    /* 
        Delete user query
     */
	
	$sql = "DELETE FROM user WHERE username = '" . $username . "'";
	
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