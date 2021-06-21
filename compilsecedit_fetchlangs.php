<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$ttlid = $_GET["ttlid"];

#echo "hello there";
$qry = "SELECT language_id, language FROM languages WHERE title_id=" . $ttlid;
if ($result = $mysqli->query($qry)) {

	if ($result->num_rows > 0) {
	
		$jslangsecarr = "[";

		while ($langinfo = $result->fetch_assoc()) {
			$jslangsecarr .= "[";
			$jslangsecarr .= $langinfo["language_id"] . ",\"" . $langinfo["language"] . "\"],";
		}
		
		$jslangsecarr = substr_replace($jslangsecarr, "]",-1);
		
		echo $jslangsecarr;
	} else {
		echo "no languages found for title id: " . $ttlid;
	}
} else {
	echo "error running query: " . $qry;
}

?>