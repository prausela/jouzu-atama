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

if(!has_url_param($params, 'setId')) {
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

    $setId        = trim(mysqli_real_escape_string($dbConn, $params['setId']));

    if ($setId === ""){
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

    if (!$data || !isset($data->name) || !isset($data->answers) || !isset($data->correct_answer)) {
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

    $answers = array();

    if (!is_array($data->answers) || count($data->answers) < 2) {
        closeConn($dbConn);
		http_response_code(400);
		exit();
    }

    foreach ($data->answers as $dataAnswer) {
        if (!isset($dataAnswer->name)) {
            closeConn($dbConn);
            http_response_code(400);
            exit();
        }

        $answer = new stdClass;
        $answer->name = trim(mysqli_real_escape_string($dbConn, $dataAnswer->name));

        if ($answer->name === "") {
            closeConn($dbConn);
            http_response_code(400);
            exit();
        }

        $answers[] = $answer;

    }

    if (!$data->correct_answer || !isset(($data->correct_answer)->name)){
        closeConn($dbConn);
        http_response_code(400);
        exit();
    }

    $correct_answer = new stdClass;
    $correct_answer->name = trim(mysqli_real_escape_string($dbConn, ($data->correct_answer)->name));

    if ($correct_answer->name === "") {
        closeConn($dbConn);
        http_response_code(400);
        exit();
    }
	
    /* 
        Insert into database
     */

	$sql = "INSERT INTO `question`(name, setId) VALUES('" . $name . "', '" . $setId . "')";
	
	$result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

    $questionId = mysqli_insert_id($dbConn);

    foreach ($answers as $answer){
        $sql = "INSERT INTO `answer`(name, questionId) VALUES('" . $answer->name . "', '" . $questionId . "')";

        $result = dbQuery($dbConn, $sql);

        if (!$result) {
            closeConn($dbConn);
            http_response_code(409);
            exit();
        }
    }

    $sql = "INSERT INTO `answer`(name, questionId) VALUES('" . $correct_answer->name . "', '" . $questionId . "')";

    $result = dbQuery($dbConn, $sql);

    if (!$result) {
        closeConn($dbConn);
        http_response_code(409);
        exit();
    }

    $correctAnswerId = mysqli_insert_id($dbConn);

    $sql = "INSERT INTO `correctAnswer`(answerId, questionId) VALUES('" . $correctAnswerId . "', '" . $questionId . "')";

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

    $sql = "SELECT `question`.`id`, `question`.`name`, `question`.`visible` FROM `question` WHERE `question`.`id` = '" . $questionId . "' LIMIT 1";
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
    }

    $rows = $rows[0];
    $rows['visible'] = $rows['visible'] == "1" ? true : false;

    $sql = "SELECT `answer`.`id`, `answer`.`name` FROM `answer` WHERE `answer`.`questionId` = '" . $questionId . "'";
    $answers = dbSelect($dbConn, $sql);

    if ($answers === false){
        closeConn($dbConn);
        http_response_code(500);
        exit();
    }  

    if (count($answers) < 2) {
        closeConn($dbConn);
        http_response_code(500);
        exit();
    }

    $sql = "SELECT `correctAnswer`.`answerId` FROM `correctAnswer` WHERE `correctAnswer`.`questionId` = '" . $questionId . "'";
    $correctAnswerId = dbSelect($dbConn, $sql);

    if ($correctAnswerId === false){
        closeConn($dbConn);
        http_response_code(500);
        exit();
    }  

    if (count($correctAnswerId) != 1) {
        closeConn($dbConn);
        http_response_code(500);
        exit();
    }

    $rows['answers'] = array();

    foreach ($answers as &$answer) {
        if($correctAnswerId[0]['answerId'] === $answer['id']) {
            $rows['correct_answer'] = $answer;
        } else {
            $rows['answers'][] = $answer;
        }
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