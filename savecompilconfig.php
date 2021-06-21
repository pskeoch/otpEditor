<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

//First need to delete any existing references to titles for this compilation id
$qry = "DELETE FROM comptitles WHERE compid=" . $_SESSION["compilid"];
if ($result = $mysqli->query($qry)) {
			
} else {
	$mysqli->close();	
	echo "error: couldn't delete compilation title references in database";
	exit;
}

foreach($_POST as $key => $value) {
	$qry = "INSERT INTO comptitles(compid,titleid) VALUES(" . $_SESSION["compilid"] . "," . $key . ")";
	if ($result = $mysqli->query($qry)) {
			
	} else {
			
		$mysqli->close();	
		echo "error: couldn't insert title references into compilations in database";
		exit;
	}
}

$mysqli->close();
gotourl('/pubseditorhtml/editor-compils.php');
exit;

?>