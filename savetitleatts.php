<?php

session_start();

$brandid = $_POST['bid'];
$titleid = $_POST['tid'];
$newtname = $_POST['tname'];

$lnavttl1 = $_POST['lnavttl1'];
$lnavttl2 = $_POST['lnavttl2'];
$lnavttl3 = $_POST['lnavttl3'];
$lnavttl4 = $_POST['lnavttl4'];


include 'otpedfuncs.php';
$mysqli = openmysql();


//First need to get current title_name in database because that should be current directory name
if ($result = $mysqli->query("SELECT title_name FROM titles WHERE title_id='" . $titleid . "'")) {
	$curtitlename = $result->fetch_array();
} else {
	echo "couldn't get title_name from table";
	$mysqli->close();
	exit;
}
//update directory name with title_name if it has changed
//Need to find brand name to build directory structure
if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $brandid)) {
	#gotourl('/pubseditorhtml/editor-titles.php');
	$tbrand = $result->fetch_array();
} else {
	$mysqli->close();	
	echo "error: couldn't find brand in database";
	exit;
}

if (builddirref_sel([$tbrand[0],$newtname],1,0)!=builddirref_sel([$tbrand[0],$curtitlename[0]],1,0)) {
	if (file_exists(builddirref_sel([$tbrand[0],$curtitlename[0]],1,0))) {
		if (file_exists(builddirref_sel([$tbrand[0],$newtname],1,0))) {
			#echo "This title name already exists";
			#$mysqli->close();
			#exit;
		} else {
			rename(builddirref_sel([$tbrand[0],$curtitlename[0]],1,0),builddirref_sel([$tbrand[0],$newtname],1,0));
		}
	}
}

if ($result = $mysqli->query("UPDATE titles SET title_name='" . $newtname . "',leftNavTitle1='" . $lnavttl1 . "',leftNavTitle2='" . $lnavttl2 . "',leftNavTitle3='" . $lnavttl3 . "',leftNavTitle4='" . $lnavttl4 . "' WHERE title_id='" . $titleid . "'")) {
	
	$_SESSION['brandid'] = $brandid;
	$mysqli->close();
	gotourl('/pubseditorhtml/editor-titles.php');
	exit;	

			
} else {
		
	$mysqli->close();	
	echo "error: couldn't update title in database";
	exit;
}



?>