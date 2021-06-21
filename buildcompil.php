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

?>

<!DOCTYPE html>
<html>
<body>

<?php

Class techpubs_compilesections {
	//Properties
	public $tmpltlangid;
	public $tmpltlang3code;
	public $techpubsparid;
	public $mysqli;
	public $titleids_in_compil;
	public $techpubssecs;
	public $xmldat;
	public $langpubloc;
	public $secgroups;
	
	function __construct($mysqli,$tmpltlangid,$lang3code,$xmldat,$langpubloc) {
		$this->tmpltlangid = $tmpltlangid;
		$this->tmpltlang3code = $lang3code;
		#echo "code: " . $this->tmpltlang3code;
		$this->compilid = $compilid;
		$this->mysqli = $mysqli;
		$this->titleids_in_compil = array();
		$this->techpubssecs = array();
		$this->xmldat = $xmldat;
		$this->langpubloc = $langpubloc;
		$this->secgroups = array();
		
		//find titleids for all titles in compilation
		$qry = "SELECT titleid FROM comptitles WHERE compid=" . $_SESSION['compilid'] . " ORDER BY titleid";
		#echo $qry;
		if ($result = $this->mysqli->query($qry)) {
			#echo $result->num_rows;
			
			#now find all languages in each title, check if (any) language matches this run of the tmpltlang3code and then determine the correct section id from relevant langid
			while ($titleinfo = $result->fetch_assoc()) {
				$qry = "SELECT language_id, language, xmlFile FROM languages WHERE title_id=" . $titleinfo["titleid"];
				#echo $qry;
				if ($titleresult = $this->mysqli->query($qry)) {
					while ($langinfo = $titleresult->fetch_assoc()) {
						$tlang3code = substr($langinfo["language"],0,3);
						if ($tlang3code == $this->tmpltlang3code) {
							#echo "langinfo: " . $langinfo["language_id"];
							$this->titleids_in_compil[$titleinfo["titleid"]] = $langinfo["language_id"];
							
							$qry = "SELECT techpubs_secid FROM langid_techpubslookup WHERE langid=" . $langinfo["language_id"];
							if ($techpubsresult = $this->mysqli->query($qry)) {
								$techpubsec = $techpubsresult->fetch_assoc();
								$this->techpubssecs[$langinfo["language_id"]] = $techpubsec["techpubs_secid"];
							} else {
								$mysqli->close();	
								echo "error: couldn't find techpubs in database";
								exit;
							}
						}
					}
				} else {	
					$mysqli->close();	
					echo "error: couldn't find comptitles->titleid in database";
					exit;
				}
			}
			
		} else {	
			$mysqli->close();	
			echo "error: couldn't find comptitles->titleid in database";
			exit;
		}
	}
	
	function write_titlexml() {
		
		#var_dump($this->titleids_in_compil);
		foreach($this->titleids_in_compil as $tcompiltitleid => $tcompillangid) {
			#echo $tcompillangid . "+++";
			
			$tsecxml = $this->xmldat->addChild('section');
			$tsecxml->addAttribute("contentPaneType","A");
			
			$qry = "SELECT title_name, leftNavTitle1, leftNavTitle2, leftNavTitle3, leftNavTitle4 FROM titles WHERE title_id=" . $tcompiltitleid;
			if ($result = $this->mysqli->query($qry)) {
				$ttlnameinfo = $result->fetch_assoc();
				echo $ttlnameinfo["title_name"];
				$tsecxml->addChildWithCDATA('name', "(" . $ttlnameinfo["title_name"] . ") " . $ttlnameinfo["leftNavTitle1"] . " " . $ttlnameinfo["leftNavTitle2"] . " " . $ttlnameinfo["leftNavTitle3"] . " " . $ttlnameinfo["leftNavTitle4"] . " ");
			} else {	
				$mysqli->close();	
				echo "error: couldn't find titles->title_name in database";
				exit;
			}
			#var_dump($this->techpubssecs);
			$tsecxml->addChildWithCDATA('copy', "");
			#echo ": " . $tcompillangid . "|||";
			#echo $this->techpubssecs[$tcompillangid] . "|||" . $tcompillangid . "|||" . $this->lang3code . "|||";
			$tsg = new sectionGroup($this->techpubssecs[$tcompillangid],$tsecxml,$this->langpubloc,$this->mysqli,$this->tmpltlang3code,0,$tcompillangid,$this->tmpltlang3code,$this->tmpltlangid,0);
			array_push($this->secgroups,$tsg);
			$tsg->exploresecs();
			
		}
	}
	
}

