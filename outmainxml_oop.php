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

Class sectionGroup {
	//Properties
	public $groupid;
	public $istoplevel;
	public $parentid;
	public $parxml;
	public $sgxmldat;
	public $childgrps;
	public $mysqli;
	public $contpanelkup;
	public $linkkindarr;
	public $langpubloc;
	
	//Methods
	function __construct($groupid,$parxml,$langpubloc,$mysqli,$istoplevel) {
		//$parxml->asXML("textxml" . $groupid . ".xml");
		$this->groupid = $groupid;
		$this->sgxmldat = $parxml->addChild('section_group');
		
		//$parxml->asXML("textxml" . $groupid . ".xml");
		
		$this->childgrps = array();
		$this->mysqli = $mysqli;
		$this->contpanelkup = array(0=>"A",4=>"W",5=>"Q",6=>"E");
		$this->linkkindarr = array(0=>"file",1=>"webpage",2=>"email");
		$this->langpubloc = $langpubloc;
		$this->istoplevel = $istoplevel;
		echo $this->groupid;
	}
	
	function nextsg($tsecarr,$tsecxml) {
		
		if ($result=$this->mysqli->query("SELECT section_id FROM sections WHERE parent_id='" . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"] . "'")) {
			//echo "bye" . $tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"];
			if ($result->num_rows>0) {
				$tsg = new sectionGroup($tsecarr["CONVERT(CAST(section_id as BINARY) USING utf8)"],$tsecxml,$this->langpubloc,$this->mysqli,0);
				array_push($this->childgrps,$tsg);
				$tsg->exploresecs();
			}
		}
	}
	
	
	function wallpaper($tsecarr,$tsecxml) {
		//echo "wppane " . $tsecarr['section_id'];
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
			$twparr = sqlbuild_mysqlsel($this->mysqli,$qry,[$twp["CONVERT(CAST(wallpaper_group_id as BINARY) USING utf8)"],"wallpaper_group_id"]);
									
			foreach($twparr as $twp) {
				//e.g. <screensize_btn file="assets/pics/wallpapers/wp1_800x600.jpg"><![CDATA[800 x 600]]></screensize_btn>
				$twpsize = $twpgrpxml->addChildWithCDATA('screensize_btn', $twp["CONVERT(CAST(size as BINARY) USING utf8)"]);
				$twpsize->addAttribute("file", "assets/pics/wallpapers/" . $twpgrp["CONVERT(CAST(name as BINARY) USING utf8)"] . "_" . $twp["CONVERT(CAST(size as BINARY) USING utf8)"] . ".jpg");
			}
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
						
						$tlinkxml->addAttribute("location",str_replace(".pdf",".pdc",$targlinkdir . $this->langpubloc . "/" . $tlink["location"]));
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
		
		$tsecxml = $this->sgxmldat->addChild('section');
		//echo "content pane " . $tsecarr["CONVERT(CAST(pane_type as BINARY) USING utf8)"] . " that was";
		$tsecxml->addAttribute("contentPaneType",$this->contpanelkup[$tsecarr["CONVERT(CAST(pane_type as BINARY) USING utf8)"]]);
		
		
		$tsecxml->addChildWithCDATA('name', $tsecarr["CONVERT(CAST(name as BINARY) USING utf8)"]);
		$tsecxml->addChildWithCDATA('copy', $tsecarr["CONVERT(CAST(copy as BINARY) USING utf8)"]);
		
		//var_dump($tsecarr);
		$this->writelinks($tsecarr,$tsecxml,$parxml);
	}
	
	function exploresecs() {
		//echo "groupy " . $this->groupid;
		$varlist = ["section_id",
					"name",
					"pane_type",
					"copy"];
					
		if ($this->istoplevel == 1) { //If this is the top level of the title, in which language id is parent id
			$qry = mysqlselect_encconv_and($varlist,"utf8","sections",["parent_id","parent_is_language_id"],[$this->groupid,1],"sort_order");
		} else {
			$qry = mysqlselect_encconv_and($varlist,"utf8","sections",["parent_id","parent_is_language_id"],[$this->groupid,0],"sort_order");
		}
		
		$tparsecarr = sqlbuild_mysqlsel($this->mysqli,$qry,[$this->groupid,"parent_id"]);
		//var_dump($tparsecarr);
		
		//write sections and
		//check if any subsections are group parents themselves
		foreach($tparsecarr as $tparsec) {
			//echo $tparsec["CONVERT(CAST(name as BINARY) USING utf8)"];
			
			$this->stwritesec($tparsec);
		}
		
	}
	
	function outputxml($dirbuildls,$indref,$txmlfile) {

	}

}



///START SCRIPT
//Get language title information from mysql dbase
$langtitlid = $_POST['explid'];

$varlist = ["title_id",
			"language",
			"continueBtn",
			"returnBtn",
			"exitBtn",
			"gallery",
			"gallery_note"];
$qry = mysqlselect_encconv($varlist,"utf8","languages","language_id",$langtitlid,NULL); //mysqlselect_encconv($tmysql,$varlist,$enctype,$table,$condvar,$cond,$ord)
$tlangrow = sqlbuild_mysqlsel_one($mysqli,$qry,[$langtitlid,"language_id"]);
$thisttlid = $tlangrow[0];
$xmldat = new SimpleXMLElementExtended('<?xml version="1.0" encoding="utf-8"?><BrandInterface></BrandInterface>');
$xmldat->addAttribute('language',$tlangrow[1]);

//Add title xml section (from titles table cross-referencing from title id in language table)
///////////////////////////////////////////////////////////////////////////
$varlist = ["leftNavTitle1","leftNavTitle2","leftNavTitle3","leftNavTitle4"];
$qry = mysqlselect_encconv($varlist,"utf8","titles","title_id",$thisttlid,NULL);

$thisttlrow = sqlbuild_mysqlsel_one($mysqli,$qry,[$thisttlid,"title_id"]);
//https://stackoverflow.com/questions/6260224/how-to-write-cdata-using-simplexmlelement
	$ttlxml = $xmldat->addChild('title');
	$ttlxml->addChildWithCDATA('row1', $thisttlrow[0]);
	$ttlxml->addChildWithCDATA('row2', $thisttlrow[1]);
	$ttlxml->addChildWithCDATA('row3', $thisttlrow[2]);
	$ttlxml->addChildWithCDATA('row4', $thisttlrow[3]);
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add main_nav xml section (from sections table)
///////////////////////////////////////////////////////////////////////////
//Find all sections associated with this language id in sections table
$varlist = ["name"];
$qry = mysqlselect_encconv($varlist,"utf8","sections","parent_id",$langtitlid,"sort_order");
$tparsecarr = sqlbuild_mysqlsel($mysqli,$qry,[$langtitlid,"parent_id"]);
	//NEED TO CHECK AGAINST parent_is_language_id column to avoid accidental pick up of unassociated child
	//NEED TO SORT TO CORRECT ORDER - HOW?
	//$ttlxml->addChild('SectionCount',$row_cnt);
$mainnavxml = $xmldat->addChild('main_nav');
foreach($tparsecarr as $tparsec) {
	$mainnavxml->addChildWithCDATA('nav', $tparsec[0]);
}
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add gallery nav element (from languages table, using language id row already obtained)
///////////////////////////////////////////////////////////////////////////
$thisgallref = $tlangrow[5];
$thisgallnoteref = $tlangrow[6];
$galleryxml = $xmldat->addChild('gallery');
$galleryxml->addChildWithCDATA('title', $thisgallref);
$galleryxml->addChildWithCDATA('note', $thisgallnoteref);
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
//Add Return button nav element (from languages table, using language id row already obtained)
///////////////////////////////////////////////////////////////////////////
$thisretbut = $tlangrow[3];
$returnbutxml = $xmldat->addChild('back_button');
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


//Now need to find correct directory, need before section_group iteration for link locations
//first find parent title id, while also getting the xml file name
$qry = "SELECT title_id,xmlFile FROM languages WHERE language_id='" . $langtitlid . "'";
if ($result = $mysqli->query($qry)) {
	
	$trow = $result->fetch_array();
	$ttitleid = $trow[0];
	$txmlfile = $trow[1];
		
} else {
	echo "couldn't obtain title_id from language";
}

//extract country code from new xml data file name
$stcntrycode = strpos($trow[1],"_"); //data xml files must always be in format data_<countrycode>.xml
$endcntrycode = strpos($trow[1],".xml");
$cntrycode = substr($trow[1],$stcntrycode+1,$endcntrycode-$stcntrycode-1);
//echo $cntrycode . "that was country";	




//Iterating section groups through OOP
//Get chosen title identifying attributes from POST
//$xmldat->asXML("textxml.xml");

$sgrouparr = [];
$tsg = new sectionGroup($langtitlid,$xmldat,$cntrycode,$mysqli,1);
array_push($sgrouparr,$tsg);
$tsg->exploresecs($xmldat);



//Now, close up...


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


//Header('Content-type: text/xml');
/*if (count($sgrouparr==1)) { //Check I'm not going to blow up the server
	foreach($sgrouparr as $sgroup) {
		//$sgroup->outputxml([$tbrandname,$ttitlename,$GLOBALS['xmldir']],1,$txmlfile);
	}
} else {
	echo "what happened?";
}*/

		//Header('Content-type: text/xml');
		
		$targxmldir = builddirref_sel([$tbrandname,$ttitlename,$GLOBALS['xmldir']],1,0);
		if (file_exists($targxmldir)) {
			if (file_exists($targxmldir . "/" . $txmlfile)) {
				if (file_exists($targxmldir . "/" . $txmlfile . "-old")) {
					unlink($targxmldir . "/" . $txmlfile . "-old");
					rename($targxmldir . "/" . $txmlfile,$targxmldir . "/" . $txmlfile . "-old");
				} else {
					rename($targxmldir . "/" . $txmlfile,$targxmldir . "/" . $txmlfile . "-old");
				}
				$nowprint = $xmldat->asXML($targxmldir . "/" . $txmlfile);
			} else {
				$nowprint = $xmldat->asXML($targxmldir . "/" . $txmlfile);
			}
		} else {
			$targxmldir = builddirref_add([$tbrandname,$ttitlename,$GLOBALS['xmldir']],1);
			$nowprint = $xmldat->asXML($targxmldir . "/" . $txmlfile);
		}
	
		//Now write to export_status_log.txt that build has completed
		$logf = fopen($targxmldir . "/export_status_log.txt","a"); //"a" = open for writing only, places pointer to end of file
		fwrite($logf,"\r\n" . date(DATE_RFC2822) . " " . "Export of " . $txmlfile . " succeeded.");
		fclose($logf);


	//NEED TO CHECK AGAINST parent_is_language_id column to avoid accidental pick up of unassociated child
	//NEED TO SORT TO CORRECT ORDER - HOW?

	$_SESSION['lang'] = $langtitlid;
	$_SESSION['parsecid'] = $_POST['expparid'];
	$_SESSION['tsec'] = $_POST['exptid'];
	$_SESSION['selsecid'] = $_POST['expsid'];
	
	//gotourl('/pubseditorhtml/editor-sections.php');

?>

<script>
document.getElementById("tp").
</script>

<form name="xmlcomplete" action="editor-sections.php" method="POST">
<button type="submit" name="submit" value="submit">Go Back</button
</form>
<img src="" onload="document.xmlcomplete.submit();">
</body>
</html>
