<?php

session_start();

$brandid = $_POST['bid'];
$titleid = $_POST['tid'];
$langid = $_POST['lid'];

$langname = $_POST['lname'];
$sortord = $_POST['sortord'];
$dataxmlpath = $_POST['datfile'];
$contlabel = $_POST['contlabel'];
$retlabel = $_POST['returnlabel'];
$exitlabel = $_POST['exitlabel'];
$gallbutlabel = $_POST['gallbutlab'];
$gallpanenote = $_POST['gallnote'];
$termsbutlabel = $_POST['termsbutlab'];
$termscont = $_POST['termscont'];


include 'otpedfuncs.php';
$mysqli = openmysql();

//First find current language xmlFile
if ($result = $mysqli->query("SELECT xmlFile FROM languages WHERE language_id='" . $langid . "'")) {
	
	$curxmlpath = $result->fetch_array();
	
} else {
	echo "couldn't get existing xmlFile from languages table";
	$mysqli->close();
	exit;
}

//Now find bname and tname
if ($result = $mysqli->query("SELECT title_name FROM titles WHERE title_id='" . $titleid . "'")) {
	$tname = $result->fetch_array();
} else {
	echo "couldn't get title_name from table";
	$mysqli->close();
	exit;
}

if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id='" . $brandid . "'")) {
	$bname = $result->fetch_array();
} else {
	echo "couldn't get brand_name from table";
	$mysqli->close();
	exit;
}


$qry = "UPDATE languages SET language='" . $langname . 
							"',xmlFile='" . $dataxmlpath . 
							"',termsBtn='" . $termsbutlabel . 
							"',continueBtn='" . $contlabel . 
							"',returnBtn='" . $retlabel . 
							"',exitBtn='" . $exitlabel . 
							"',terms_content='" . str_replace("'","''",$termscont) . 
							"',gallery='" . $gallbutlabel . 
							"',gallery_note='" . $gallpanenote . 
							"',sort_order='" . $sortord . 
							"' WHERE language_id='" . $langid . "'";

//echo $qry;
if ($result = $mysqli->query($qry)) {
	
	//extract country code from new xml data file name
	$stcntrycode = strpos($dataxmlpath,"_"); //data xml files must always be in format data_<countrycode>.xml
	$endcntrycode = strpos($dataxmlpath,".xml");
	$cntrycode = substr($dataxmlpath,$stcntrycode+1,$endcntrycode-$stcntrycode-1);
	//echo $cntrycode;
	
	//...and extract country code from old xml data file name
	$stcntrycode = strpos($curxmlpath[0],"_"); //data xml files must always be in format data_<countrycode>.xml
	$endcntrycode = strpos($curxmlpath[0],".xml");
	$curcntrycode = substr($curxmlpath[0],$stcntrycode+1,$endcntrycode-$stcntrycode-1);
	//echo $curcntrycode;
	
	//update directories if needed
	if ($cntrycode != $curcntrycode) {
		//echo builddirref_sel([$bname[0],$tname[0],$GLOBALS['pubspath'],$curcntrycode],1);
		if (file_exists(builddirref_sel([$bname[0],$tname[0],$GLOBALS['pubspath'],$curcntrycode],1,0))) {
			if (file_exists(builddirref_sel([$bname[0],$tname[0],$GLOBALS['pubspath'],$cntrycode],1,0))) {
				echo "This title name already exists";
				$mysqli->close();
				exit;
			} else {
				rename(builddirref_sel([$bname[0],$tname[0],$GLOBALS['pubspath'],$curcntrycode],1,0),builddirref_sel([$bname[0],$tname[0],$GLOBALS['pubspath'],$cntrycode],1,0));
			}
		}
	} else {
		//No change to xml file name so need to change here
		
	}
	
	
	$_SESSION['brandid'] = $brandid;
	$_SESSION['titleid'] = $titleid;
	$mysqli->close();
	gotourl('/pubseditorhtml/editor-langs.php');
	exit;	

			
} else {
		
	$mysqli->close();	
	echo "error: couldn't update language in database";
	exit;
}



?>