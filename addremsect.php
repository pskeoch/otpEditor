<?php
//Received from editor-sections.php. Two submit form buttons, AddTitle and RemTitle go to here.
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$tlangid = $_POST['addremlid'];
$tselsec = $_POST['addremsid'];
$tsectid = $_POST['addremtid'];
$parid = $_POST['parid'];


if (isset($_POST['remsect'])) { // Check if we want to remove language
	
	if ($tsectid!="") { //Check that a section was selected on editor-sections.php
		
		//Procedure for removing section
		//Think best way to do this is to create a delete table in mysql database and move the section reference to there, deleting the section record from the sections table
		//All hierarchical sub-references to that section will remain in other tables, and no actual changes to files/folders will happen
		//This should prevent catastrophic accidental deletion
		//We then have a purge page, and if really want section deleted can go there and select to remove section and all files/folders associated from the server, or select to reinstate
		
		if ($result = $mysqli->query("INSERT INTO deletedsections SELECT * FROM sections WHERE section_id=" . $tselsec)) {
			
		} else {
			echo "unable to copy section to deletedsections";
			$mysqli->close();
			exit;
		}
	
		if ($result = $mysqli->query("DELETE FROM sections WHERE section_id=" . $tselsec)) {
			
			$_SESSION['lang'] = $langid;
			$_SESSION['parsecid'] = $parid;
			$_SESSION['selsecid'] = "na";
			$_SESSION['tsec'] = $tsectid;
			$mysqli->close();
			gotourl('/pubseditorhtml/editor-sections.php');
			exit;			
			
		} else {
			echo "couldn't delete section";
			$mysqli->close();
			exit;
		}
		
		
	} else {
		//if no selected section send them back
		$_SESSION['lang'] = $langid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = "na";
		$_SESSION['tsec'] = $tsectid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-sections.php');
	}
	
} elseif (isset($_POST['addsect'])) {

		
	//Now create new section in database, and create directories as needed
	
	
	//Now need to get sort order list for all sections associated with this parent section id/language id and initially set to last in the list
	$qry = "SELECT sort_order FROM sections WHERE parent_id='" . $tsectid . "'";
	//echo $qry
	if ($result = $mysqli->query($qry)) {
		$sortorderlast = 0;
		
		while($tsort = $result->fetch_array()) {
			if ($tsort[0]>$sortorderlast) {
				$sortorderlast = $tsort[0];
			}
		}
		$tsortord = $sortorderlast+1;
		
	} else {
		echo "Couldn't retrive sort orders for sections associated with parent id " . $tsectid;
		$mysqli->close();
		exit;
	}
	
	if ($tlangid==$tsectid) {
		$parentlang = 1;
	} else {
		$parentlang = 0;
	}
	$qry = "INSERT INTO sections(parent_is_language_id,parent_id,name,sort_order) VALUES('" . $parentlang . "','" . $tsectid . "','Untitled','" . $tsortord . "')";
	//echo $qry;
	if ($result = $mysqli->query($qry)) {
		//Now find id of just created section to return as selected section
		$tselsec = $mysqli->insert_id;
		
		//echo $tlangid . "///////////";
		//echo $parid . "....,,,,,,,,,,";
		//echo $tselsec . "?////??///";
		//echo $tsectid . "********8";
		
		$_SESSION['lang'] = $tlangid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = $tselsec;
		$_SESSION['tsec'] = $tsectid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't add section to sections table";
		$mysqli->close();
	}
		
} else {
	echo "looks like you shouldn't be here";
	$mysqli->close();
}


?>