<?php

session_start();

$brandid = $_POST['bid'];
$compid = $_POST['compid'];
$newcname = $_POST['cname'];

$lnavttl1 = $_POST['lnavttl1'];
$lnavttl2 = $_POST['lnavttl2'];
$lnavttl3 = $_POST['lnavttl3'];
$lnavttl4 = $_POST['lnavttl4'];


include 'otpedfuncs.php';
$mysqli = openmysql();


//First need to get current compname in database because that should be current directory name
if ($result = $mysqli->query("SELECT compname FROM compilations WHERE compid='" . $compid . "'")) {
	$curcompname = $result->fetch_array();
	//echo "SELECT compname FROM compilations WHERE compid='" . $compid . "'";
	//var_dump($curcompname);
	//echo $compid;
} else {
	echo "couldn't get compname from table";
	$mysqli->close();
	exit;
}
//update directory name with compname if it has changed
//Need to find brand name to build directory structure
if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $brandid)) {
	#gotourl('/pubseditorhtml/editor-titles.php');
	$tbrand = $result->fetch_array();
} else {
	$mysqli->close();	
	echo "error: couldn't find brand in database";
	exit;
}

if (builddirref_sel([$tbrand[0],$newcname],1,0)!=builddirref_sel([$tbrand[0],$curcompname[0]],1,0)) {
	if (file_exists(builddirref_sel([$tbrand[0],$curcompname[0]],1,0))) {
		if (file_exists(builddirref_sel([$tbrand[0],$newcname],1,0))) {
			#echo "This compilation name already exists";
			#$mysqli->close();
			#exit;
		} else {
			rename(builddirref_sel([$tbrand[0],$curcompname[0]],1,0),builddirref_sel([$tbrand[0],$newcname],1,0));
		}
	}
}

if ($result = $mysqli->query("UPDATE compilations SET compname='" . $newcname . "',leftNavTitle1='" . $lnavttl1 . "',leftNavTitle2='" . $lnavttl2 . "',leftNavTitle3='" . $lnavttl3 . "',leftNavTitle4='" . $lnavttl4 . "' WHERE compid='" . $compid . "'")) {
	
	$_SESSION['brandid'] = $brandid;
	$mysqli->close();
	#echo "UPDATE compilations SET compname='" . $newcname . "',leftNavTitle1='" . $lnavttl1 . "',leftNavTitle2='" . $lnavttl2 . "',leftNavTitle3='" . $lnavttl3 . "',leftNavTitle4='" . $lnavttl4 . "' WHERE compid='" . $compid . "'";
	gotourl('/pubseditorhtml/editor-titles.php');
	exit;	

			
} else {
		
	$mysqli->close();	
	echo "error: couldn't update compilation in database";
	exit;
}



?>