Class sectionGroup {
	//Properties
	public $groupid;
	public $hierarchylev;
	public $parentid;
	public $parxml;
	public $sgxmldat;
	public $childgrps;
	public $mysqli;
	public $contpanelkup;
	public $linkkindarr;
	public $langpubloc;
	public $parentislang;
	public $langid;
	public $lang3code;
	public $compilerref;
	public $tmpltlangid;
	public $istemplate;
	
	//Methods
	function __construct($groupid,$parxml,$langpubloc,$mysqli,$tlangpref,$parentislang,$langid,$lang3code,$tmpltlangid,$istemplate) {
		//$parxml->asXML("textxml" . $groupid . ".xml");
		$this->groupid = $groupid;
		$this->sgxmldat = $parxml->addChild('section_group');
		
		//$parxml->asXML("textxml" . $groupid . ".xml");
		
		$this->childgrps = array();
		$this->mysqli = $mysqli;
		$this->contpanelkup = array(0=>"A",4=>"W",5=>"Q",6=>"E");
		$this->linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
		$this->langpubloc = $langpubloc;
		$this->langpref = $tlangpref;
		$this->parentislang = $parentislang;
		$this->langid = $langid;
		$this->lang3code = $lang3code;
		$this->tmpltlangid = $tmpltlangid;
		$this->istemplate = $istemplate;
		#echo $this->groupid;
		
		
	}
	
	function nextsg($tsecarr,$tsecxml) {
		
		if ($result=$this->mysqli->query("SELECT section_id FROM sections WHERE parent_id='" . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"] . "'")) {
			//echo "bye" . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
			if ($result->num_rows>0) {
				$tsg = new sectionGroup($tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],$tsecxml,$this->langpubloc,$this->mysqli,$this->langpref,0,$this->langid,$this->lang3code,$this->tmpltlangid,$this->istemplate);
				array_push($this->childgrps,$tsg);
				$tsg->exploresecs();
			}
		}
	}
	
	
	function wallpaper($tsecarr,$tsecxml) {
		/*//echo "wppane " . $tsecarr['section_id'];
		$varlist = ["wallpaper_group_id",
					"name"];
		$qry = mysqlselect_encconv($varlist,"utf8","wallpaper_groups","parent_section_id",$tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],"sort_order");
		$twpgarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],"parent_section_id"]);
							
		$twpsecxml = $tsecxml->addChild('wallpaper_list');
							
		foreach ($twpgarr as $twpgrp) {
							
			$twpgrpxml = $twpsecxml->addChild('wallpaper');
			$twpgrpxml->addAttribute("thumb", "assets/pics/wallpapers/" . $twpgrp["CONVERT(CAST(name as BINARY) USING utf8)"] . "_thumb.jpg");
			
			$varlist = ["size"];
			$qry = mysqlselect_encconv($varlist,"utf8","wallpapers","wallpaper_group_id",$twpgrp["CONVERT(CAST(wallpaper_group_id as BINARY) USING utf8)"],"sort_order");
			$twparr = sqlbuild_mysqlsel($this->mysqli,$qry,[$twpgrp["CONVERT(CAST(wallpaper_group_id as BINARY) USING utf8)"],"wallpaper_group_id"]);
									
			foreach($twparr as $twp) {
				//e.g. <screensize_btn file="assets/pics/wallpapers/wp1_800x600.jpg"><![CDATA[800 x 600]]></screensize_btn>
				$twpsize = $twpgrpxml->addChildWithCDATA('screensize_btn', $twp["CONVERT(CAST(size as BINARY) USING utf8)"]);
				$twpsize->addAttribute("file", "assets/pics/wallpapers/" . $twpgrp["CONVERT(CAST(name as BINARY) USING utf8)"] . "_" . $twp["CONVERT(CAST(size as BINARY) USING utf8)"] . ".jpg");
			}
		}		*/
		
		//Build compilation wallpaper section by finding titles in compilation, building xml title menu and then writing out individual title wallpaper sections
		
		$twpsecxml = $tsecxml->addChild('section_group');
		
		$qry = "SELECT titleid FROM comptitles WHERE compid=" . $_SESSION['compilid'] . " ORDER BY titleid";
		if ($comptitle_result = $this->mysqli->query($qry)) {
			
			while ($comptitleinfo = $comptitle_result->fetch_assoc()) {
				
				$qry = "SELECT title_name, leftNavTitle1, leftNavTitle2, leftNavTitle3, leftNavTitle4 FROM titles WHERE title_id=" . $comptitleinfo["titleid"];
				if ($title_result = $this->mysqli->query($qry)) {
				
					$titleinfo = $title_result->fetch_assoc();
				
				} else {
					echo "error " . $qry;
				}
				
				$qry = "SELECT wallpaper_sectionid FROM title_wallpaperlookup WHERE title_id=" . $comptitleinfo["titleid"];
				echo $qry;
				if ($wpid_result = $this->mysqli->query($qry)) {
					echo "wpidinfo_count: " . $wpid_result->num_rows . "||";
					$wpidinfo = $wpid_result->fetch_assoc();
					echo "wpidinfo: " . var_dump($wpidinfo) . "||";
						
					
					$twptitlexmlsec = $twpsecxml->addChild('section');
					$twptitlexmlsec->addAttribute("contentPaneType","W");

					$twptitlexmlsec->addChildWithCDATA('name', "(" . $titleinfo["title_name"] . ") " . $titleinfo["leftNavTitle1"] . " " . $titleinfo["leftNavTitle2"] . " " . $titleinfo["leftNavTitle3"] . " " . $titleinfo["leftNavTitle4"] . " ");
					$twptitlexmlsec->addChildWithCDATA('copy', "");
						
					$twptitlexmlsecwall = $twptitlexmlsec->addChild('wallpaper_list');
						
					$varlist = ["wallpaper_group_id",
					"name"];
					$qry = mysqlselect_encconv($varlist,"utf8","wallpaper_groups","parent_section_id",$wpidinfo["wallpaper_sectionid"],NULL);
					$twpgarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$wpidinfo["wallpaper_sectionid"],"parent_section_id"]);
						
					foreach ($twpgarr as $twpgrp) {
						
						$twpgrpxml = $twptitlexmlsecwall->addChild('wallpaper');
						$twpgrpxml->addAttribute("thumb", "assets/pics/wallpapers/" . $titleinfo["title_name"] . "/" . $twpgrp["CONVERT(CAST(name as BINARY) USING utf8)"] . "_thumb.jpg");
		
						$varlist = ["size"];
						$qry = mysqlselect_encconv($varlist,"utf8","wallpapers","wallpaper_group_id",$twpgrp["CONVERT(CAST(wallpaper_group_id as BINARY) USING utf8)"],"sort_order");
						$twparr = sqlbuild_mysqlsel($this->mysqli,$qry,[$twpgrp["CONVERT(CAST(wallpaper_group_id as BINARY) USING utf8)"],"wallpaper_group_id"]);
									
						foreach($twparr as $twp) {
							//e.g. <screensize_btn file="assets/pics/wallpapers/wp1_800x600.jpg"><![CDATA[800 x 600]]></screensize_btn>
							$twpsize = $twpgrpxml->addChildWithCDATA('screensize_btn', $twp["CONVERT(CAST(size as BINARY) USING utf8)"]);
							$twpsize->addAttribute("file", "assets/pics/wallpapers/" . $titleinfo["title_name"] . "/" . $twpgrp["CONVERT(CAST(name as BINARY) USING utf8)"] . "_" . $twp["CONVERT(CAST(size as BINARY) USING utf8)"] . ".jpg");
						}
					}
									
					
				} else {
					echo "error " . $qry;
				}
				
			}
			
		} else {
			echo "error " . $qry;
		}
		
	}
	
	function quitpane($tsecarr,$tsecxml) {
		$tsecxml->addChildWithCDATA("quit_button", $tsecarr["CONVERT(CAST(name as BINARY) USING utf8)"]);
	}
	
	function picgall($tsecarr,$tsecxml) {
		//<external_app file="assets/apps/slideShow.swf"/>
		$slideshowapp = $tsecxml->addChild('external_app');
		$slideshowapp->addAttribute("file","assets/apps/slideShow.swf");
	}
	
	
	function sectypes($tsecarr,$tsecxml) {
		switch ($tsecarr["CONVERT(CAST(pane_type as BINARY) USING utf8)"]) {
			case 4:
				$this->wallpaper($tsecarr,$tsecxml);
				break;
			case 5:
				$this->quitpane($tsecarr,$tsecxml);
				break;
			case 6:
				$this->picgall($tsecarr,$tsecxml);
			default: 
				$this->nextsg($tsecarr,$tsecxml);
		}
	}
	
	function writelinks($tsecarr,$tsecxml) {
		//echo "links " . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
		//add links
		$varlist = ["name",
					"kind",
					"location"];
		//$qry = mysqlselect_encconv($varlist,"utf8","links2","parent_section_id",$tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],"sort_order");
		//if ($tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"]==1037) {
			//echo $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
			$qry = "SELECT name,kind,location FROM links WHERE parent_section_id=" . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"] . " ORDER BY sort_order";
		//	echo $qryop;
		//}
		$tlinksarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],"parent_section_id"]);
		//var_dump($tlinksarr);
		if (count($tlinksarr)>0) {
			//echo $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
			$tlinksxml = $tsecxml->addChild('links');
	
			foreach($tlinksarr as $tlink) {
				$tlinkxml = $tlinksxml->addChild('link');
				$tlinkxml->addAttribute("kind",$this->linkkindarr[$tlink["kind"]]);
				
				switch ($tlink["kind"]) {
					case 0:
						//build correct path for location of file link
						$targlinkdir = builddirref_sel($GLOBALS['pubspath'],1,1);
						$qry = "SELECT title_id FROM languages WHERE language_id=" . $this->langid;
						if ($ttlidresult = $this->mysqli->query($qry)) {
							$ttlid = $ttlidresult->fetch_assoc();
							
							$qry = "SELECT title_name FROM titles WHERE title_id=" . $ttlid["title_id"];
							if ($ttlnamresult = $this->mysqli->query($qry)) {
								$ttlname = $ttlnamresult->fetch_assoc();
								
							} else {
								echo "error finding title_name in titles";
								exit;
							}							
							
						} else {
							echo "error finding title_id in languages";
							exit;
						}
						
						$tlinkxml->addAttribute("location",str_replace(".pdf",".pdc",$targlinkdir . $this->langpubloc . "/" . $ttlname["title_name"] . "/" . $tlink["location"]));
						$tlinkxml->addChildWithCDATA('name',$tlink["name"]);
						break;
					case 1:
						$tlinkxml->addAttribute("location",$tlink["location"]);
						$tlinkxml->addChildWithCDATA('name',$tlink["name"]);
						break;
					case 2:
						$tlinkxml->addAttribute("location",$tlink["location"]);
						$tlinkxml->addChildWithCDATA('name',$tlink["name"]);
						break;
				}
			}
		}
		
		$this->sectypes($tsecarr,$tsecxml);
	}
	
	function stwritesec($tsecarr) {
		
		if ($this->contpanelkup[$tsecarr["CONVERT(CAST(pane_type as BINARY) USING utf8)"]] == "W") {
		
			$tsecxml = $this->sgxmldat->addChild('section');
			$tsecxml->addAttribute("contentPaneType","A");
			$tsecxml->addChildWithCDATA('name', $tsecarr["CONVERT(CAST(name as BINARY) USING utf8)"] . "-");
			$tsecxml->addChildWithCDATA('copy', $tsecarr["CONVERT(CAST(copy as BINARY) USING utf8)"]);
			
			$this->wallpaper($tsecarr,$tsecxml);
			
			
		} else {
		
			$tsecxml = $this->sgxmldat->addChild('section');
			#echo "content pane " . $tsecarr["CONVERT(CAST(pane_type as BINARY) USING utf8)"] . " that was";
			$tsecxml->addAttribute("contentPaneType",$this->contpanelkup[$tsecarr["CONVERT(CAST(pane_type as BINARY) USING utf8)"]]);
			
			$tsecxml->addChildWithCDATA('name', $tsecarr["CONVERT(CAST(name as BINARY) USING utf8)"]);
			$tsecxml->addChildWithCDATA('copy', $tsecarr["CONVERT(CAST(copy as BINARY) USING utf8)"]);
			
			//Get brand name
			#$qry = "SELECT brand_name FROM brands WHERE brand_id=" . $_SESSION["brandid"];
			#$brandname = sqlbuild_mysqlsel_one($this->mysqli,$qry,"brand_name");
				
			//var_dump($tsecarr);
			
			$this->writelinks($tsecarr,$tsecxml);
			
		}
		
	}
	
	function exploresecs() {
	
		//First check if this section id is on the exempt list, don't run anything if so
		$qry = "SELECT compsecexempt FROM compilations WHERE compid=" . $_SESSION["compilid"];
		if ($result = $this->mysqli->query($qry)) {
			$secexemptdat = $result->fetch_assoc();
			$secexemptstr = $secexemptdat["compsecexempt"];
			$secexemptarr = explode(",", $secexemptstr);
			if (in_array($this->groupid,$secexemptarr,$strict=true)) {
				$secexempt = true;
			}
			
		} else {
			$mysqli->close();	
			echo "error: couldn't obtain compilation exemption list";
			exit;
		
		}
		
		if ($secexempt==true) {
			echo $this->groupid . " is exempt";
		} else {
	
		//check if this parent is a tech pubs section - this needs to be handled differently by going through titles in compilation
		if ($this->istemplate == 1) { //if this group is being run as part of the template title then this needs to be checked
			$qry = "SELECT techpubs_secid FROM langid_techpubslookup WHERE langid=" . $this->tmpltlangid;
			if ($result = $this->mysqli->query($qry)) {
				$techpubsinfo = $result->fetch_row();
				$techpubsid = $techpubsinfo[0];
				#echo $techpubsid . "//";
				#echo $this->groupid;
				if ($this->groupid == $techpubsid) {
					#echo "yes" . "<br>";
					//if this is the tech pub section then need to cycle through compilation titles for the equivalent section in each
					#echo $this->lang3code;
					
					$this->compilerref = new techpubs_compilesections($this->mysqli,$this->langid,$this->lang3code,$this->sgxmldat,$this->langpubloc);
					$this->compilerref->write_titlexml();
					
				} else {
				
					#echo "groupy " . $this->groupid;
					$varlist = ["section_id",
								"name",
								"pane_type",
								"copy"];
					#echo "groupid: " . $this->groupid . "|||";
					$qry = mysqlselect_encconv_and($varlist,"utf8","sections",array("parent_id","parent_is_language_id"),array($this->groupid,$this->parentislang),"sort_order");#mysqlselect_encconv($varlist,"utf8","sections","parent_id",$this->groupid,"sort_order");
					$tparsecarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$this->groupid,"parent_id"]);
					#echo $this->groupid;
					
					//write sections and
					//check if any subsections are group parents themselves
					#echo "<p style='margin: 0px 0px 0px " . $this->tmarg . "px'>";
					foreach($tparsecarr as $tparsec) {
						
						#echo $tparsec["CONVERT(CAST(name as BINARY) USING utf8)"];
						#echo "<br>";
						#$this->tmarg += 20;
						$this->stwritesec($tparsec);
					}
					#echo "</p>";
						
				}
			}
		} else { //if istemplate=0 else if not part of template title then run as normal section group
		
			#echo "groupy " . $this->groupid;
			$varlist = ["section_id",
						"name",
						"pane_type",
						"copy"];
			#echo "groupid: " . $this->groupid . "|||";
			$qry = mysqlselect_encconv_and($varlist,"utf8","sections",array("parent_id","parent_is_language_id"),array($this->groupid,$this->parentislang),"sort_order");#mysqlselect_encconv($varlist,"utf8","sections","parent_id",$this->groupid,"sort_order");
			$tparsecarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$this->groupid,"parent_id"]);
			#echo $this->groupid;
					
			//write sections and
			//check if any subsections are group parents themselves
			#echo "<p style='margin: 0px 0px 0px " . $this->tmarg . "px'>";
			foreach($tparsecarr as $tparsec) {
						
				#echo $tparsec["CONVERT(CAST(name as BINARY) USING utf8)"];
				#echo "<br>";
				#$this->tmarg += 20;
				$this->stwritesec($tparsec);
			}
			#echo "</p>";
		
		}
		
		}
		
	}
	
	function outputxml($dirbuildls,$indref,$txmlfile) {

	}
	
	function techpubcompil($tsecarr,$tsecxml) {
		
		//get list of compilation titles from compilation table
		$qry = "SELECT titleid FROM comptitles WHERE compid=" . $_SESSION['compilid'];
		$ttitleslist = sqlbuild_mysqlsel($this->mysqli,$qry,"titleid");
		
		//for each title in compilation now find title name text and add to xml as links
		$tcompsecxml = $tsecxml->addChild('section_group');
		foreach($ttitleslist as $ttitleref) {
			$qry = "SELECT leftNavtitle1,leftNavtitle2,leftNavtitle3 FROM titles WHERE title_id=" . $ttitleref[0];
			$disptitlearr = sqlbuild_mysqlsel_one($this->mysqli,$qry,"title_id");
			$disptitle = $disptitlearr[0] . $disptitlearr[1] . $disptitlearr[2];
			//echo $disptitle;
			$tcompttlxml = $tcompsecxml->addChild('section');
			$tcompttlxml->addAttribute("contentPaneType","A");
			$tcompttlxml->addChildWithCDATA("name",$disptitle);
			$tcompttlxml->addChildWithCDATA("copy","");
			
			//now need to find correct section id for technical publications for this title - need to know language as part of this
			$qry = "SELECT language_id, language FROM languages WHERE title_id=" . $ttitleref[0];
			$ttllanglist = sqlbuild_mysqlsel($this->mysqli,$qry,"language_id");
			foreach ($ttllanglist as $ttllang) {
				if (substr($ttllang[1],0,3)) {
					$tlangid = $ttllang[0];
				}
			}
			
			$qry = "SELECT brand_name FROM brands WHERE brand_id=" . $_SESSION["brandid"];
			$brandname = sqlbuild_mysqlsel_one($this->mysqli,$qry,"brand_name");
			
			$qry = "SELECT section_id FROM `sections` WHERE `parent_id`=" . $tlangid . " AND `name`='" . $brandname[0] . " Technical Publications'";
			//echo $qry;
			$subsec = sqlbuild_mysqlsel_one($this->mysqli,$qry,"section_id");
			
			//echo $subsec;
			//echo $subsec[0];
			
			$tsg = new sectionGroup($subsec[0],$tcompttlxml,$this->langpubloc,$this->mysqli,$this->langpref,$this->tlang3code);
			array_push($this->childgrps,$tsg);
			$tsg->exploresecs();
		}
		
		//var_dump($ttitleslist);
		//exit;
	}

}

