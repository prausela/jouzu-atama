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

    /* 
        Get Payload 

            {
                name : "After the Rain"
            }
     */

    $data = json_decode(file_get_contents("php://input", true));

    if (!$data || !isset($data->name)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

	$name = trim(mysqli_real_escape_string($dbConn, $data->name));

	if ($name === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    $sql = "SELECT position FROM category ORDER BY position DESC LIMIT 1";
	$rows = dbSelect($dbConn, $sql);

    if (count($rows) < 1) {
        $position = 0;
    } else {
        $rows = $rows[0];
        $position = ((int) $rows["position"]) + 1;
    }
	
    /* 
        Insert into database
     */

	$sql = "INSERT INTO category(name, position) VALUES('" . $name . "', '" . $position . "')";
	
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

    $categoryId = mysqli_insert_id($dbConn);

    $sql = "SELECT id, name, position, visibility FROM category WHERE id = '" . $categoryId . "' LIMIT 1";
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
        $rows['visibility'] = $rows['visibility'] == "1" ? "visible" : "invisible";
        $rows['sets_url'] = get_protocol($_SERVER) . $_SERVER['SERVER_NAME'] . "/categories/" . $rows['id'] . "/sets/get";
    }

    /*
        Close connection to database
     */

    closeConn($dbConn);

    /*
        Response code : 201 Created
     */

    http_response_code(201);

    /*
        Include in response's payload the inserted category
    
            {
                id      : "123",
                name    : "After the Rain" 
            }
    */

    echo json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    
    exit();
}

?>