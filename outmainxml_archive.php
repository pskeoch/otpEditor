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


//Get chosen title identifying attributes from POST
$langtitlid = $_POST['langtitlid'];

$varlist = ["title_id",
			"language"];
$qry = mysqlselect_encconv($mysqli,$varlist,"utf8","languages","language_id",$langtitlid); //mysqlselect_encconv($tmysql,$varlist,$enctype,$table,$condvar,$cond,$ord)

$tlangrow = xmlbuild_mysqlsel_one($mysqli,$qry,[$langtitlid,"language_id"]);
$thisttlid = $tlangrow["title_id"];
$xml = new SimpleXMLElementExtended('<?xml version="1.0" encoding="utf-8"?><BrandInterface></BrandInterface>');
$xml->addAttribute('language',$tlangrow["language"]);

//Add title xml section (from titles table cross-referencing from title id in language table)
///////////////////////////////////////////////////////////////////////////
$varlist = ["leftNavTitle1","leftNavTitle2","leftNavTitle3","leftNavTitle4"];
$qry = mysqlselect_encconv($mysqli,$varlist,"utf8","titles","title_id",$thisttlid);

$thisttlrow = xmlbuild_mysqlsel_one($mysqli,$qry,[$thisttlid,"title_id"]);
//https://stackoverflow.com/questions/6260224/how-to-write-cdata-using-simplexmlelement
	$ttlxml = $xml->addChild('title');
	$ttlxml->addChildWithCDATA('row1', $thisttlrow[0]);
	$ttlxml->addChildWithCDATA('row2', $thisttlrow[1]);
	$ttlxml->addChildWithCDATA('row3', $thisttlrow[2]);
	$ttlxml->addChildWithCDATA('row4', $thisttlrow[3]);
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add main_nav xml section (from sections table)
///////////////////////////////////////////////////////////////////////////
//Find all sections associated with this language id in sections table
$varlist = [];
$qry = mysqlselect_encconv($mysqli,$varlist,"utf8","titles","title_id",$thisttlid);
if ($result = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $langtitlid . "' ORDER BY sort_order")) {
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



//str_replace array for special characters
$specchars = array(chr(189),"dummydummydummy");
$repchars = array("&‌#189;","dummydummydummy");

function replace_specchars($tstr) {
	
	$tarr = str_split($tstr);
	foreach ($tarr as $key => $char) {
		if (ord($char)==189) {
			$tarr[$key] = "&‌#189;";
		}
	}
	$namfix = implode($tarr);
	return $namfix;
	
}



if ($result = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $langtitlid . "' ORDER BY sort_order")) {
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
			
				//add links
				$linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
				
				
				if ($lresult = $mysqli->query("SELECT * from links WHERE parent_section_id ='" . $tmainsec["section_id"] . "' ORDER BY sort_order")) {
					$lrow_cnt = $lresult->num_rows;
					
					if ($lrow_cnt>0) {
						$tlinks = $tsecchild->addChild('links');
						while ($tlinkfet = $lresult->fetch_assoc()) {
							$tlink = $tlinks->addChild('link');
							$tlink->addAttribute("kind",$linkkindarr[$tlinkfet["kind"]]);
							$tlink->addAttribute("location",$tlinkfet["location"]);
							$tlink->addChildWithCDATA('name',replace_specchars($tlinkfet["name"]));
						}
					}
				}
			
			
			//Check for wallpaper content pane type (4 = "W") and add wallpaper tags if needed
			if ($tmainsec["pane_type"] == 4) {
				
				if ($wgrpresult = $mysqli->query("SELECT * from wallpaper_groups WHERE parent_section_id ='" . $tmainsec["section_id"] . "' ORDER BY sort_order")) {
					$wrow_cnt = $wgrpresult->num_rows;
					
					if ($wrow_cnt>0) {						
						$twpsec = $tsecchild->addChild('wallpaper_list');
						
						while ($twpgrpfet = $wgrpresult->fetch_assoc()) {
							
							$twpgrp = $twpsec->addChild('wallpaper');
							$twpgrp->addAttribute("thumb", "assets/pics/wallpapers/" . $twpgrpfet["name"] . "_thumb.jpg");
							
							if ($wpresult = $mysqli->query("SELECT * from wallpapers WHERE wallpaper_group_id ='" . $twpgrpfet["wallpaper_group_id"] . "' ORDER BY sort_order")) {
								$wprow_cnt = $wpresult->num_rows;
								if ($wprow_cnt>0) {
									
									while ($twpsizefet = $wpresult->fetch_assoc()) {
										//e.g. <screensize_btn file="assets/pics/wallpapers/wp1_800x600.jpg"><![CDATA[800 x 600]]></screensize_btn>
										$twpsize = $twpgrp->addChildWithCDATA('screensize_btn', $twpsizefet["size"]);
										$twpsize->addAttribute("file", "assets/pics/wallpapers/" . $twpgrpfet["name"] . "_" . $twpsizefet["size"] . ".jpg");
									}
									
								} else {
									echo "no wallpaper count found associated with wallpaper group" . $twpgrpfet["wallpaper_group_id"];
								}
								
							} else {
								echo "no wallpapers found associated with wallpaper group" . $twpgrpfet["wallpaper_group_id"];
							}
							
						}
						
					} else {
						echo "no count of wallpapers found for content pane marked 'W' in section" . $tmainsec["section_id"];
					}
					
					
				} else {
					echo "no wallpapers found for content pane marked 'W' in section" . $tmainsec["section_id"];
				}
				
			}
			
			//Check for content pane = "Q" - need to add quit button tag
			if ($tmainsec["pane_type"] == 5) {
				
				$tsecchild->addChildWithCDATA("quit_button", $tmainsec["name"]);
				
			}
			
			
			///////Next iteration
			/////////////////////
			if ($result2 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tmainsec["section_id"] . "' ORDER BY sort_order")) {
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
							
							//add links
							$linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
							
							if ($lresult2 = $mysqli->query("SELECT * from links WHERE parent_section_id ='" . $tsec2["section_id"] . "' ORDER BY sort_order")) {
								$lrow_cnt2 = $lresult2->num_rows;
								
								if ($lrow_cnt2>0) {
									$tlinks2 = $tsecchild2->addChild('links');
									while ($tlinkfet = $lresult2->fetch_assoc()) {
										$tlink = $tlinks2->addChild('link');
										$tlink->addAttribute("kind",$linkkindarr[$tlinkfet["kind"]]);
										$tlink->addAttribute("location",$tlinkfet["location"]);
										$tlink->addChildWithCDATA('name',replace_specchars($tlinkfet["name"]));
									}
								}
							}	

						
						
						
						///////Next iteration
						///////////////////////
						if ($result3 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tsec2["section_id"] . "' ORDER BY sort_order")) {
							$row_cnt = $result3->num_rows;
							//var_dump($row_cnt);
						
							if ($row_cnt>0) { //if child sections exist then create new section_group
								$tsec3grp = $tsecchild2->addChild('section_group');
								$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
								
								while ($tsec3 = $result3->fetch_assoc()) {										
									
									if ($tsec2["name"] == "Service Publications" ){
										$tsecchild3 = $tsec3grp->addChild('section');
										$tsecchild3->addAttribute("contentPaneType",$contpanelkup[$tsec3["pane_type"]]);
										
										$tsecchild3->addChildWithCDATA('name', replace_specchars($tsec3["name"]));
										$tsecchild3->addChildWithCDATA('copy', "filler copy for now, need to fix");
										
										
									} elseif ($tsec2["name"] == "Owners' Literature" ){
										$tsecchild3 = $tsec3grp->addChild('section');
										$tsecchild3->addAttribute("contentPaneType",$contpanelkup[$tsec3["pane_type"]]);
										
										$tsecchild3->addChildWithCDATA('name', replace_specchars($tsec3["name"]));
										$tsecchild3->addChildWithCDATA('copy', "filler copy for now, need to fix");
										
										
									} else {
										$tsecchild3 = $tsec3grp->addChild('section');
										$tsecchild3->addAttribute("contentPaneType",$contpanelkup[$tsec3["pane_type"]]);
										
										$tsecchild3->addChildWithCDATA('name', replace_specchars($tsec3["name"]));
										$tsecchild3->addChildWithCDATA('copy', $tsec3["copy"]);
									}
									
										//echo $tsec3["section_id"] . " = ";
										
										//add links
										$linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
										
										if ($lresult3 = $mysqli->query("SELECT * from links WHERE parent_section_id ='" . $tsec3["section_id"] . "' ORDER BY sort_order")) {
											$lrow_cnt3 = $lresult3->num_rows;
											//echo $lrow_cnt3 . "<br><br>";
											
											if ($lrow_cnt3>0) {
												$tlinks3 = $tsecchild3->addChild('links');
												while ($tlinkfet = $lresult3->fetch_assoc()) {
													$tlink = $tlinks3->addChild('link');
													$tlink->addAttribute("kind",$linkkindarr[$tlinkfet["kind"]]);
													$tlink->addAttribute("location",$tlinkfet["location"]);
													$tlink->addChildWithCDATA('name',replace_specchars($tlinkfet["name"]));
												}
											}
										}
										
									
									///////Next iteration
									///////////////////////
									if ($result4 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tsec3["section_id"] . "' ORDER BY sort_order")) {
										$row_cnt = $result4->num_rows;
										//var_dump($row_cnt);
									
										if ($row_cnt>0) { //if child sections exist then create new section_group
											$tsec4grp = $tsecchild3->addChild('section_group');
											$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
											
											while ($tsec4 = $result4->fetch_assoc()) {
												//var_dump($tsec);
												if ($tsec3["name"] == "Service Publications" ){
													$tsecchild4 = $tsec4grp->addChild('section');
													$tsecchild4->addAttribute("contentPaneType",$contpanelkup[$tsec4["pane_type"]]);
													$tsecchild4->addChildWithCDATA('name', replace_specchars($tsec4["name"]));
													$tsecchild4->addChildWithCDATA('copy', "filler copy for now, need to fix");
												} elseif ($tsec3["name"] == "Owners' Literature" ){
													$tsecchild4 = $tsec3grp->addChild('section');
													$tsecchild4->addAttribute("contentPaneType",$contpanelkup[$tsec4["pane_type"]]);
													$tsecchild4->addChildWithCDATA('name', replace_specchars($tsec4["name"]));
													$tsecchild4->addChildWithCDATA('copy', "filler copy for now, need to fix");	
													
												} else {
													$tsecchild4 = $tsec4grp->addChild('section');
													$tsecchild4->addAttribute("contentPaneType",$contpanelkup[$tsec4["pane_type"]]);
													$tsecchild4->addChildWithCDATA('name', replace_specchars($tsec4["name"]));
													$tsecchild4->addChildWithCDATA('copy', $tsec4["copy"]);
												}
													
													//echo $tsec4["section_id"] . " = ";
													
													//add links
													$linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
													
													if ($lresult4 = $mysqli->query("SELECT * from links WHERE parent_section_id ='" . $tsec4["section_id"] . "' ORDER BY sort_order")) {
														$lrow_cnt4 = $lresult4->num_rows;
														//echo $lrow_cnt4 . "<br><br>";
											
														if ($lrow_cnt4>0) {
															$tlinks4 = $tsecchild4->addChild('links');
															while ($tlinkfet = $lresult4->fetch_assoc()) {
																$tlink = $tlinks4->addChild('link');
																$tlink->addAttribute("kind",$linkkindarr[$tlinkfet["kind"]]);
																$tlink->addAttribute("location",$tlinkfet["location"]);
																$tlink->addChildWithCDATA('name',replace_specchars($tlinkfet["name"]));
															}
														}
													}
													
													
												
												
												///////Next iteration
												///////////////////////
												if ($result5 = $mysqli->query("SELECT * from sections WHERE parent_id ='" . $tsec4["section_id"] . "' ORDER BY sort_order")) {
													$row_cnt = $result5->num_rows;
													//var_dump($row_cnt);
												
													if ($row_cnt>0) { //if child sections exist then create new section_group
														$tsec5grp = $tsecchild4->addChild('section_group');
														$contpanelkup = array("0"=>"A","4"=>"W","5"=>"Q","6"=>"E");
														
														while ($tsec5 = $result5->fetch_assoc()) {
															//var_dump($tsec);
															if ($tsec4["name"] == "Service Publications" ){
																$tsecchild5 = $tsec5grp->addChild('section');
																$tsecchild5->addAttribute("contentPaneType",$contpanelkup[$tsec5["pane_type"]]);
																$tsecchild5->addChildWithCDATA('name', replace_specchars($tsec5["name"]));
																$tsecchild5->addChildWithCDATA('copy', "filler copy for now, need to fix");
															} elseif ($tsec4["name"] == "Owners' Literature" ){
																$tsecchild5 = $tsec4grp->addChild('section');
																$tsecchild5->addAttribute("contentPaneType",$contpanelkup[$tsec5["pane_type"]]);
																$tsecchild5->addChildWithCDATA('name', replace_specchars($tsec5["name"]));
																$tsecchild5->addChildWithCDATA('copy', "filler copy for now, need to fix");	
																
															} else {
																$tsecchild5 = $tsec5grp->addChild('section');
																$tsecchild5->addAttribute("contentPaneType",$contpanelkup[$tsec5["pane_type"]]);
																$tsecchild5->addChildWithCDATA('name', replace_specchars($tsec5["name"]));
																$tsecchild5->addChildWithCDATA('copy', $tsec5["copy"]);
															}	
															
																//add links
																$linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
																
																if ($lresult5 = $mysqli->query("SELECT * from links WHERE parent_section_id ='" . $tsec5["section_id"] . "' ORDER BY sort_order")) {
																	$lrow_cnt5 = $lresult5->num_rows;
																	
																	if ($lrow_cnt5>0) {
																		$tlinks5 = $tsecchild5->addChild('links');
																		while ($tlinkfet = $lresult5->fetch_assoc()) {
																			$tlink = $tlinks5->addChild('link');
																			$tlink->addAttribute("kind",$linkkindarr[$tlinkfet["kind"]]);
																			$tlink->addAttribute("location",$tlinkfet["location"]);
																			$tlink->addChildWithCDATA('name',replace_specchars($tlinkfet["name"]));
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
					
				}
			}
		}
		
	}
}


//Now need to find correct directory
//first find parent title id, while also getting the xml file name
$qry = "SELECT title_id,xmlFile FROM languages WHERE language_id='" . $langtitlid . "'";
if ($result = $mysqli->query($qry)) {
	
	$trow = $result->fetch_array();
	$ttitleid = $trow[0];
	$txmlfile = $trow[1];
		
} else {
	echo "couldn't obtain title_id from language";
}
$qry = "SELECT title_name,brand_id FROM titles WHERE title_id='" . $ttitleid . "'";
if ($result = $mysqli->query($qry)) {
	
	$trow = $result->fetch_array();
	$ttitlename = $trow[0];
	$tbrandid = $trow[1];		
} else {
	echo "couldn't obtain brand_id from title";
}
$qry = "SELECT brand_name FROM brands WHERE brand_id='" . $tbrandid . "'";
if ($result = $mysqli->query($qry)) {
	
	$trow = $result->fetch_array();
	$tbrandname = $trow[0];	
} else {
	echo "couldn't obtain brand_name from brand";
}

$mysqli->close();

$targxmldir = builddirref_sel([$tbrandname,$ttitlename,$GLOBALS['xmldir']],1);
if (file_exists($targxmldir)) {
	$nowprint = $xml->asXML($targxmldir . "/" . $txmlfile);
} else {
	$targxmldir = builddirref_add([$tbrandname,$ttitlename,$GLOBALS['xmldir']],1);
	$nowprint = $xml->asXML($targxmldir . "/" . $txmlfile);
}


//Header('Content-type: text/xml');
$xml->asXML($thisttlrow[2] . ".xml");
	//NEED TO CHECK AGAINST parent_is_language_id column to avoid accidental pick up of unassociated child
	//NEED TO SORT TO CORRECT ORDER - HOW?

	$_SESSION['lang'] = $langtitlid;
	$_SESSION['parsecid'] = $_POST['expparid'];
	$_SESSION['tsec'] = $_POST['exptid'];
	$_SESSION['selsecid'] = $_POST['expsid'];
	
	gotourl('/pubseditorhtml/editor-sections.php');

?>
