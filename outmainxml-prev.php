<?php
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
#Get chosen title identifying attributes from POST
$langtitlid = $_POST['langtitlid'];
$username = 'otpubsco_usr';
$pasz = 'Jugg3rn4utM';
$database = 'otpubsco_testdb';
$mysqli = new mysqli('localhost',$username,$pasz,$database);
if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    echo "Error: " . $mysqli->connect_error . "\n";
	exit;
}
//echo "SELECT * from languages WHERE language_id ='" . $langtitlid . "'";
if ($result = $mysqli->query("SELECT * from languages WHERE language_id ='" . $langtitlid . "'")) {
	$row_cnt = $result->num_rows;
	//echo $row_cnt;
	//should only be one row because language_id should be unique, but check just in case
	if ($row_cnt = 1) {
		$thislangidrow = $result->fetch_row();
		$thisttl = $thislangidrow[1];
		//echo "<br>" . $thisttl;
		$xml = new SimpleXMLElementExtended('<BrandInterface></BrandInterface>');
		$xml->addAttribute('language',$thislangidrow[2]);
	} else {
		echo "duplicate entries found for this language_id";
		exit;
	}
} else {	
	$mysqli->close();	
	echo "error: couldn't find language_id in database";
	exit;
}
//Add title xml section (from titles table cross-referencing from title id in language table)
///////////////////////////////////////////////////////////////////////////
if ($result = $mysqli->query("SELECT * from titles WHERE title_id ='" . $thislangidrow[1] . "'")) {
//https://stackoverflow.com/questions/6260224/how-to-write-cdata-using-simplexmlelement
	$thisttlrow = $result->fetch_row();
	$ttlxml = $xml->addChild('title');
	$ttlxml->addChildWithCDATA('row1', $thisttlrow[3]);
	$ttlxml->addChildWithCDATA('row2', $thisttlrow[4]);
	$ttlxml->addChildWithCDATA('row3', $thisttlrow[5]);
	$ttlxml->addChildWithCDATA('row4', $thisttlrow[6]);
} else {
	$mysqli->close();	
	echo "error: couldn't find title_id in titles table";
	exit;
}
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add main_nav xml section (from sections table)
///////////////////////////////////////////////////////////////////////////
//Find all sections associated with this language id in sections table
if ($result = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $langtitlid . "'")) {
	$secmainarr = []; //make associative array to store section refs for use in later processes in xml building
	$secmainids = [];
	$row_cnt = $result->num_rows;
	//NEED TO CHECK AGAINST parent_is_language_id column to avoid accidental pick up of unassociated child
	//NEED TO SORT TO CORRECT ORDER - HOW?
	//$ttlxml->addChild('SectionCount',$row_cnt);
	$mainnavxml = $xml->addChild('main_nav');
	for ($i=0; $i<$row_cnt; $i++) {
		$thissecrow = $result->fetch_row();
		$mainnavxml->addChildWithCDATA('nav', $thissecrow[3]);
		$secmainarr[$thissecrow[0]] = $thissecrow;
		array_push($secmainids,$thissecrow[0]);
	}
} else {
	$mysqli->close();
	echo "error: couldn't find language_id in database";
	exit;
}

///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add gallery nav element (from languages table, using language id row already obtained)
///////////////////////////////////////////////////////////////////////////
$thisgallref = $thislangidrow[9];
$thisgallnoteref = $thislangidrow[10];
$galleryxml = $xml->addChild('gallery');
$galleryxml->addChildWithCDATA('title', $thisgallref);
$galleryxml->addChildWithCDATA('note', $thisgallnoteref);
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add Return button nav element (from languages table, using language id row already obtained)
///////////////////////////////////////////////////////////////////////////
$thisretbut = $thislangidrow[6];
$returnbutxml = $xml->addChild('back_button');
$returnbutxml->addChildWithCDATA('label', $thisretbut);
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

/*
//Build main section group pages (using $secgrouparr and $secgroupids setup earlier)
//Need cross references to links table using section ids
///////////////////////////////////////////////////////////////////////////
//Build structure:
//<name>
//<copy>
//child sections<section_group><section>
//<links>
//DON'T UNDERSTAND WHAT SECTION_GROUP REFERS TO. MAY NEED TO TRIAL AND ERROR TO FIGURE OUT
//Ok section_group refers to hierarchy of sections. The main menu items are all sections in one section group and then...
//in some of those sections there are a further layer of sections, which will be in another section group.
$mainsecsxmlarr = [];
$secarr = [];
$secno = 0;
$secmainidsslice = array_slice($secmainids,0,3) + array_slice($secmainids,4,5);
foreach ($secmainidsslice as $secid) {
	$mainsecxmlarr[$secid] = $xml->addChild('section_group');
	//get content pane type letter from number in table - need quick lookup
	$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
	$tsecchild = $secgrxmlarr[$secid]->addChild('section');
	$tsecchild->addAttribute("contentPaneType",$contpanelkup[$secgrouparr[$secid][4]]);
	$tsecchild->addChildWithCDATA('name', $secgrouparr[$secid][3]);
	$tsecchild->addChildWithCDATA('copy', $secgrouparr[$secid][5]);
}
Header('Content-type: text/xml');
print($xml->asXML());*/

