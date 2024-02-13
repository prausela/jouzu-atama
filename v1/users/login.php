<?php

header("Access-Control-Allow-Methods: POST");

require_once 'v1/db.php';
require_once 'v1/jwt_utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// get posted data
	$data = json_decode(file_get_contents("php://input", true));

	if (!$data || !isset($data->username) || !isset($data->password)) {
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}

	$username = trim(mysqli_real_escape_string($dbConn, $data->username));
	$password = trim(mysqli_real_escape_string($dbConn, $data->password));

	if ($username === "" || $password === ""){
		closeConn($dbConn);
		http_response_code(400);
		exit();
	}
	
	$sql = "SELECT * FROM user WHERE username = '" . $username . "' LIMIT 1";
	
	$result = dbQuery($dbConn, $sql);
	
	if(!$result || dbNumRows($result) < 1) {
		closeConn($dbConn);
		http_response_code(404);
		exit();
	}

	$row = dbFetchAssoc($result);
	closeConn($dbConn);

	if (!password_verify($password, $row['password'])) {
		http_response_code(404);
		exit();
	}
	
	$username = $row['username'];
	
	$headers = array('alg'=>'HS256','typ'=>'JWT');
	$payload = array('username'=>$username, 'exp'=>(time() + 60*15));

	$jwt = generate_jwt($headers, $payload);

	$role = "user";
	if ($username === "hika") {
		$role = "admin";
	}

	$reset = "yes";
	if ($row['reset'] === "0") {
		$reset = "no";
	}

	http_response_code(200);
	echo json_encode(array('token' => $jwt, 'role' => $role, 'reset' => $reset), JSON_UNESCAPED_SLASHES);
	exit();
}

//End of file

?>