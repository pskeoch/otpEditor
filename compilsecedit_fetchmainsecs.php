<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$langid = $_GET["langid"];

$qry = "SELECT section_id, name FROM sections WHERE parent_id=" . $langid . " AND parent_is_language_id=1";
if ($result = $mysqli->query($qry)) {

	if ($result->num_rows>0) {
	
		$jssecarr = '[';

		while ($secinfo = $result->fetch_assoc()) {
			$jssecarr .= '[';
			$jssecarr .= '"' . $secinfo["section_id"] . '","' . $secinfo["name"] . '"],';
			
		}
		$jssecarr = substr_replace($jssecarr, ']',-1);
		
		echo $jssecarr;
	} else {
		echo "no sections found for language id: " . $langid;
	}
	
} else {
	echo "error running query: " . $qry;
}

?>