Class compillang {

	//Properties
	public $tlanginfo;
	public $tlangname;
	public $tlang3code;
	public $tlangxmlpath;
	public $mysqli;
	public $xmldat;
	public $sgrouparr;

	function __construct($tlang,$mysqli) {
		$this->tlanginfo = $tlang;
		#echo "here";
		#var_dump($this->tlanginfo);
		$this->mysqli = $mysqli;
		$this->comptmpltid;
	}
	
	function startxmlstream() {
		$this->xmldat = new SimpleXMLElementExtended('<?xml version="1.0" encoding="utf-8"?><BrandInterface></BrandInterface>');
		$this->xmldat->addAttribute('language',$this->tlanginfo[1]);
		
		//Add title xml section (from compilation table)
		///////////////////////////////////////////////////////////////////////////
		$varlist = ["leftNavTitle1","leftNavTitle2","leftNavTitle3","leftNavTitle4"];
		$qry = mysqlselect_encconv($varlist,"utf8","compilations","compid",$_SESSION['compilid'],NULL);
		$thisttlrow = sqlbuild_mysqlsel_one($this->mysqli,$qry,[$_SESSION['compilid'],"compid"]);
		//https://stackoverflow.com/questions/6260224/how-to-write-cdata-using-simplexmlelement
		$ttlxml = $this->xmldat->addChild('title');
		$ttlxml->addChildWithCDATA('row1', $thisttlrow[0]);
		$ttlxml->addChildWithCDATA('row2', $thisttlrow[1]);
		$ttlxml->addChildWithCDATA('row3', $thisttlrow[2]);
		$ttlxml->addChildWithCDATA('row4', $thisttlrow[3]);
		////////////////////////////////////////////////////////////////////////////
		//Add main_nav xml section (from sections table)
		///////////////////////////////////////////////////////////////////////////
		//Find all sections associated with this language id in sections table
		$varlist = ["name"];
		$qry = mysqlselect_encconv_and($varlist,"utf8","sections",array("parent_id","parent_is_language_id"),array($this->tlanginfo[0],1),"sort_order");
		$tparsecarr = sqlbuild_mysqlsel($this->mysqli,$qry,array($this->tlanginfo[0],"parent_id"));
			//NEED TO CHECK AGAINST parent_is_language_id column to avoid accidental pick up of unassociated child
			//NEED TO SORT TO CORRECT ORDER - HOW?
			//$ttlxml->addChild('SectionCount',$row_cnt);
		$mainnavxml = $this->xmldat->addChild('main_nav');
		foreach($tparsecarr as $tparsec) {
			#echo $tparsec[0];
			$mainnavxml->addChildWithCDATA('nav', $tparsec[0]);
		}
		///////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////
		#$varlist2 = ["termsBtn","continueBtn","returnBtn","exitBtn","terms_content","gallery","gallery_note"];
		#$qry = mysqlselect_encconv($varlist2,"utf8","languages","language_id",$this->tlanginfo[0],NULL);
		#echo $qry;
		#$compnavinfo = sqlbuild_mysqlsel_one($this->mysqli,$qry,array($this->tlanginfo[0],"language_id"));
		$qry = "SELECT termsBtn,continueBtn,returnBtn,exitBtn,terms_content,gallery,gallery_note FROM languages WHERE language_id=" . $this->tlanginfo[0];
		if ($navresult=$this->mysqli->query($qry)) {
			$compnavinfo = $navresult->fetch_assoc();
		}
		#$var_dump($compnavinfo);
		//Add gallery nav element (from languages table, using language id row already obtained)
		///////////////////////////////////////////////////////////////////////////
		$thisgallref = $compnavinfo["gallery"];
		#echo $thisgallref;
		$thisgallnoteref = $compnavinfo["gallery_note"];
		$galleryxml = $this->xmldat->addChild('gallery');
		$galleryxml->addChildWithCDATA('title', $thisgallref);
		$galleryxml->addChildWithCDATA('note', $thisgallnoteref);
		
		////////////////////////////////////////////////////////////
		//Add Return button nav element (from languages table, using language id row already obtained)
		///////////////////////////////////////////////////////////////////////////
		$thisretbut = $compnavinfo["returnBtn"];
		$returnbutxml = $this->xmldat->addChild('back_button');
		$returnbutxml->addChildWithCDATA('label', $thisretbut);
		///////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////
		
		//1. Get seed section id = language id
		//2. Check sections TABLE for sections which have this as parent_id
		//3. If sections exist define a new section_group, then start writing out sections
		//4. For each section now need to check sections TABLE for those having this section as parent id
		//5. If such sections exist define new section_group...
		//6. Iterate through until nothing left to check
		
		//extract country code from new xml data file name
		$stcntrycode = strpos($this->tlangxmlpath,"_"); //data xml files must always be in format data_<countrycode>.xml
		$endcntrycode = strpos($this->tlangxmlpath,".xml");
		$cntrycode = substr($this->tlangxmlpath,$stcntrycode+1,$endcntrycode-$stcntrycode-1);
		//echo $cntrycode . "that was country";
		
		//Iterating section groups through OOP
		//Get chosen title identifying attributes from POST
		//$xmldat->asXML("textxml.xml");

		$this->sgrouparr = [];
		#echo $this->tlang3code;
		$tsg = new sectionGroup($this->tlanginfo[0],$this->xmldat,$cntrycode,$this->mysqli,$this->tlang3code,1,$this->tlanginfo[0],$this->tlang3code,$this->tlanginfo[0],1);
		array_push($this->sgrouparr,$tsg);
		$tsg->exploresecs($this->xmldat);
		
	}
	
	function gettitlelanginfo() {
		$varlist = ["title_id",
					"language",
					"continueBtn",
					"returnBtn",
					"exitBtn",
					"gallery",
					"gallery_note"];
		$qry = mysqlselect_encconv($varlist,"utf8","languages","language_id",$this->tlanginfo[0],NULL); //mysqlselect_encconv($tmysql,$varlist,$enctype,$table,$condvar,$cond,$ord)
		$tlangrow = sqlbuild_mysqlsel_one($this->mysqli,$qry,[$this->tlanginfo[0],"language_id"]);
		$thisttlid = $tlangrow[0];
		#echo $thisttlid;
		#echo $tlangrow[1];
	}
	
	function getlangxmlpath($tlanginfo) {
		$langpref = substr($tlanginfo[1],0,3);
		switch ($langpref) {
			case "Eng":
				echo "English";
				$this->tlang3code = "Eng";
				$this->tlangxmlpath = "data_eng.xml";
				break;
			case "Fra":
				echo "French";
				$this->tlang3code = "Fra";
				$this->tlangxmlpath = "data_fra.xml";
				break;
			case "Esp":
				echo "Spanish";
				$this->tlang3code = "Esp";
				$this->tlangxmlpath = "data_esp.xml";
				break;
			case "Sve":
				echo "Swedish";
				$this->tlang3code = "Sve";
				$this->tlangxmlpath = "data_sve.xml";
				break;
			case "Deu":
				echo "German";
				$this->tlang3code = "Deu";
				$this->tlangxmlpath = "data_deu.xml";
				break;
			case "Ned":
				echo "Dutch";
				$this->tlang3code = "Ned";
				$this->tlangxmlpath = "data_ned.xml";
				break;
			case "Ita":
				echo "Italian";
				$this->tlang3code = "Ita";
				$this->tlangxmlpath = "data_ita.xml";
				break;
			case "Por":
				echo "Portuguese";
				$this->tlang3code = "Por";
				$this->tlangxmlpath = "data_por.xml";
				break;
		}
	}
	
	function writexml($brandname,$compname) {
	
		echo "starting xmlwrite...";
		$targxmldir = builddirref_sel([$brandname,$compname,$GLOBALS['xmldir']],1,0);
		#echo "to " . $targxmldir . "/" . $this->tlangxmlpath;
		if (file_exists($targxmldir)) {
			if (file_exists($targxmldir . "/" . $this->tlangxmlpath)) {
				if (file_exists($targxmldir . "/" . $this->tlangxmlpath . "-old")) {
					unlink($targxmldir . "/" . $this->tlangxmlpath . "-old");
					rename($targxmldir . "/" . $this->tlangxmlpath,$targxmldir . "/" . $this->tlangxmlpath . "-old");
				} else {
					rename($targxmldir . "/" . $this->tlangxmlpath,$targxmldir . "/" . $this->tlangxmlpath . "-old");
				}
				$nowprint = $this->xmldat->asXML($targxmldir . "/" . $this->tlangxmlpath);
			} else {
				$nowprint = $this->xmldat->asXML($targxmldir . "/" . $this->tlangxmlpath);
			}
		} else {
			$targxmldir = builddirref_add([$brandname,$compname,$GLOBALS['xmldir']],1);
			$nowprint = $this->xmldat->asXML($targxmldir . "/" . $this->tlangxmlpath);
		}
		echo "finished writing xml";
		#$nowprint = $this->xmldat->asXML($this->tlangxmlpath);
	
	}

}

