<?php

$brandid = $_POST['bid'];
$newbname = $_POST['bname'];
$introback = $_POST['introback'];
$introhorz = $_POST['introhorz'];
$introlang = $_POST['introlang'];
$introbuts = $_POST['introbuts'];
$introbutslab = $_POST['introbutslab'];
$leftnavpan = $_POST['leftnavpan'];
$rightpan = $_POST['rightpan'];
$gallerypan = $_POST['gallerypan'];
$leftnavbut = $_POST['leftnavbut'];
$leftnavbuthov = $_POST['leftnavbuthov'];


include 'otpedfuncs.php';
$mysqli = openmysql();


//First need to get current brand_name in database because that should be current directory name
if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id='" . $brandid . "'")) {
	$curbrandname = $result->fetch_array();
} else {
	echo "couldn't get brand_name from table";
	$mysqli->close();
	exit;
}
//update directory name with brand_name if it has changed
if (builddirref_sel([$newbname],1,0)!=builddirref_sel([$curbrandname[0]],1,0)) {
	if (file_exists(builddirref_sel([$curbrandname[0]],1,0))) {
		if (file_exists(builddirref_sel([$newbname],1,0))) {
			echo "This brand name already exists";
			exit;
		} else {
			rename(builddirref_sel([$curbrandname[0]],1,0),builddirref_sel([$newbname],1,0));
		}
	}
}

if ($result = $mysqli->query("UPDATE brands SET brand_name='" . $newbname . "',introBgColor='" . $introback . "',introImgBgColor='" . $introhorz . "',introLangPaneBgColor='" . $introlang . "',introBtnBgColor='" . $introbuts . "',introBtnLabelColor='" . $introbutslab . "',leftPaneColor='" . $leftnavpan . "',rightPaneColor='" . $rightpan . "',galleryPaneColor='" . $gallerypan . "',leftNavBtnColor='" . $leftnavbut . "',leftNavBtnOnColor='" . $leftnavbuthov . "' WHERE brand_id='" . $brandid . "'")) {
	
	$mysqli->close();
	gotourl('/pubseditorhtml/editor.php');
	exit;

			
} else {
		
	$mysqli->close();	
	echo "error: couldn't find brands in database";
	exit;
}



?>