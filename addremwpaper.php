<?php
//Received from editor-sections.php. One button, AddLink goes here
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$wpid= $_POST['wpapername'];
$secid = $_POST['wpsecid'];

if (isset($_POST['addwpname'])) {

	$qry = "INSERT INTO wallpaper_groups(parent_section_id,name) VALUES('$secid','Untitled')";
	
	if ($result = $mysqli->query($qry)) {
		
		$_SESSION['lang'] = $tlangid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = $secid;
		$_SESSION['tsec'] = $tsectid;
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't add wallpaper group to wallpaper_groups table";
	}

} else if (isset($_POST['remwpname'])) {
	
	$qry = "DELETE FROM wallpaper_groups WHERE wallpaper_group_id=" . $wpid;
	
	if ($result = $mysqli->query($qry)) {
		
		$_SESSION['lang'] = $tlangid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = $secid;
		$_SESSION['tsec'] = $tsectid;
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't delete wallpaper group from wallpaper_groups table";
	}

} else {
	echo "looks like you shouldn't be here";
}