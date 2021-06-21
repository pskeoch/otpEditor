<?php
//Received from editor-sections.php. One submit form button, SaveEdit goes to here.
session_start();

include "otpedfuncs.php";
$mysqli = openmysql();

$secid = $_POST["wpeditsecid"];
$wpid = $_POST["thiswpid"];
$newwpname = $_POST["wpnamenew"];

if (isset($_POST["savewpname"])) {
	
	$qry = "UPDATE wallpaper_groups SET name='" . $newwpname . "' WHERE wallpaper_group_id=" . $wpid;
	
	if ($result = $mysqli->query($qry)) {
		
		$_SESSION['selsecid'] = $secid;
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't save wallpaper group name change";
		exit;
	}
	
} else {
	echo "looks like you shouldn't be here";
}