Class compil_control {

	//Properties
	public $langlist;
	public $curlangnum;
	public $langcompil_store;
	public $mysqli;
	public $brandname;
	public $compname;
	
	function __construct($langlist,$mysqli,$brandname,$compname) {
		$this->langlist = $langlist;
		$this->curlangnum = 0;
		$this->langcompil_store = array();
		$this->mysqli = $mysqli;
		$this->brandname = $brandname;
		$this->compname = $compname;
		$this->compilid = $compilid;
	}
	
	function startlangcompil($langnum) {
		if ($langnum<count($this->langlist)) {
			#echo $langnum;
			#echo $this->langlist[$langnum];
			$this->langcompil_store[$langnum] = new compillang($this->langlist[$langnum],$this->mysqli);
			$this->langcompil_store[$langnum]->gettitlelanginfo();
			$this->langcompil_store[$langnum]->getlangxmlpath($this->langlist[$langnum]);
			$this->langcompil_store[$langnum]->startxmlstream();
			$this->langcompil_store[$langnum]->writexml($this->brandname,$this->compname);
			$langnum += 1;
			$this->startlangcompil($langnum);
		} else {
			echo "all languages done";
		}
	}
}


/*foreach($_POST as $key => $value)
{
    echo $key;
	echo "::::";
	echo $value;
	echo "<br>";
}*/

