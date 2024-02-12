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

    if (!$data || !isset($data->name) || !isset($data->position) || !is_numeric($data->position)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

	$name = trim(mysqli_real_escape_string($dbConn, $data->name));
    $visibility = trim(mysqli_real_escape_string($dbConn, $data->visibility));
    if (is_int($data->position)){
        $new_position = (int) $data->position;
    } else {
        $new_position = (int) trim(mysqli_real_escape_string($dbConn, $data->position));
    }
    $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

	if ($name === "" || $id === "" || ($visibility !== "visible" && $visibility !== "invisible")){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

    $visibility = $visibility === "visible" ? "TRUE" : "FALSE";

    $sql = "SELECT position FROM `set` WHERE categoryId = '" . $categoryId . "' ORDER BY position DESC LIMIT 1";
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
        $last_position = ((int) $rows["position"]);
    }

    if ($new_position > $last_position) {
        closeConn($dbConn);
		http_response_code(400);
		exit();
    }

    $sql = "SELECT position FROM `set` WHERE id = '" . $id . "' AND categoryId = '" . $categoryId . "' LIMIT 1";
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
        $old_position = ((int) $rows["position"]);
    }

    if ($old_position > $new_position) {
        $sql = "UPDATE `set` SET position = position + 1 WHERE categoryId = '" . $categoryId . "' AND position >= '" . $new_position . "' AND position < '" . $old_position . "'";
	
        $result = dbQuery($dbConn, $sql);

        if (!$result) {
            closeConn($dbConn);
            http_response_code(500);
            exit();
        }
    } elseif ($old_position < $new_position) {
        $sql = "UPDATE `set` SET position = position - 1 WHERE categoryId = '" . $categoryId . "' AND position > '" . $old_position . "' AND position <= '" . $new_position . "'";
	
        $result = dbQuery($dbConn, $sql);

        if (!$result) {
            closeConn($dbConn);
            http_response_code(500);
            exit();
        }
    }


    /* 
        Update category in database
     */
	
	$sql = "UPDATE `set` SET name = ('" . $name . "'), visibility = (" . $visibility . "), position = ('" . $new_position . "') WHERE id = '" . $id . "' AND categoryId = '" . $categoryId . "'";
	
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

    $sql = "SELECT id, name, position, visibility FROM `set` WHERE id = '" . $setId . "' LIMIT 1";
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