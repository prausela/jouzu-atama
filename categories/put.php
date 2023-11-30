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

if(!has_url_param($params, 'id')) {
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

    if (!$data || !isset($data->name) || !isset($data->visibility) || !isset($data->position) || !is_numeric($data->position)) {
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

    $sql = "SELECT position FROM category ORDER BY position DESC LIMIT 1";
	$rows = dbSelect($dbConn, $sql);

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

    $sql = "SELECT position FROM category WHERE id = '" . $id . "' LIMIT 1";
	$rows = dbSelect($dbConn, $sql);

    if (count($rows) < 1) {
        closeConn($dbConn);
        http_response_code(404);
        exit();
    } else {
        $rows = $rows[0];
        $old_position = ((int) $rows["position"]);
    }

    if ($old_position > $new_position) {
        $sql = "UPDATE category SET position = position + 1 WHERE position >= '" . $new_position . "' AND position < '" . $old_position . "'";
	
        $result = dbQuery($dbConn, $sql);

        if (!$result) {
            closeConn($dbConn);
            http_response_code(500);
            exit();
        }
    } elseif ($old_position < $new_position) {
        $sql = "UPDATE category SET position = position - 1 WHERE position > '" . $old_position . "' AND position <= '" . $new_position . "'";
	
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
	
	$sql = "UPDATE category SET name = ('" . $name . "'), visibility = (" . $visibility . "), position = ('" . $new_position . "') WHERE id = '" . $id . "'";
	
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

    $categoryId = $id;

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