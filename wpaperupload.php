<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

//var_dump($_SESSION);

$secid = $_POST["wpfiles-secid"];


//find corresponding brand_name for directory
if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $_SESSION["brandid"])) {	
	$ftch = $result->fetch_array();
	$bname = $ftch[0];
			
} else {
	echo "unable to find brand name from brand id " . $_SESSION["brandid"];
	exit;
}
//find corresponding title_name for directory
if ($result = $mysqli->query("SELECT title_name FROM titles WHERE title_id=" . $_SESSION["titleid"])) {	
	$ftch = $result->fetch_array();
	$tname = $ftch[0];
			
} else {
	echo "unable to find title name from title_id " . $_SESSION["titleid"];
	exit;
}

$target_titledir = builddirref_sel([$bname,$tname],1,1);
$target_picsdir = builddirref_sel([$bname,$tname,$GLOBALS['picsdir'][0],$GLOBALS['picsdir'][1],"wallpapers"],1,1);
//check if target assets/pics/wallpapers directory already exists for this title
if (file_exists($target_picsdir)) {
	
} else {
	$target_picsdir = builddirref_add([$bname,$tname,$GLOBALS['picsdir'][0],$GLOBALS['picsdir'][1],"wallpapers"],1,1);
}

$i = 0;
while($i<count($_FILES["wpfiles"]["name"])) {
	
	$target_file = $target_picsdir . "/" . basename($_FILES["wpfiles"]["name"][$i]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["wpfiles"]["tmp_name"][$i]);
	  if($check !== false) {
		//echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	  } else {
		echo "File is not an image.";
		$uploadOk = 0;
	  }
	}

	// Allow certain file formats
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
	&& $imageFileType != "gif" ) {
	  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
	  $uploadOk = 0;
	}

	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
	  echo "Sorry, your file was not uploaded.";
	// if everything is ok, try to upload file
	} else {
	  if (move_uploaded_file($_FILES["wpfiles"]["tmp_name"][$i], $target_file)) {
		//echo "The file ". basename( $_FILES["files"]["name"][$i]). " has been uploaded.";
	  } else {
		echo "Sorry, there was an error uploading your file.";
	  }
	}
	
	$i++;
}

$_SESSION["selsecid"] = $secid;
gotourl('/pubseditorhtml/editor-sections.php');

?>