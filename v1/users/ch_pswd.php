<?php

header("Access-Control-Allow-Methods: POST");

require_once 'v1/db.php';
require_once 'v1/jwt_utils.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim(mysqli_real_escape_string($dbConn, $params['username']));
        
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

	$reset = '1';
	if ($user === $username) {
		$reset = '0';
	}

	// get posted data
	$data = json_decode(file_get_contents("php://input", true));

	if (!$data || !isset($data->password)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

	$password = trim(mysqli_real_escape_string($dbConn, $data->password));

	if ($password === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}
	
	$sql = "UPDATE user SET password = ('" . password_hash($password, PASSWORD_DEFAULT) . "'), reset = (" . $reset . ") WHERE username = '" . $username . "'";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

	closeConn($dbConn);

	http_response_code(204);	
	exit();

}

//End of file

?>