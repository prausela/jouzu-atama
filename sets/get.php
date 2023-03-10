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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $categoryId   = trim(mysqli_real_escape_string($dbConn, $params['categoryId']));

    if ($categoryId === ""){
        closeConn($dbConn);
        http_response_code(400);
        exit();
    }

    $sql = "";
    if(!has_url_param($params, 'id')){
        $sql = "SELECT id, name FROM `set` WHERE categoryId = '" . $categoryId . "'";
    } else {
        $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

        if ($id === ""){
            closeConn($dbConn);
            http_response_code(400);
            exit();
        }

        $sql = "SELECT id, name FROM `set` WHERE id = '" . $id . "' AND categoryId = '" . $categoryId . "' LIMIT 1";
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
            $rows['questions_url'] = get_protocol($_SERVER) . $_SERVER['SERVER_NAME'] . "/categories/" . $categoryId . "/sets/" . $rows['id'] . "/questions/get";
        }
    } else {
        foreach ($rows as &$row) {
            $row['questions_url'] = get_protocol($_SERVER) . $_SERVER['SERVER_NAME'] . "/categories/" . $categoryId . "/sets/" . $row['id'] . "/questions/get";
        }
    }

    closeConn($dbConn);

    http_response_code(200);

    echo json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    exit();
} 
?>