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

if(!has_url_param($params, 'username')) {
    closeConn($dbConn);
    http_response_code(400);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 
        Get Payload 

            {
                name : "After the Rain"
            }
     */

    $data = json_decode(file_get_contents("php://input", true));

    if (!$data || !isset($data->name) || !isset($data->username)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

	$name        = trim(mysqli_real_escape_string($dbConn, $data->name));
    $newUsername = trim(mysqli_real_escape_string($dbConn, $data->username));

    $username    = trim(mysqli_real_escape_string($dbConn, $params['username']));

	if ($name === "" || $newUsername === "" || $username === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    /* 
        Update category in database
     */
	
	$sql = "UPDATE user SET name = ('" . $name . "'), username = (" . $newUsername . ") WHERE username = '" . $username . "'";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

    /*
        Get inserted category to include in response
            
            {
                id      : "123",
                name    : "After the Rain" 
            }
     */

    $sql = "SELECT id, name, username FROM user WHERE username = '" . $newUsername . "' LIMIT 1";
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
    }

    /*
        Close connection to database
     */

    closeConn($dbConn);

    /*
        Response code : 200 OK
     */

    http_response_code(200);

    /*
        Include in response's payload the updated category
    
            {
                id      : "123",
                name    : "After the Rain" 
            }
    */

    echo json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    
    exit();
} 

?>