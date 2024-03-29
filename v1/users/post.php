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

if ($user !== "hika") {
    closeConn($dbConn);
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// get posted data
	$data = json_decode(file_get_contents("php://input", true));

	if (!$data || !isset($data->username) || !isset($data->password) || !isset($data->name)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

	$username = trim(mysqli_real_escape_string($dbConn, $data->username));
	$password = trim(mysqli_real_escape_string($dbConn, $data->password));
	$name = trim(mysqli_real_escape_string($dbConn, $data->name));

	if ($username === "" || $password === "" || $name === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}
	
	$sql = "INSERT INTO user(username, password, name) VALUES('" . $username . "', '" . password_hash($password, PASSWORD_DEFAULT) . "', '" . $name . "')";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

	$sql = "SELECT id, username, name FROM user WHERE username = '" . $username . "' LIMIT 1";
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

	http_response_code(201);

	echo json_encode($rows, JSON_UNESCAPED_SLASHES);
	
	exit();

}

//End of file

?>