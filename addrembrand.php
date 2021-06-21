<?php

//Received from editor.php. Two submit form buttons, AddBrand and RemBrand go to here.


include 'otpedfuncs.php';
$mysqli = openmysql();

$tbrandid = $_POST['addrembid'];

if (isset($_POST['rembrand'])) { // Check if we want to remove brand
	
	if ($tbrandid!="") { //Check that a brand was selected on editor.php
		
		//Procedure for removing brand
		//Think best way to do this is to create a delete table in mysql database and move the brand reference to there, deleting the brand record from the brands table
		//All hierarchical sub-references to that brand will remain in other tables, and no actual changes to files/folders will happen
		//This should prevent catastrophic accidental deletion
		//We then have a purge page, and if really want brand deleted can go there and select to delete brand and all files/folders associated from the server, or select to reinstate brand
		
		if ($result = $mysqli->query("INSERT INTO deletedbrands SELECT * FROM brands WHERE brand_id=" . $tbrandid)) {
			
		} else {
			echo "unable to copy brand to deletedbrands";
			$mysqli->close();
			exit;
		}
		
		if ($result = $mysqli->query("DELETE FROM brands WHERE brand_id=" . $tbrandid)) {
			
			$mysqli->close();
			gotourl('/pubseditorhtml/editor.php');
			
			
		} else {
			echo "couldn't delete brand";
			$mysqli->close();
			exit;
		}
		
		
	} else {
		//if no brand, send them back
		$mysqli->close();
		gotourl('/pubseditorhtml/editor.php');

	}
	
} elseif (isset($_POST['addbrand'])) {
	
	$dirsuff = 1;
	while(file_exists($tnewdir = builddirref_sel(["Untitled" . $dirsuff],1,0))) {
		$dirsuff++;
	}
	
	$qry = "INSERT INTO brands(brand_name) VALUES('Untitled" . $dirsuff . "')"; //Brands named Untitled to begin by default
	//echo $qry;
	if ($result = $mysqli->query($qry)) {
		
		$tnewdir = builddirref_add(["Untitled". $dirsuff],1,0); //calls function to add new brand directory, creating any necessary parent directories in the structure as it does
													//2 parameters, first is directory hierarchy list starting after industry dir (i.e. "Automobile"), second is industry id ref 1= "Automobile"
		$mysqli->close();
		gotourl('/pubseditorhtml/editor.php');
		
	} else {
		echo "could't create new brand";
		$mysqli->close();
	}
	

} else {
	
	echo "looks like you shouldn't be here";
	$mysqli->close();	

}

?>