$comptmpltid = $_POST["comptmplt"]; //title id of title being used as a template for the compilation

//find languages in this template title id
$langlist = [];
if ($langresult = $mysqli->query("SELECT language_id, language FROM languages WHERE title_id=" . $comptmpltid . " ORDER BY language_id")) {	
	$langrow_cnt = $langresult->num_rows;
								
	for ($i = 0; $i < $langrow_cnt; $i++) {
		$thislang = $langresult->fetch_row();
		$langlist[] = $thislang;
	}
}
//var_dump($langlist);

$qry = "SELECT brand_name FROM brands WHERE brand_id=" . $_SESSION["brandid"];
$brandname = sqlbuild_mysqlsel_one($mysqli,$qry,"brand_name")["brand_name"];
$qry = "SELECT compname FROM compilations WHERE compid=" . $_SESSION["compilid"];
$compname = sqlbuild_mysqlsel_one($mysqli,$qry,"compname")["compname"];

$compilcontrol = new compil_control($langlist,$mysqli,$brandname,$compname);
$compilcontrol->startlangcompil(0);
/*$compilcontrol->exploresecs($xmldat);
	
	
}*/
?>

<script>
document.getElementById("tp").
</script>

<form name="xmlcomplete" action="editor-compils.php" method="POST">
<button type="submit" name="submit" value="submit">Go Back</button>
</form>
<img src="" onload="document.xmlcomplete.submit();">
</body>
</html>