<?php

header("Access-Control-Allow-Methods: GET");

require_once 'v0/db.php';
require_once 'v0/jwt_utils.php';
require_once 'v0/url_utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $order_by = "";
    if (isset($_GET["sort"])) {
        $sort     = trim(mysqli_real_escape_string($dbConn, $_GET["sort"]));
        $order_by = " ORDER BY " . $sort . " ASC";
    }

    $sql = "";
    if(!has_url_param($params, 'id')){
        $sql = "SELECT id, name, position, visibility FROM category" . $order_by;
    } else {
        $id   = trim(mysqli_real_escape_string($dbConn, $params['id']));

        if ($id === ""){
            closeConn($dbConn);
            http_response_code(400);
            exit();
        }

        $sql = "SELECT id, name, position, visibility FROM category WHERE id = '" . $id . "' LIMIT 1" . $order_by;
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
            $rows['visibility'] = $rows['visibility'] == "1" ? "visible" : "invisible";
            $rows['sets_url'] = get_protocol($_SERVER) . $_SERVER['SERVER_NAME'] . "/categories/" . $rows['id'] . "/sets/get";
        }
    } else {
        foreach ($rows as &$row) {
            $row['visibility'] = $row['visibility'] == "1" ? "visible" : "invisible";
            $row['set_url'] = get_protocol($_SERVER) . $_SERVER['SERVER_NAME'] . "/categories/" . $row['id'] . "/sets/get";
        }
    }

    closeConn($dbConn);

    http_response_code(200);

    echo json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    exit();
} 
?>