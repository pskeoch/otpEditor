<?php

session_start();

Class SimpleXMLElementExtended extends SimpleXMLElement {
  /**
   * Adds a child with $value inside CDATA
   * @param unknown $name
   * @param unknown $value
   */
  public function addChildWithCDATA($name, $value = NULL) {
    $new_child = $this->addChild($name);
    if ($new_child !== NULL) {
      $node = dom_import_simplexml($new_child);
      $no   = $node->ownerDocument;
      $node->appendChild($no->createCDATASection($value));
    }
    return $new_child;
  }
}

include 'otpedfuncs.php';
$mysqli = openmysql();

$brandid = $_POST['uplbid'];
if (isset($_POST['upltid'])) {
	$titleid = $_POST['upltid'];
}
if (isset($_POST['uplcid'])) {
	$titleid = $_POST['uplcid'];
}

if (isset($_POST['upltname'])) {
	$tname = $_POST['upltname'];
}
if (isset($_POST['uplcname'])) {
	$tname = $_POST['uplcname'];
}



function export_bgxml($filelist,$bname,$tname) {
	
	//need to detect background image name format and check/organise in pairs
	$bglist = array();
	
	//first find all bg types files and put in assoc array
	foreach($filelist as $fname) {
		$formst = substr($fname, 0,3);
		$formend = substr($fname, -4,4);
		if ($formst == "bg_" && $formend == ".jpg") {
			//check if thumb or bg
			if (substr($fname,-10)=="_thumb.jpg") {
				$tkey = "thumb";
				$tf = $fname;
			} else {
				$tkey = "bg";
				$tf = $fname;
			}
			//now need to isolate the bg number and put in assoc array
			$fnamecut = str_replace("bg_","",$fname);
			$fnamecut = str_replace("_thumb","",$fnamecut);
			$fnamecut = str_replace(".jpg","",$fnamecut);
			$bglist[$fnamecut][$tkey] = $tf;
		}
	}
	
	
	$xml = new SimpleXMLElementExtended('<gallery></gallery>');
	foreach($bglist as $bgnum=>$bgpair) {
		if (count($bgpair)==2) {
			$tbg = $xml->addChild('image');
			$tbg->addAttribute("img","bg_" . $bgnum);
		} else {
			echo "bg_" . $bgnum . " does not appear to be paired";
			exit;
		}
	}
	
	$targxmldir = builddirref_sel([$bname,$tname,$GLOBALS['xmldir'][0],$GLOBALS['xmldir'][1]],1,1);
	#echo $targxmldir;
	if (file_exists($targxmldir)) {
		$nowprint = $xml->asXML($targxmldir . "/bg_images.xml");
	} else {
		$targxmldir = builddirref_add([$bname,$tname,$GLOBALS['xmldir'][0],$GLOBALS['xmldir'][1]],1,1);
		$nowprint = $xml->asXML($targxmldir . "/bg_images.xml");
	}
	
	
}
//find corresponding brand_name for directory
if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $brandid)) {	
	$ftch = $result->fetch_array();
	$bname = $ftch[0];
			
} else {
	echo "unable to find brand name from brand id " . $brandid;
	exit;
}

$target_titledir = builddirref_sel([$bname,$tname],1,1);
$target_picsdir = builddirref_sel([$bname,$tname,$GLOBALS['picsdir'][0],$GLOBALS['picsdir'][1]],1,1);
//check if target assets/pics directory already exists for this title
if (file_exists($target_picsdir)) {
	
} else {
	$target_picsdir = builddirref_add([$bname,$tname,$GLOBALS['picsdir'][0],$GLOBALS['picsdir'][1]],1,1);
}

$i = 0;
while($i<count($_FILES["files"]["name"])) {
	
	$target_file = $target_picsdir . "/" . basename($_FILES["files"]["name"][$i]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["files"]["tmp_name"][$i]);
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
	  if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $target_file)) {
		//echo "The file ". basename( $_FILES["files"]["name"][$i]). " has been uploaded.";
	  } else {
		echo "Sorry, there was an error uploading your file.";
	  }
	}
	
	$i++;
}

//also need to export backgrounds xml file, do this here as needs to be part of same process
$makebgxml = export_bgxml($_FILES["files"]["name"],$bname,$tname);

//Now send back to editor-titles.php with brand id, maybe need to set up title selector system
$_SESSION['brandid'] = $brandid;
gotourl('/pubseditorhtml/editor-titles.php');

?>