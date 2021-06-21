<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$parentsecid = $_GET["secid"];
#echo $parentsecid;
$qry = "SELECT section_id, name FROM sections WHERE parent_id=" . $parentsecid . " AND parent_is_language_id=0";
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
		echo "no sections found for parent section id: " . $parentsecid;
	}
	
} else {
	echo "error running query: " . $qry;
}

?>