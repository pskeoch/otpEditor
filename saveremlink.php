<?php
//Received from editor-sections.php. Two submit form buttons, AddTitle and RemTitle go to here.
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$tlangid = $_POST['edlnklid'];
$tselsec = $_POST['edlnksid'];
$tsectid = $_POST['edlnktid'];
$parid = $_POST['edlnkparid'];

$lnktype = $_POST['linktype'];
$lnktitle = $_POST['linktitle'];
$lnktarg = $_POST['linktarg'];
$lnksort = $_POST['lnksortord'];
$lnkid = $_POST['edlnkid'];


if (isset($_POST['remlink'])) { // Check if we want to remove link
	
	if ($lnkid!="") { //Check that a link was selected on editor-sections.php
		
		//Procedure for removing link
		//Think best way to do this is to create a delete table in mysql database and move the link reference to there, deleting the link record from the links table
		//All hierarchical sub-references to that link will remain in other tables, and no actual changes to files/folders will happen
		//This should prevent catastrophic accidental deletion
		//We then have a purge page, and if really want link deleted can go there and select to remove link and all files/folders associated from the server, or select to reinstate
		
		if ($result = $mysqli->query("INSERT INTO deletedlinks SELECT * FROM links WHERE link_id=" . $lnkid)) {
			
		} else {
			echo "unable to copy link to deletedlinks";
			$mysqli->close();
			exit;
		}
	
		if ($result = $mysqli->query("DELETE FROM links WHERE link_id=" . $lnkid)) {
			
			$_SESSION['lang'] = $langid;
			$_SESSION['parsecid'] = $parid;
			$_SESSION['selsecid'] = $tselsec;
			$_SESSION['tsec'] = $tsectid;
			$mysqli->close();
			gotourl('/pubseditorhtml/editor-sections.php');
			exit;			
			
		} else {
			echo "couldn't delete link";
			$mysqli->close();
			exit;
		}
		
		
	} else {
		//if no selected link send them back
		$_SESSION['lang'] = $langid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = $tselsec;
		$_SESSION['tsec'] = $tsectid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-sections.php');
	}
	
} elseif (isset($_POST['savelink'])) {

		
	//Edit existing link in database
	
	if ($tlangid==$tsectid) {
		$parentlang = 1;
	} else {
		$parentlang = 0;
	}
	$qry = "UPDATE links SET name='" . $lnktitle . "',kind='" . $lnktype . "',location='" . $lnktarg . "',sort_order='" . $lnksort . "' WHERE link_id=" . $lnkid;
	//echo $qry;
	if ($result = $mysqli->query($qry)) {

		
		$_SESSION['lang'] = $tlangid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = $tselsec;
		$_SESSION['tsec'] = $tsectid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't save link changes";
		$mysqli->close();
		exit;
	}
		
} else {
	echo "looks like you shouldn't be here";
	$mysqli->close();
	exit;
}


?>