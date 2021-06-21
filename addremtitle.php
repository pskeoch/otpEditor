<?php
//Received from editor-titles.php. Two submit form buttons, AddTitle and RemTitle go to here.
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$ttitleid = $_POST['addremtid'];
$tbrandid = $_POST['addrembid'];


if (isset($_POST['remtitle'])) { // Check if we want to remove title
	
	if ($ttitleid!="") { //Check that a title was selected on editor.php
		
		//Procedure for removing title
		//Think best way to do this is to create a delete table in mysql database and move the title reference to there, deleting the title record from the titles table
		//All hierarchical sub-references to that title will remain in other tables, and no actual changes to files/folders will happen
		//This should prevent catastrophic accidental deletion
		//We then have a purge page, and if really want title deleted can go there and select to remove title and all files/folders associated from the server, or select to reinstate
		
		if ($result = $mysqli->query("INSERT INTO deletedtitles SELECT * FROM titles WHERE title_id=" . $ttitleid)) {
			
		} else {
			echo "unable to copy title to deletedtitles";
			$mysqli->close();
			exit;
		}
	
		if ($result = $mysqli->query("DELETE FROM titles WHERE title_id=" . $ttitleid)) {
			
			$_SESSION['brandid'] = $tbrandid;
			$mysqli->close();
			gotourl('/pubseditorhtml/editor-titles.php');
			exit;			
			
		} else {
			echo "couldn't delete title";
			$mysqli->close();
			exit;
		}
		
		
	} else {
		//if no title, send them back
		$_SESSION['brandid'] = $tbrandid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-titles.php');
	}
	
} elseif (isset($_POST['addtitle'])) {

	//Need to find title directory
	//First find correct brand from brand_id to get parent brand directory
	if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $tbrandid)) {	
		$ftch = $result->fetch_array();
		$bname = $ftch[0];
			
	} else {
		echo "unable to find brand name from brand id " . $tbrandid;
		$mysqli->close();
		exit;
	}
		
	//Now create new title in database and create directory
		
	$dirsuff = 1;
	while(file_exists($tnewdir = builddirref_sel([$bname,"Untitled" . $dirsuff],1,0))) {
		$dirsuff++;
	}
		
	$qry = "INSERT INTO titles(brand_id,title_name,active) VALUES('" . $tbrandid . "','Untitled" . $dirsuff . "','1')";
	if ($result = $mysqli->query($qry)) {
		$tnewdir = builddirref_add([$bname,"Untitled" . $dirsuff],1,0);
		
		$_SESSION['brandid'] = $tbrandid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-titles.php');
			
	} else {
		echo "couldn't add Untitled" . $dirsuff . " to titles table";
		$mysqli->close();
	}
		
} else {
	echo "looks like you shouldn't be here";
	$mysqli->close();
}


?>