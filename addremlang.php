<?php
//Received from editor-langs.php. Two submit form buttons, AddLang and RemLang go to here.
session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

$tlangid = $_POST['addremlid'];
$ttitleid = $_POST['addremtid'];
$tbrandid = $_POST['addrembid'];

Class sectionGroup {
	//Properties
	public $groupid;
	public $istoplevel;
	public $parentid;
	public $childgrps;
	public $mysqli;
	public $newgroupid;
	
	//Methods
	function __construct($groupid,$mysqli,$istoplevel,$newgroupid) {
		//$parxml->asXML("textxml" . $groupid . ".xml");
		$this->groupid = $groupid;
		
		//$parxml->asXML("textxml" . $groupid . ".xml");
		
		$this->childgrps = array();
		$this->mysqli = $mysqli;
		$this->istoplevel = $istoplevel;
		$this->newgroupid = $newgroupid;
		#echo $this->groupid;
	}
	
	function nextsg($tsecarr,$newsecid) {
		#echo $newsecid;
		#echo "SELECT section_id FROM sections WHERE parent_id='" . $tsecarr["section_id"] . "'";
		if ($result=$this->mysqli->query("SELECT section_id FROM sections WHERE parent_id='" . $tsecarr["section_id"] . "'")) {
			//echo "bye" . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
			if ($result->num_rows>0) {
				$tsg = new sectionGroup($tsecarr["section_id"],$this->mysqli,0,$newsecid);
				array_push($this->childgrps,$tsg);
				$tsg->exploresecs();
			} else {
				#echo "no subsections in " . $tsecarr["section_id"] . "||" . $newsecid . "||" . $tsecarr["name"];
			}
		} else {
			echo "couldn't obtain subsection info for " . $tsecarr["section_id"];
		}
	}
	
	
	function wallpaper($tsecarr,$newsecid) {
		//echo "wppane " . $tsecarr['section_id'];
		
		$qry = "SELECT * FROM wallpaper_groups WHERE parent_section_id=" . $tsecarr["section_id"];
		#$qry = mysqlselect_encconv($varlist,"utf8","wallpaper_groups","parent_section_id",$tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],"sort_order");
		#echo $qry;
		$twpgarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$tsecarr["section_id"],"parent_section_id"]);
		#var_dump($twpgarr);
							
		foreach ($twpgarr as $twpgrp) {
			var_dump($twpgrp);
			$qry = "INSERT INTO wallpaper_groups(parent_section_id,name) VALUES(" . $newsecid . ",'" . $twpgrp["name"] . "')";
			#echo $qry;
			if ($result = $this->mysqli->query($qry)) {
				$newwpgid = $this->mysqli->insert_id;
			} else {
				echo "couldn't duplicate wallpaper group";
				exit;
			}
							
			#$qry = mysqlselect_encconv($varlist,"utf8","wallpapers","wallpaper_group_id",$twpgrp["CONVERT(CAST(wallpaper_group_id as BINARY) USING utf8)"],"sort_order");
			$qry = "SELECT * FROM wallpapers WHERE wallpaper_group_id=" . $twpgrp["wallpaper_group_id"];
			$twparr = sqlbuild_mysqlsel($this->mysqli,$qry,[$twpgrp["wallpaper_group_id"],"wallpaper_group_id"]);
									
			foreach($twparr as $twp) {
				//e.g. <screensize_btn file="assets/pics/wallpapers/wp1_800x600.jpg"><![CDATA[800 x 600]]></screensize_btn>
				$qry = "INSERT INTO wallpapers(wallpaper_group_id,size,sort_order) VALUES(" . $newwpgid . ",\"" . $twp["size"] . "\"," . $twp["sort_order"] . ")";
				echo $qry;
				if ($result = $this->mysqli->query($qry)) {
				} else {
					echo "couldn't duplicate wallpaper";
					exit;
				}
			}
		}		
		
	}
	
	function quitpane($tsecarr,$newsecid) {
	//
	}
	
	function picgall($tsecarr,$newsecid) {
	//
	}
	
	
	function sectypes($tsecarr,$newsecid) {
		#echo $newsecid;
		switch ($tsecarr["pane_type"]) {
			case 4:
				$this->wallpaper($tsecarr,$newsecid);
				break;
			case 5:
				$this->quitpane($tsecarr,$newsecid);
				break;
			case 6:
				$this->picgall($tsecarr,$newsecid);
			default: 
				$this->nextsg($tsecarr,$newsecid);
		}
	}
	
	function writelinks($tsecarr,$newsecid) {
		#echo $newsecid;
		//echo "links " . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
		//add links
		//$qry = mysqlselect_encconv($varlist,"utf8","links2","parent_section_id",$tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],"sort_order");
		//if ($tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"]==1037) {
			//echo $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
			$qry = "SELECT * FROM links WHERE parent_section_id=" . $tsecarr["section_id"] . " ORDER BY sort_order";
		//	echo $qryop;
		//}
		$tlinksarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$tsecarr["section_id"],"parent_section_id"]);
		//var_dump($tlinksarr);
		if (count($tlinksarr)>0) {
			//echo $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
	
			foreach($tlinksarr as $tlink) {
			
				
				$qry = "INSERT INTO links(parent_section_id,name,kind,location,sort_order) VALUES(" . $newsecid . ",\"" . $tlink["name"] . "\"," . $tlink["kind"] . ",\"" . $tlink["location"] . "\"," . $tlink["sort_order"] . ")";
				if ($result = $this->mysqli->query($qry)) {
				} else {
					echo "couldn't duplicate new link";
					exit;
				}
			}
		}
		
		$this->sectypes($tsecarr,$newsecid);
	}
	
	function stdupesec($tsecarr) {
		#echo $tsecarr["name"];
		$qry = "INSERT INTO sections(parent_is_language_id,parent_id,name,pane_type,copy,image,sort_order) VALUES(" . $tsecarr["parent_is_language_id"] . "," . $this->newgroupid . ",\"" . $tsecarr["name"] . "\"," . $tsecarr["pane_type"] . ",\"" . $tsecarr["copy"] . "\",\"" . $tsecarr["image"] . "\"," . $tsecarr["sort_order"] . ")";
		#echo $qry;
		if ($result = $this->mysqli->query($qry)) {
		} else {
			echo "couldn't insert new section";
			exit;
		}
		$newsecid = $this->mysqli->insert_id;
		$this->writelinks($tsecarr,$newsecid);
	}
	
	function exploresecs() {
		//echo "groupy " . $this->groupid;
					
		if ($this->istoplevel == 1) { //If this is the top level of the title, in which language id is parent id
			$qry = "SELECT * FROM sections WHERE parent_id=" . $this->groupid . " AND parent_is_language_id=1";
			#mysqlselect_encconv_and($varlist,"utf8","sections",["parent_id","parent_is_language_id"],[$this->groupid,1],"sort_order");
		} else {
			#$qry = mysqlselect_encconv_and($varlist,"utf8","sections",["parent_id","parent_is_language_id"],[$this->groupid,0],"sort_order");
			$qry = "SELECT * FROM sections WHERE parent_id=" . $this->groupid . " AND parent_is_language_id=0";
		}
		
		$tparsecarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$this->groupid,"parent_id"]);
		#var_dump($tparsecarr);
		
		//write sections and
		//check if any subsections are group parents themselves
		foreach($tparsecarr as $tparsec) {
			//echo $tparsec["CONVERT(CAST(name as BINARY) USING utf8)"];
			
			$this->stdupesec($tparsec);
		}
		
	}
	
	function outputxml($dirbuildls,$indref,$txmlfile) {

	}

}


