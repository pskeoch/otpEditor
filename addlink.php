<?php
//Received from editor-sections.php. One button, AddLink goes here
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$tlangid = $_POST['lnkaddlid'];
$tselsec = $_POST['lnkaddsid'];
$tsectid = $_POST['lnkaddtid'];
$parid = $_POST['lnkaddparid'];

//echo $tlangid;
//echo $tselsec;

	
if (isset($_POST['addlink'])) {

		
	//Now create new link in database, and create directories as needed
	
	
	//Now need to get sort order list for all links associated with this parent section id and initially set to last in the list
	$qry = "SELECT sort_order FROM links WHERE parent_section_id='" . $tselsec . "'";
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
		echo "Couldn't retrive sort orders for links associated with parent_section id " . $tselsec;
		exit;
	}
	
	if ($tlangid==$tsectid) {
		$parentlang = 1;
	} else {
		$parentlang = 0;
	}
	$qry = "INSERT INTO links(parent_section_id,name,sort_order) VALUES('" . $tselsec . "','Untitled','" . $tsortord . "')";
	//echo $qry;
	if ($result = $mysqli->query($qry)) {
		
		$_SESSION['lang'] = $tlangid;
		$_SESSION['parsecid'] = $parid;
		$_SESSION['selsecid'] = $tselsec;
		$_SESSION['tsec'] = $tsectid;
		gotourl('/pubseditorhtml/editor-sections.php');
			
	} else {
		echo "couldn't add link to links table";
	}
		
} else {
	echo "looks like you shouldn't be here";
}


?>