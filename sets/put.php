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
    $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

	if ($name === "" || $id === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    /* 
        Update category in database
     */
	
	$sql = "UPDATE `set` SET name = ('" . $name . "') WHERE id = '" . $id . "' AND categoryId = '" . $categoryId . "'";
	
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

    $setId = $id;

    $sql = "SELECT id, name, visible FROM `set` WHERE id = '" . $setId . "' LIMIT 1";
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
        $rows['visible'] = $rows['visible'] == "1" ? true : false;
        $rows['questions_url'] = get_protocol($_SERVER) . $_SERVER['SERVER_NAME'] . "/categories/" . $categoryId . "/sets/" . $rows['id'] . "/questions/get";
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