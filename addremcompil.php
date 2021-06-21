<?php
//Received from editor-titles.php. Two submit form buttons, AddTitle and RemTitle go to here.
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$tcompilid = $_POST['addremcompid'];
$tbrandid = $_POST['addrembid'];

if (isset($_POST['remcompil'])) { // Check if we want to remove compilaton
	
	if ($tcompilid!="") { //Check that a compilation was selected on editor.php
		
		//Procedure for removing compilation
		//Simply remove compilation record from compilation table
		//Keep directories in place
		
		#echo "DELETE FROM compilations WHERE compid=" . $tcompilid;
	
		if ($result = $mysqli->query("DELETE FROM compilations WHERE compid=" . $tcompilid)) {
			
			$_SESSION['brandid'] = $tbrandid;
			$mysqli->close();
			gotourl('/pubseditorhtml/editor-titles.php');
			exit;			
			
		} else {
			echo "couldn't delete compilation";
			$mysqli->close();
			exit;
		}
		
		
	} else {
		//if no compilation, send them back
		$_SESSION['brandid'] = $tbrandid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-titles.php');
	}
	
} elseif (isset($_POST['addcompil'])) {

	//First find correct brand from brand_id to get parent brand directory
	if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $tbrandid)) {	
		$ftch = $result->fetch_array();
		$bname = $ftch[0];
			
	} else {
		echo "unable to find brand name from brand id " . $tbrandid;
		$mysqli->close();
		exit;
	}
		
	//Now create new compilation in database and create directory
		
	$dirsuff = 1;
	while(file_exists($tnewdir = builddirref_sel([$bname,"Untitled" . $dirsuff],1,0))) {
		$dirsuff++;
	}
		
	$qry = "INSERT INTO compilations(compname,brandid,tmplt_titleid,compilemainsecs) VALUES('Untitled" . $dirsuff . "'," . $tbrandid . ",12,'')";
	#echo $qry;
	if ($result = $mysqli->query($qry)) {
		$tnewdir = builddirref_add([$bname,"Untitled" . $dirsuff],1,0);
		
		$_SESSION['brandid'] = $tbrandid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-titles.php');
			
	} else {
		echo "couldn't add Untitled" . $dirsuff . " to compilations table";
		$mysqli->close();
	}
		
} else {
	echo "looks like you shouldn't be here";
	$mysqli->close();
}


?>