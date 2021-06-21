<?php
//Received from editor-sections.php. Two buttons, Add and Remove go here
session_start();

include "otpedfuncs.php";
$mysqli = openmysql();

$wpid= $_POST["wpsize-wpid"];
$secid = $_POST["wpsize-secid"];
$wpsizeid = $_POST["wpapersize"];

if (isset($_POST['addwpsize'])) {

	$qry = "INSERT INTO wallpapers(wallpaper_group_id,size) VALUES('$wpid','Untitled')";
	
	if ($result = $mysqli->query($qry)) {
		
		$_SESSION['selsecid'] = $secid;
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't add wallpaper size to wallpapers table";
	}

} else if (isset($_POST['remwpsize'])) {
	
	$qry = "DELETE FROM wallpapers WHERE wallpaper_id=" . $wpsizeid;
	
	if ($result = $mysqli->query($qry)) {
		
		$_SESSION['selsecid'] = $secid;
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't delete wallpaper size from wallpapers table";
	}

} else {
	echo "looks like you shouldn't be here";
}