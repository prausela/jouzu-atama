<?php

header("Access-Control-Allow-Methods: GET");

require_once 'db.php';
require_once 'jwt_utils.php';
require_once 'url_utils.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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

    $sql = "";
    if(!has_url_param($params, 'id')){
        $sql = "SELECT `question`.`id`, `question`.`name`, `question`.`visible` FROM `question` INNER JOIN `set` ON `question`.`setId` = `set`.`id` WHERE setId = '" . $setId . "' AND categoryId = '" . $categoryId ."'";
    } else {
        $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

        if ($id === ""){
            closeConn($dbConn);
            http_response_code(400);
            exit();
        }

        $sql = "SELECT `question`.`id`, `question`.`name`, `question`.`visible` FROM `question` INNER JOIN `set` ON `question`.`setId` = `set`.`id` WHERE `question`.`id` = '" . $id . "' AND categoryId = '" . $categoryId . "' AND setId ='" . $setId . "' LIMIT 1";
    }
    
    $rows = dbSelect($dbConn, $sql);

    if ($rows === false){
        closeConn($dbConn);
        http_response_code(404);
        exit();
    }    

    if(has_url_param($params, 'id')){
        if (count($rows) < 1) {
            closeConn($dbConn);
            http_response_code(404);
            exit();
        } else {
            $rows = $rows[0];
            $rows['visible'] = $rows['visible'] == "1" ? true : false;

            $sql = "SELECT `answer`.`id`, `answer`.`name` FROM `answer` WHERE `answer`.`questionId` = '" . $id . "'";
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

            $sql = "SELECT `correctAnswer`.`answerId` FROM `correctAnswer` WHERE `correctAnswer`.`questionId` = '" . $id . "'";
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
        }
    } else {
        foreach ($rows as &$row) {
            $sql = "SELECT `answer`.`id`, `answer`.`name` FROM `answer` WHERE `answer`.`questionId` = '" . $row['id'] . "'";
            $answers = dbSelect($dbConn, $sql);

            if ($answers === false){
                closeConn($dbConn);
                http_response_code(500);
                exit();
            }  

            if (count($answers) < 3) {
                closeConn($dbConn);
                http_response_code(500);
                exit();
            }

            $sql = "SELECT `correctAnswer`.`answerId` FROM `correctAnswer` WHERE `correctAnswer`.`questionId` = '" . $row['id'] . "'";
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

            $row['visible'] = $row['visible'] == "1" ? true : false;
            $row['answers'] = array();

            foreach ($answers as &$answer) {
                if($correctAnswerId[0]['answerId'] === $answer['id']) {
                    $row['correct_answer'] = $answer;
                } else {
                    $row['answers'][] = $answer;
                }
            }
        }
    }

    closeConn($dbConn);

    http_response_code(200);

    echo json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    exit();
} 
?>