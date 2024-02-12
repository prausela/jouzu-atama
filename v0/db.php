<?php

require_once "secrets.php";
	
$dbConn = mysqli_connect($db_host, $db_name, $db_user, $db_pass);
mysqli_set_charset($dbConn, 'utf8');

function dbQuery($dbConn, $sql) {
	$result = mysqli_query($dbConn, $sql);
	return $result;
}

function dbSelect($dbConn, $sql) {
	$results = dbQuery($dbConn, $sql);

	if (!$results){
		return $results;
	}

	$rows = array();

	while($row = dbFetchAssoc($results)) {
		$rows[] = $row;
	}

	return $rows;
}

function dbFetchAssoc($result) {
	return mysqli_fetch_assoc($result);
}

function dbNumRows($result) {
    return mysqli_num_rows($result);
}

function closeConn($dbConn) {
	mysqli_close($dbConn);
}
	
//End of file

?>