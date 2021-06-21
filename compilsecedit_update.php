<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$secexemptls = $_POST["secremovechks"];
$secexemptstr = implode(",",$secexemptls);

$qry = "UPDATE `compilations` SET `compsecexempt`=\"" . $secexemptstr . "\" WHERE compid=" . $_SESSION["compilid"];
echo $qry;
if ($result = $mysqli->query($qry)) {

	$mysqli->close();
	gotourl('/pubseditorhtml/editor-compils.php');
	exit;

} else {
	$mysqli->close();	
	echo "error: couldn't update compilation exemption list";
	exit;
} 

?>