//1. Get seed section id = language id
//2. Check sections TABLE for sections which have this as parent_id
//3. If sections exist define a new section_group, then start writing out sections
//4. For each section now need to check sections TABLE for those having this section as parent id
//5. If such sections exist define new section_group...
//6. Iterate through until nothing left to check
if ($result = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $langtitlid . "'")) {
	$row_cnt = $result->num_rows;
	//var_dump($row_cnt);
	
	if ($row_cnt>0) { //if child sections exist then create new section_group
		$mainsecgrp = $xml->addChild('section_group');
		$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
		
		while ($tmainsec = $result->fetch_assoc()) {
			//var_dump($tsec);
			if ($tmainsec["name"] == "Jaguar Heritage" ){
				$tsecchild = $mainsecgrp->addChild('section');
				$tsecchild->addAttribute("contentPaneType",$contpanelkup[$tmainsec["pane_type"]]);
				$tsecchild->addChildWithCDATA('name', $tmainsec["name"]);
				$tsecchild->addChildWithCDATA('copy', "filler copy for now, need to fix");
			} else {
				$tsecchild = $mainsecgrp->addChild('section');
				$tsecchild->addAttribute("contentPaneType",$contpanelkup[$tmainsec["pane_type"]]);
				$tsecchild->addChildWithCDATA('name', $tmainsec["name"]);
				$tsecchild->addChildWithCDATA('copy', $tmainsec["copy"]);
			}
			
			if ($result2 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tmainsec["section_id"] . "'")) {
				$row_cnt = $result2->num_rows;
				
				if ($row_cnt>0) { //if child sections exist then create new section_group
					$tsec2grp = $tsecchild->addChild('section_group');
					$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
					
					while ($tsec2 = $result2->fetch_assoc()) {
						//var_dump($tsec);
						if ($tsec2["name"] == "Jaguar Heritage" ){
							$tsecchild2 = $tsec2grp->addChild('section');
							$tsecchild2->addAttribute("contentPaneType",$contpanelkup[$tsec2["pane_type"]]);
							$tsecchild2->addChildWithCDATA('name', $tsec2["name"]);
							$tsecchild2->addChildWithCDATA('copy', "filler copy for now, need to fix");
						} else {
							$tsecchild2 = $tsec2grp->addChild('section');
							$tsecchild2->addAttribute("contentPaneType",$contpanelkup[$tsec2["pane_type"]]);
							$tsecchild2->addChildWithCDATA('name', $tsec2["name"]);
							$tsecchild2->addChildWithCDATA('copy', $tsec2["copy"]);
						}
						
						
						if ($result3 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tsec2["section_id"] . "'")) {
							$row_cnt = $result3->num_rows;
							//var_dump($row_cnt);
						
							if ($row_cnt>0) { //if child sections exist then create new section_group
								$tsec3grp = $tsecchild2->addChild('section_group');
								$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
								
								while ($tsec3 = $result3->fetch_assoc()) {
									//var_dump($tsec);
									if ($tsec2["name"] == "Service Publications" ){
										$tsecchild3 = $tsec3grp->addChild('section');
										$tsecchild3->addAttribute("contentPaneType",$contpanelkup[$tsec3["pane_type"]]);
										$tsecchild3->addChildWithCDATA('name', $tsec2["name"]);
										$tsecchild3->addChildWithCDATA('copy', "filler copy for now, need to fix");
									} elseif ($tsec2["name"] == "Owners' Literature" ){
										$tsecchild3 = $tsec3grp->addChild('section');
										$tsecchild3->addAttribute("contentPaneType",$contpanelkup[$tsec3["pane_type"]]);
										$tsecchild3->addChildWithCDATA('name', $tsec2["name"]);
										$tsecchild3->addChildWithCDATA('copy', "filler copy for now, need to fix");	
										
									} else {
										$tsecchild3 = $tsec3grp->addChild('section');
										$tsecchild3->addAttribute("contentPaneType",$contpanelkup[$tsec3["pane_type"]]);
										$tsecchild3->addChildWithCDATA('name', $tsec3["name"]);
										$tsecchild3->addChildWithCDATA('copy', $tsec3["copy"]);
									}
									
									if ($result4 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tsec3["section_id"] . "'")) {
										$row_cnt = $result4->num_rows;
										//var_dump($row_cnt);
									
										if ($row_cnt>0) { //if child sections exist then create new section_group
											$tsec4grp = $tsecchild3->addChild('section_group');
											$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
											
											while ($tsec4 = $result3->fetch_assoc()) {
												//var_dump($tsec);
												if ($tsec3["name"] == "Service Publications" ){
													$tsecchild4 = $tsec4grp->addChild('section');
													$tsecchild4->addAttribute("contentPaneType",$contpanelkup[$tsec4["pane_type"]]);
													$tsecchild4->addChildWithCDATA('name', $tsec3["name"]);
													$tsecchild4->addChildWithCDATA('copy', "filler copy for now, need to fix");
												} elseif ($tsec3["name"] == "Owners' Literature" ){
													$tsecchild4 = $tsec3grp->addChild('section');
													$tsecchild4->addAttribute("contentPaneType",$contpanelkup[$tsec4["pane_type"]]);
													$tsecchild4->addChildWithCDATA('name', $tsec3["name"]);
													$tsecchild4->addChildWithCDATA('copy', "filler copy for now, need to fix");	
													
												} else {
													$tsecchild4 = $tsec3grp->addChild('section');
													$tsecchild4->addAttribute("contentPaneType",$contpanelkup[$tsec4["pane_type"]]);
													$tsecchild4->addChildWithCDATA('name', $tsec4["name"]);
													$tsecchild4->addChildWithCDATA('copy', $tsec4["copy"]);
												}
											}
										}	
									}														
									
									
									
								}
							}	
						}	
						
					}
				}
			}
		}
		
	}
}
$mysqli->close();

Header('Content-type: text/xml');
print($xml->asXML());
	//NEED TO CHECK AGAINST parent_is_language_id column to avoid accidental pick up of unassociated child
	//NEED TO SORT TO CORRECT ORDER - HOW?

?>
