<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

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


$titleid = $_POST['expxmltid'];
$brandid = $_POST['expxmlbid'];

if ($result = $mysqli->query("SELECT * FROM languages WHERE title_id='" . $titleid . "'")) {
	
	$row_cnt = $result->num_rows;
	if ($row_cnt>0) {
		
		$xml = new SimpleXMLElementExtended('<?xml version="1.0" encoding="utf-8"?><languages></languages>');
		
		while ($trow = $result->fetch_array()) {
			
			$tlangxml = $xml->addChildWithCDATA('language',$trow["terms_content"]);
			$tlangxml->addAttribute("name", $trow["language"]);
			$tlangxml->addAttribute("file", $trow["xmlFile"]);
			$tlangxml->addAttribute("terms", $trow["termsBtn"]);
			$tlangxml->addAttribute("cont", $trow["continueBtn"]);
			$tlangxml->addAttribute("return", $trow["returnBtn"]);
			$tlangxml->addAttribute("quit", $trow["exitBtn"]);
		}
			
		//Need to find brand_name and title_name for directory structure
		if ($bresult = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id='" . $brandid . "'")) {
			$bname = $bresult->fetch_array();
		} else {
			echo "couldn't obtain brand_name";
		}
		if ($tresult = $mysqli->query("SELECT title_name FROM titles WHERE title_id='" . $titleid . "'")) {
			$tname = $tresult->fetch_array();
		} else {
			echo "couldn't obtain title_name";
		}
		
		//echo "bname " . $bname[0] . "////";
		//echo "tname " . $tname[0] . "////";
		
		$targxmldir = builddirref_sel([$bname[0],$tname[0],$GLOBALS['xmldir'][0],$GLOBALS['xmldir'][1]],1,0);
		if (file_exists($targxmldir)) {
			$nowprint = $xml->asXML($targxmldir . "/languages.xml");
		} else {
			$targxmldir = builddirref_add([$bname[0],$tname[0],$GLOBALS['xmldir'][0],$GLOBALS['xmldir'][1]],1,0);
			$nowprint = $xml->asXML($targxmldir . "/languages.xml");
		}				
		
		$_SESSION['brandid'] = $brandid;
		$_SESSION['titleid'] = $titleid;
		gotourl('/pubseditorhtml/editor-langs.php');
		
	} else {
		echo "No languages associated with this title";
		exit;
	}
			
			
} else {
		
	$mysqli->close();	
	echo "error: could't get languages data from table for " . $titleid;
	exit;
}

?>