if (isset($_POST['remlang'])) { // Check if we want to remove language
	
	if ($tlangid!="") { //Check that a language was selected on editor.php
		
		//Procedure for removing language
		//Think best way to do this is to create a delete table in mysql database and move the language reference to there, deleting the language record from the languages table
		//All hierarchical sub-references to that language will remain in other tables, and no actual changes to files/folders will happen
		//This should prevent catastrophic accidental deletion
		//We then have a purge page, and if really want language deleted can go there and select to remove language and all files/folders associated from the server, or select to reinstate
		//echo "INSERT INTO deletedlanguages SELECT * FROM languages WHERE language_id=" . $tlangid;
		
		if ($result = $mysqli->query("INSERT INTO deletedlanguages SELECT * FROM languages WHERE language_id=" . $tlangid)) {
			
		} else {
			echo "unable to copy language to deletedlanguages";
			$mysqli->close();
			exit;
		}
	
		if ($result = $mysqli->query("DELETE FROM languages WHERE language_id=" . $tlangid)) {
			
			$_SESSION['brandid'] = $tbrandid;
			$_SESSION['titleid'] = $ttitleid;
			$mysqli->close();
			gotourl('/pubseditorhtml/editor-langs.php');
			exit;			
			
		} else {
			echo "couldn't delete language";
			$mysqli->close();
			exit;
		}
		
		
	} else {
		//if no language send them back
		$_SESSION['brandid'] = $tbrandid;
		$_SESSION['titleid'] = $ttitleid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-langs.php');
	}
	
} elseif (isset($_POST['addlang'])) {

	//Need to find title directory
	//First find correct brand and title names from brand_id and title_id to get parent brand directory structure
	if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $tbrandid)) {	
		$ftch = $result->fetch_array();
		$bname = $ftch[0];
			
	} else {
		echo "unable to find brand name from brand id " . $tbrandid;
		exit;
	}
	
	if ($result = $mysqli->query("SELECT title_name FROM titles WHERE title_id=" . $ttitleid)) {	
		$ftch = $result->fetch_array();
		$tname = $ftch[0];
			
	} else {
		echo "unable to find title name from title id " . $titleid;
		exit;
	}
	
	
	//Now create new language in database, create directories in publications using language code from global default

	
	//Now need to get sort order list for all languages associated with this title and initially set to last in the list
	if ($result = $mysqli->query("SELECT sort_order FROM languages WHERE title_id=" . $ttitleid)) {
		$sortorderlast = 0;
		
		while($tsort = $result->fetch_array()) {
			if ($tsort[0]>$sortorderlast) {
				$sortorderlast = $tsort[0];
			}
		}
		$tsortord = $sortorderlast+1;
		
	} else {
		echo "Couldn't retrive sort orders for languages associated with title id " . $ttitleid;
		exit;
	}
	
	$qry = "INSERT INTO languages(title_id,language,sort_order) VALUES('" . $ttitleid . "','Untitled" . $dirsuff . "','" . $tsortord . "')";
	//echo $qry;
	if ($result = $mysqli->query($qry)) {

		if (file_exists(builddirref_sel([$bname,$tname,$GLOBALS['pubspath'],"new"],1,0))) {
			
		} else {
			$tnewdir = builddirref_add([$bname,$tname,$GLOBALS['pubspath'],"new"],1,0);
		}
		
		
		$_SESSION['brandid'] = $tbrandid;
		$_SESSION['titleid'] = $ttitleid;
		$mysqli->close();
		gotourl('/pubseditorhtml/editor-langs.php');
			
	} else {
		echo "couldn't add Untitled" . $dirsuff . " to languages table";
		$mysqli->close();
	}
		
} elseif (isset($_POST['duplang'])) {

	if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $tbrandid)) {	
		$ftch = $result->fetch_array();
		$bname = $ftch[0];
			
	} else {
		echo "unable to find brand name from brand id " . $tbrandid;
		exit;
	}
	
	if ($result = $mysqli->query("SELECT title_name FROM titles WHERE title_id=" . $ttitleid)) {	
		$ftch = $result->fetch_array();
		$tname = $ftch[0];
			
	} else {
		echo "unable to find title name from title id " . $titleid;
		exit;
	}
	
	
	//Now create new language in database, create directories in publications using language code from global default

	
	//Now need to get sort order list for all languages associated with this title and initially set to last in the list
	if ($result = $mysqli->query("SELECT sort_order FROM languages WHERE title_id=" . $ttitleid)) {
		$sortorderlast = 0;
		
		while($tsort = $result->fetch_array()) {
			if ($tsort[0]>$sortorderlast) {
				$sortorderlast = $tsort[0];
			}
		}
		$tsortord = $sortorderlast+1;
		
	} else {
		echo "Couldn't retrieve sort orders for languages associated with title id " . $ttitleid;
		exit;
	}
	
	$qry = "INSERT INTO languages(title_id,language,sort_order) VALUES('" . $ttitleid . "','Untitled" . $dirsuff . "','" . $tsortord . "')";
	//echo $qry;
	if ($result = $mysqli->query($qry)) {

		if (file_exists(builddirref_sel([$bname,$tname,$GLOBALS['pubspath'],"new"],1,0))) {
			
		} else {
			$tnewdir = builddirref_add([$bname,$tname,$GLOBALS['pubspath'],"new"],1,0);
		}
		
		
		//Now need to follow down section tree and make duplicates referencing back to new language
		//first find new language_id
		$newlangid = $mysqli->insert_id;
		echo $tlangid;
		echo $newlangid;
		$sgrouparr = [];
		$tsg = new sectionGroup($tlangid,$mysqli,1,$newlangid);
		array_push($sgrouparr,$tsg);
		$tsg->exploresecs();
		
		
		$_SESSION['brandid'] = $tbrandid;
		$_SESSION['titleid'] = $ttitleid;
		$mysqli->close();
		echo "hello";
		gotourl('/pubseditorhtml/editor-langs.php');
		echo "bye";
			
	} else {
		echo "couldn't add Untitled" . $dirsuff . " to languages table";
		$mysqli->close();
	}
		
} else {
	echo "looks like you shouldn't be here";
	$mysqli->close();
}


?>