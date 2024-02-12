<?php

header("Access-Control-Allow-Methods: POST");

require_once 'v1/db.php';

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
	
	$sql = "INSERT INTO user(username, password) VALUES('" . $username . "', '" . password_hash($password, PASSWORD_DEFAULT) . "')";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

	$sql = "SELECT id, username FROM user WHERE username = '" . $username . "' LIMIT 1";
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