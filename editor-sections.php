<?php

require("../OTPManager2/otpmgr_setup.php");
require_once(APPLICATION_DIR."otp_manager_secure.php");

include 'otpedfuncs.php';
$mysqli = openmysql();

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">
<link rel="stylesheet" href="otpedcss.css">

<script language="javascript">


	function subsecfill(item, index) {
		//alert(item);
		document.getElementById("subsecls").innerHTML += "<option value='" + item[0] + "'>" + item[1] + "</option>";
	}
	
	function seclinkfill(secid, item, index) {
		//alert(item);
		//alert(secarr[secid][9]);
		//alert(JSON.stringify(secarr[secid][9][item][0]));
		document.getElementById("seclinksls").innerHTML += "<option value='" + item + "'>" + secarr[secid][9][item][0] + "</option>";
	}
	
	function secwpsizfill(secid,twpgid,item,index) {
		//alert(item);
		document.getElementById("wpapersize").innerHTML += "<option value='" + item + "'>" + secarr[secid][10][twpgid][1][item][0] + "</option>";
	}
	
	function secwpnamfill(secid,item, index) {
		//alert(item);
		document.getElementById("wpapername").innerHTML += "<option value='" + item + "'>" + secarr[secid][10][item][0] + "</option>";
		
		//twpgid = item
		//Object.keys(secarr[secid][10][item][1]).forEach(function (item, index) {
		//secwpsizfill(secid, twpgid, item, index)});
	}

	function showsecatts(secid) {

		document.getElementById("sid").value = secid;
		document.getElementById("addremsid").value = secid;
		document.getElementById("subaddremparid").value = secid;
		document.getElementById("subaddremtid").value = secid;
		document.getElementById("lnkaddsid").value = secid;
		document.getElementById("edlnksid").value = secid;
		//document.getElementById("subaddremtid").value = secid;
		
		document.getElementById("panetype").value=secarr[secid][4];
		checkpanetype(secarr[secid][4]);
		
		document.getElementById("sortord").value=secarr[secid][7];
		document.getElementById("idtxt").innerHTML = secarr[secid][0];
		
		document.getElementById("sectitle").value=secarr[secid][3];
		
		document.getElementById("seccont").innerHTML = secarr[secid][5];
		
		document.getElementById("subsecls").innerHTML = "";
		
		if (secarr[secid][8]!="NA") { //9th item in array references subsections
			//alert(secarr[secid][8]);
			//document.getElementById("subsecls").size = secarr[secid][8].length;
			secarr[secid][8].forEach(subsecfill);
		} else {
			//alert("8 is NA");
		}
		
		document.getElementById("seclinksls").innerHTML = "";
		if (secarr[secid][9]!="NA") { //10th item in array references links in section
			//document.getElementById("seclinksls").size = secarr[secid][9].length;
			//alert(Object.keys(secarr[secid][9]));
			Object.keys(secarr[secid][9]).forEach(function (item, index) {
				seclinkfill(secid, item, index)});
		} else {
			//alert("9 is NA");
		}
		
		if (secarr[secid][10]!="NA") { //11th item in array references wallpapers in section
			Object.keys(secarr[secid][10]).forEach(function (item, index) {
				secwpnamfill(secid, item, index)});
		} else {
			//alert("10 is NA");
		}
		//alert("hllo");
		
		//also set subsecpar form element to the selected section so that this can be passed when going to subsections
		document.getElementById("subsecpar").value = secid;
		document.getElementById("wpsecid").value = secid;
		document.getElementById("wpsize-secid").value = secid;
		document.getElementById("wpfiles-secid").value = secid;
		document.getElementById("wpeditsecid").value = secid;
		
		if (secid == techpubssec) {
			document.getElementById("techpubssec").checked = true;
		} else {
			document.getElementById("techpubssec").checked = false;
		}
		//alert(document.getElementById("subsecpar").value);
	}
	
	function exportcolors() {
		
	}
	
	function gotosection(secid) {
		window.location.replace("editor-sections.php");
	}
	
	function showlinkatts(tlinkid) {
		//alert(document.getElementById("seclinksls").value);
		tlinkid = document.getElementById("seclinksls").value;
		cursecid = document.getElementById("sid").value;
		//alert(JSON.stringify(secarr[cursecid][9][tlinkid]));
		
		document.getElementById('linktype').value = secarr[cursecid][9][tlinkid][1];
		document.getElementById('lnksortord').value = secarr[cursecid][9][tlinkid][3];
		document.getElementById('linkid').innerHTML = tlinkid;
		document.getElementById('edlnkid').value = tlinkid;
		document.getElementById('linktitle').value = secarr[cursecid][9][tlinkid][0];
		document.getElementById('linktarg').value = secarr[cursecid][9][tlinkid][2];
		
		document.getElementById('overlay').style.display = "block";
		document.getElementById('linkseditarea').style.display = "block";
	}
	
	function showwpsizes(twpgid) {
		//alert(twpgid);
		
		cursecid = document.getElementById("sid").value;
		Object.keys(secarr[cursecid][10][twpgid][1]).forEach(function (item, index) {
		secwpsizfill(cursecid, twpgid, item, index)});
		
		document.getElementById("wpeditnamearea").style.display = "none";
		document.getElementById("wpnameditbut").style.display = "inline";
		
		document.getElementById("wpsize-wpid").value = twpgid;
	}
	
	function showwpnameedit() {
		curwpid = document.getElementById("wpapername").value;
		cursecid = document.getElementById("sid").value;
		
		document.getElementById("wpeditnamearea").style.display = "block";
		document.getElementById("wpnameold").value = secarr[cursecid][10][curwpid][0];
		document.getElementById("wpnamenew").value = secarr[cursecid][10][curwpid][0];
		document.getElementById("thiswpid").value = curwpid;
		
	}
	
	function hidewpnameedit() {
		document.getElementById("wpeditnamearea").style.display = "none";
	}
	
	function checkpanetype(paneid) {
		//alert(paneid);
		if (paneid==6) {
			document.getElementById("seccontarea").style.display = "none";
			document.getElementById("wpapereditarea").style.display = "none";
			document.getElementById("secsubdisparea").style.display = "none";
			document.getElementById("editbuttonarea").style.display = "none";
			document.getElementById("photgalleditarea").style.display = "block";
			
		} else if (paneid==4) {
			document.getElementById("secsubdisparea").style.display = "none";
			document.getElementById("wpapereditarea").style.display = "block";
			document.getElementById("editbuttonarea").style.display = "none";
			document.getElementById("seccontarea").style.display = "initial";
			document.getElementById("photgalleditarea").style.display = "none";
		} else if (paneid==0) {
			document.getElementById("secsubdisparea").style.display = "block";
			document.getElementById("wpapereditarea").style.display = "none";
			document.getElementById("editbuttonarea").style.display = "initial";
			document.getElementById("seccontarea").style.display = "initial";
			document.getElementById("photgalleditarea").style.display = "none";
		} else if (paneid==1) {
			document.getElementById("secsubdisparea").style.display = "initial";
			document.getElementById("wpapereditarea").style.display = "none";
			document.getElementById("editbuttonarea").style.display = "initial";
			document.getElementById("seccontarea").style.display = "initial";
			document.getElementById("photgalleditarea").style.display = "none";
		} else if (paneid==2) {
			document.getElementById("secsubdisparea").style.display = "initial";
			document.getElementById("wpapereditarea").style.display = "none";
			document.getElementById("editbuttonarea").style.display = "initial";
			document.getElementById("seccontarea").style.display = "initial";
			document.getElementById("photgalleditarea").style.display = "none";
		} else if (paneid==3) {
			document.getElementById("secsubdisparea").style.display = "initial";
			document.getElementById("wpapereditarea").style.display = "none";
			document.getElementById("editbuttonarea").style.display = "initial";
			document.getElementById("seccontarea").style.display = "initial";
			document.getElementById("photgalleditarea").style.display = "none";
		} else if (paneid==5) {
			document.getElementById("secsubdisparea").style.display = "none";
			document.getElementById("wpapereditarea").style.display = "none";
			document.getElementById("editbuttonarea").style.display = "none";
			document.getElementById("seccontarea").style.display = "initial";
			document.getElementById("photgalleditarea").style.display = "none";
		}
	}
	
	function copyoutsecarr(item,index) {
		//alert("hello");
		alert(item);
	}
	
	
</script>

</head>

<body>
<?php

//Establish current section id, parent id and subsection select id, if any
//Plus establish main language id

//Three (legitimate) ways to arrive at this page - 1) From editor-langs.php,
//2) on selection of subsection within editor-sections.php,
//3) Returning from savesecatts.php after clicking to save section within editor-sections.php

/*if (isset($_SESSION['parsecid'])) { //Is this coming back from savesecatts.php as part of a session?
	
	$langid = $_SESSION['lang']; //The root language id
	$parid = $_SESSION['parsecid']; //The parent of the currently viewed section, accessible by return button
	$tsecid = $_SESSION['tsec']; //The currently viewed section
	$selsec = $_SESSION['selsecid']; //The selected subsecton within currently viewed section
	
	$_SESSION = array();
	session_destroy();
	
} else {
		
	if (isset($_POST['fromlang'])) { //if fromlang is set then request must be coming from editor-langs.php, meaning langid is the current sectionid and no subsections are in focus
		$langid = $_POST['lang'];
		$parid = "na";
		$tsecid = $langid;
		$selsec = "na";
	} else {

		//If no session and no fromlong then this must be an iteration as a result of selecting a subsection or clicking return to parent button
		if (isset($_POST['subsecls'])) { //if subsec then this must be coming from selection of subsection
			$langid = $_POST['lang'];
			$parid = $_POST['tsec'];
			$tsecid = $_POST['par'];
			$selsec = $_POST['subsecls'];
			
			//echo "selsec" . $selsec . "///////////";
			
			//echo "parid: " . $parid . "///////////";
		} else { //Otherwise this must finally be coming from the return to parent button, here we should set selsec to show where we've just been
			
			if (isset($_POST['retbut'])) {
				$langid = $_POST['lang'];
				$tsecid = $_POST['par'];
				
				if ($tsecid == $langid) { //Check if we're back to the language id root, if so parent is na
				
					$parid = "na";
				} else {
				//need to go to mysql to retrieve parent id on return button since don't store parent from two levels up
					if ($result = $mysqli->query("SELECT parent_id FROM sections2 WHERE section_id=" . $tsecid)) {
						$row_cnt = $result->num_rows;
						if ($row_cnt==1) {
							$rowfet = $result->fetch_row();
							$parid = $rowfet[0];
						} else {
							echo "Duplicate section_id found!";
						}
					} else {
						echo "error: couldn't find section parent_id in database here";
						exit;
					}
				}
				
				$selsec = $_POST['tsec'];
			}
		}
	}

}*/

if (!isset($_SESSION['sectionnav'])) { //if coming from editor-langs.php the section nav list won't be setup. Initialise here
	$_SESSION['sectionnav'] = array(); //initialise section navigation list)
	$_SESSION['sectionnav'][] = $_POST['lang'];
	//var_dump($_SESSION['sectionnav']);
}
if (isset($_POST['retbut'])) { //if returning from child section - return button
	array_pop($_SESSION['sectionnav']);
}

$selsec = "na";

if (isset($_POST['subsecls'])) {
	//if coming from parent section - selected this section
	//$_SESSION['sectionnav'][] = $_POST['subsecls'];
	echo $_POST['subsecls'] . "////";
	echo $_POST['lang'] . "////";
	echo $_POST['tsec'] . "////";
	echo $_POST['par'] . "////";
	$selsec = $_POST['subsecls'];
	$_SESSION['selsecid'] = $_POST['subsecls'];
	$langid = $_POST['lang'];
	$parid = $_POST['tsec'];
	$tsecid = $_POST['par'];
	array_push($_SESSION['sectionnav'],$tsecid);
}

if (isset($_SESSION['selsecid'])) {
	$selsec = $_SESSION['selsecid'];
}

//echo "<br>";
//var_dump($_SESSION['sectionnav']);

/*echo $_SESSION['sectionnav'][0] . "--";
echo end($_SESSION['sectionnav']) . "--";
if (count($_SESSION['sectionnav'])>1) {
	end($_SESSION['sectionnav']);
	echo prev($_SESSION['sectionnav']) . "--";
} else {
	echo "na" . "--";
}
echo $selsec . "--";*/


$varlist = ["section_id",
			"parent_is_language_id",
			"parent_id",
			"name",
			"pane_type",
			"copy",
			"image",
			"sort_order"];
$qry = mysqlselect_encconv($varlist,"utf8","sections","parent_id",end($_SESSION['sectionnav']),"section_id");
//echo $qry;
if ($result = $mysqli->query($qry)) {
		
	echo "<script language='javascript'>";
		
	$row_cnt = $result->num_rows;
	echo "var rowcnt = " . $row_cnt . ";";
			
} else {
		
	echo "error: couldn't find sections in database here";
	exit;
}
	
echo "var secarr = {};";

	
for ($i = 0; $i < $row_cnt; $i++) {
	$thissec = $result->fetch_row();
	//echo "alert($thissec));";
	if ($thissec[0]=="aaaaaaaaaaaaaa" || $thissec[0]=="bbbbbbbbbbbbbbbb") {
		$tarr = array("a","b","c","d","e","f","g","h");
		echo "secarr[" . $thissec[0] . "] = " . json_encode($tarr) . ";";
		//echo "alert(" . $thissec[1] . ");";
	} else {
		echo "secarr[" . $thissec[0] . "] = " . json_encode($thissec) . ";";
	}
	
	//now need to find subsections and links for all possibilities
	if ($subresult = $mysqli->query("SELECT * FROM sections WHERE parent_id=" . $thissec[0] . " AND parent_is_language_id=0 ORDER BY sort_order")) {
		$subrow_cnt = $subresult->num_rows;
		if ($subrow_cnt>0) {
			$tsublist = array();
			for ($j = 0; $j < $subrow_cnt; $j++) {
				$thissubsec = $subresult->fetch_row();
				$tsubid = $thissubsec[0];
				$tsubnam = $thissubsec[3];
				$tsubrefarr = array($tsubid,$tsubnam);
				array_push($tsublist,$tsubrefarr);				
			}
			echo "secarr[" . $thissec[0] . "].push(" . json_encode($tsublist) . ");";
		} else {
			echo "secarr[" . $thissec[0] . "].push('NA');"; //no subsections marked by "NA" - need to call this back in scripts calling subsections
		}
	}
	
	if ($linkresult = $mysqli->query("SELECT * FROM links WHERE parent_section_id=" . $thissec[0] . " ORDER BY sort_order")) {
		$linkrow_cnt = $linkresult->num_rows;
		//echo "alert(" . $linkrow_cnt . ");";
		if ($linkrow_cnt>0) {
			$tlinklist = array();
			for ($k = 0; $k < $linkrow_cnt; $k++) {
				$thislink = $linkresult->fetch_row();
				$tlinkid = $thislink[0];
				$tlinknam = $thislink[2];
				$tlinktype = $thislink[3];
				$tlinkloc = $thislink[4];
				$tlinkord = $thislink[5];
				$tlinkrefarr = array($tlinknam,$tlinktype,$tlinkloc,$tlinkord);
				//array_push($tlinklist,$tlinkrefarr);
				$tlinklist += [$tlinkid=>$tlinkrefarr];
			}
			echo "secarr[" . $thissec[0] . "].push(" . json_encode($tlinklist) . ");";
		} else {
			echo "secarr[" . $thissec[0] . "].push('NA');"; //no subsections marked by "NA" - need to call this back in scripts calling subsections
		}
	}
	
	if ($wpgroupresult = $mysqli->query("SELECT * FROM wallpaper_groups WHERE parent_section_id=" . $thissec[0] . " ORDER BY sort_order")) {
		$wpgrow_cnt = $wpgroupresult->num_rows;
		if ($wpgrow_cnt>0) {
			$twpglist = array();
			for ($wg = 0; $wg < $wpgrow_cnt; $wg++) {
				$thiswpg = $wpgroupresult->fetch_row();
				$twpgname = $thiswpg[2];
				$twpgid = $thiswpg[0];
				
				if ($wpresult = $mysqli->query("SELECT * FROM wallpapers WHERE wallpaper_group_id=" . $twpgid . " ORDER BY sort_order")) {
					$wprow_cnt = $wpresult->num_rows;
					if ($wprow_cnt>0) {
						$twplist = array();
						for ($w = 0; $w < $wprow_cnt; $w++) {
							$thiswp = $wpresult->fetch_row();
							$twpid = $thiswp[0];
							$twpsize = $thiswp[2];
							$twpsort = $thiswp[3];
							$twpref = array($twpsize,$twpsort);
							$twplist += [$twpid=>$twpref];
						}
						
					}
				}
				$twpgref = array($twpgname,$twplist);
				$twpglist += [$twpgid=>$twpgref];
			}
			
			echo "secarr[" . $thissec[0] . "].push(" . json_encode($twpglist) . ");";
			
		} else {
			echo "secarr[" . $thissec[0] . "].push('NA');"; //no subsections marked by "NA" - need to call this back in scripts calling subsections
		}
	}
	
	#now find "is tech pubs section" checkbox value
	$qry = "SELECT techpubs_secid FROM langid_techpubslookup WHERE langid=" . $_SESSION['sectionnav'][0];
	if ($techpubsresult = $mysqli->query($qry)) {
		if ($techpubsresult->num_rows>0) {
			$techpubssec = $techpubsresult->fetch_row();
			echo "var techpubssec = " . $techpubssec[0] . ";";
		} else {
			echo "var techpubssec = -999;";
		}
	}

}


echo "</script>";
//var_dump(secarr);


?>

<section id="topnavbar">
<ul id="topnavlist">
	<?php 
	if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $_SESSION['brandid'])) {
		$row_cnt = $result->num_rows;
		if ($row_cnt>0) {
			$brandnam = $result->fetch_row();
		}
	} else {
		echo "error: couldn't find brand in database";
		exit;
	}
	if ($result = $mysqli->query("SELECT title_name FROM titles WHERE title_id=" . $_SESSION['titleid'])) {
		$row_cnt = $result->num_rows;
		if ($row_cnt>0) {
			$ttlnam = $result->fetch_row();
		}
	} else {
		echo "error: couldn't find titles in database";
		exit;
	}
	
	echo "<li>" . "///<a href='editor.php' target='_self'>" . $GLOBALS['industrydirs'][1] . "</a>" . "</li>";
	echo "<li>" . "///<a href='editor-titles.php' target='_self'>" . $brandnam[0] . "</a>" . "</li>";
	echo "<li>" . "///<a href='editor-langs.php' target='_self'>" . $ttlnam[0] . "</a>" . "</li>";
	echo "<li>" . "///<a href='editor-sections.php' target='_self'>" . "Main" . "</a>" . "</li>";
	?>
</ul>
</section>

<section id="mainarea">

	<section id="controlbox">

		<p id="cb-head" class="boxhead"></p>
		
		<section id="sec-sellangform" class="formarea">
			
			<?php
			//Insert section/lang titles and nav guide - need to work backwards to build list then output_add_rewrite_var
			
			
			
			/*echo "<p id='navind'>" . $GLOBALS['industrydirs']['1'];
			if ($parid=="na") {
				echo "<h1>Main Sections</h1>";
			} else {
				$qry = "SELECT name FROM sections WHERE section_id='" . $parid . "'";
				if ($result = $mysqli->query($qry)) {
					$secname = $result->fetch_array();
					echo "<h1>" . $secname[0] . "</h1>";
				} else {
					echo "No section parent name found";
				}
			}*/
			
			
			
			
			#Insert return to parent button and form shown if in subsection
			if (count($_SESSION['sectionnav'])>1) {
				echo "<form id='retparent-form' name='retparent-form' action='editor-sections.php' method='POST'>";
				echo "<input type='hidden' name='lang' id='retbutlang' value=" . $_SESSION['sectionnav'][0] . ">";
				end($_SESSION['sectionnav']);
				echo "<input type='hidden' name='par' id='par' value=" . prev($_SESSION['sectionnav']) . ">";
				echo "<input type='hidden' name='tsec' id='retbuttsec' value=" . end($_SESSION['sectionnav']) . ">";
				echo "<input type='hidden' name='retbut' id='retbut' value='retbut'>";
				echo "<button type='submit' form='retparent-form' name='submitret' value='retparent'>Return to parent section</button>";
				echo "</form>";
			} else {
				//var_dump($_SESSION['sectionnav']);
			}
			
			?>
			
			
			<form id="sellangform" class="otpeditor-form" name="sellangform" method="POST" action="editor-.php">
			
			<?php
			
			if (end($_SESSION['sectionnav']) == $_SESSION['sectionnav'][0]) { #if parent_id is language_id set as a flag so only language_id sections are displayed & vice versa
				$parent_is_lang = 1;
			} else {
				$parent_is_lang = 0;
			}
			
			#find number of sections from mysql table
			if ($result = $mysqli->query("SELECT * FROM sections WHERE parent_id=" . end($_SESSION['sectionnav']) . " AND parent_is_language_id=" . $parent_is_lang . " ORDER BY sort_order")) {
				
				$row_cnt = $result->num_rows;
				
			} else {
			
				echo "error: couldn't find sections in database";
				exit;
			}
			
			
			$modrow_cnt = $row_cnt + 2;
			echo "<select class='otped-sel' id='secls' name='secls' size='" . $modrow_cnt . "'>";
			
			for ($i = 0; $i < $row_cnt; $i++) {
				$thissec = $result->fetch_row();
				
				echo "<option value='" . $thissec[0] . "' onClick='javascript:showsecatts($thissec[0]);'>" . $thissec[3] . "</option>";
				
			}
			echo "</select>";
				
				
			$mysqli->close();
			?>
			<br>
			
			
			</form>
			
			<form id="addsect" name="addsect" action="addremsect.php" method="POST">
				<input type="hidden" name="parid" id="addremparid" value="<?php echo end($_SESSION['sectionnav']);?>">
				<input type="hidden" name="addremsid" id="addremsid" value="">
				<input type="hidden" name="addremtid" id="addremtid" value="<?php echo end($_SESSION['sectionnav']);?>">
				<input type="hidden" name="addremlid" id="addremlid" value="<?php echo $_SESSION['sectionnav'][0];?>">
				<button type="submit" name="addsect" value="addsect">Add a Section to this level</button><br>
				<button type="submit" name="remsect" value="remsect">Remove Section</button>
			</form>
			

			
			<form id="expmainxml" name="expmainxml" action="outmainxml_oop.php" method="POST">
				<!--<input type="hidden" name="langtitlid" id="langtitlid" value="">
				<input type="hidden" name="expparid" id="expparid" value="">
				<input type="hidden" name="expsid" id="expsid" value="na">
				<input type="hidden" name="exptid" id="exptid" value="">-->
				<input type="hidden" name="explid" id="explid" value="<?php echo $_SESSION['sectionnav'][0];?>">
				<p><button type="submit" value="exportmainxml">Export XML File</button></p>
			</form>
			
		
		</section>


	</section>

	<section id="displaybox">

		<p id="dispbox-head"></p>

		<section class="attrbox" id="secattrbox">
			
			<form id="secatts" name="secatts" method="POST" action="savesecatts.php">
				<input type="hidden" name="lang" id="savelangid" value="<?php echo $_SESSION['sectionnav'][0];?>">
				<input type="hidden" name="sid" id="sid" value="">
				<input type="hidden" name="parsecid" id="parsecid" value="<?php echo $_SESSION['sectionnav'][0];?>">
				<input type="hidden" name="tsecid" id="savetsecid" value="<?php echo end($_SESSION['sectionnav']);?>">
				
				<select name='panetype' id='panetype' size='1' onClick="javascript:checkpanetype(this.value)">
					<option value=0>Pane Type A</option>
					<option value=1>Pane Type B</option>
					<option value=2>Pane Type C</option>
					<option value=3>Pane Type D</option>
					<option value=4>Wallpapers</option>
					<option value=5>Quit</option>
					<option value=6>Photo Gallery</option>
				</select>
				
				&nbsp;&nbsp;&nbsp;&nbsp;Sort Order: <input type='text' name='sortord' id='sortord' value='' size='10'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" id="techpubssec" name="techpubssec" value="techpubs"><label for="techpubssec">Is publications section?</label><br>
				
				Section Title &nbsp;&nbsp;&nbsp;&nbsp;id:<span id='idtxt'></span>
				<!--&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" value="copysectree">Copy this section tree to...</button>--><br>
				
				<input type="text" id="sectitle" name="sectitle" size="100" value=''><br>
				
				<section id="seccontarea">
					Section Content<br>
					<textarea form="secatts" id="seccont" name="seccont" size="200" rows="10" cols="100" value=""></textarea><br>
					<button type="submit" id="savesec" name="savesec" value="savesec" form="secatts">Save this section</button>
				</section>
				
			</form>

			<section id="secsubdisparea">
				<form id="subsecsform" name="subsecsform" action="editor-sections.php" method="POST">
					<input type="hidden" name="lang" id="lang" value="<?php echo $_SESSION['sectionnav'][0];?>">
					<input type="hidden" name="tsec" id="tsec" value="<?php echo end($_SESSION['sectionnav']);?>">
					<input type="hidden" name="par" id="subsecpar" value="">
					Sections<br>
					<select name="subsecls" id="subsecls" value="" size="8" onClick="form.submit()">
					</select>
				</form>
				<form id="seclinksform" name="seclinksform" action="editor-seclinks.php" method="POST">
					Links<br>
					<select name="seclinksls" id="seclinksls" size="8" onClick='javascript:showlinkatts(this.value);'>
					</select>
				</form>
				<section style="clear:both; height:16px;"></section>
			</section>
			
			<section id="wpapereditarea">
				<p><b>Wallpapers</b><p>
				
				<p>Use this naming convention for images being uploaded</p>
				<p style="color:blue;">[Wallpaper name]_thumb.jpg and [Wallpaper Name]_[Wallpaper Size].jpg<br>
				= wp1_thumb.jpg and wp1_800x600.jpg</p>
				
				<section id="wpnamearea">
				
					<form id="wpapernamform" name="wpapernamform" method="POST" action="addremwpaper.php">
					
						Wallpaper Names<br>
						<select id="wpapername" name="wpapername" size="4" value="" onClick="showwpsizes(this.value);">
						</select>
						<input type="hidden" name="wpsecid" id="wpsecid" value="">
						
						<br><button type="button" name="wpnameeditbut" id="wpnameditbut" value="wpnameeditbut" onClick="showwpnameedit();">Edit name</button>
						<br><button type="submit" name="addwpname" value="addwpname">Add</button>
						<br><button type="submit" name="remwpname" value="remwpname">Remove</button>
					
					</form>
				</section>
				
				<section id="wpeditnamearea">
					<form id="wpeditnamefrm" name="wpeditnamefrm" method="POST" action="editwpname.php">
						<br>Current wp name: <input type="text" name="wpnameold" id="wpnameold" size="20" readonly>
						<br>New wp name: <input type="text" name="wpnamenew" id="wpnamenew" size="20">
						<input type="hidden" name="thiswpid" id="thiswpid" value="">
						<input type="hidden" name="wpeditsecid" id="wpeditsecid" value="">
						<br><button type="submit" name="savewpname" value="submit">Save edit</button>
					</form>
				</section>
				
				<section id="wpsizearea">
						
					<form id="wpapersizeform" name="wpapersizeform" method="POST" action="addremwpsize.php">
						Wallpaper Sizes<br>
						<select id="wpapersize" name="wpapersize" size="4">
						</select>
						
						<input type="hidden" id="wpsize-secid" name="wpsize-secid" value="">
						<input type="hidden" id="wpsize-wpid" name="wpsize-wpid" value="">
						
						<button type="submit" name="addwpsize" value="addwpsize">Add</button>
						<button type="submit" name="remwpsize" value="remwpsize">Remove</button>
						
					</form>
					
				</section>
				
				<section id="wpaperuploadarea">
				
					<form id="wpaperupload" name="wpaperupload" method="POST" action="wpaperupload.php"  enctype="multipart/form-data">
					
						<label for="files">Choose Wallpaper files</label>
						<input type="file" id="wpfiles" name="wpfiles[]" multiple><br>
						<input type="hidden" id="wpfiles-secid" name="wpfiles-secid" value="">
						<input type="submit" name="uploadwp" id="uploadwp" value="Upload">
					</form>
				
				</section>
					
			</section>
			
			<section id="photgalleditarea" class="attrbox">
				Upload a group of Large and Thumbnail images equal in name and number. Then upload one album preview image. When uploading is completed, use the Export DVD Gallery XML button.
				<form id="photgallform" name="photgallform" action="exportgallxml.php" method="POST">
					<button name="uplargegall">Upload Large Gallery Images</button> (476 x 298 px)<br>
					<button name="upthumbgall">Upload Thumbnail Gallery Images</button> (100 x 63 px)<br>
					<button name="upprevgall">Upload Gallery Preview Image</button> (Name MUST be albumPreview.jpg. 78 x 49 px)<br><br>
					
					<button type="submit" name="exportgallxml">Export DVD Gallery XML File</button>
				</form>
				
			</section>
			
			
			<section id="editbuttonarea">
			
				<section style="float:left;width:200px;">
				<form id="addsubsec" name="addsubsec" action="addremsect.php" method="POST">
					<input type="hidden" name="parid" id="subaddremparid" value="">
					<input type="hidden" name="addremsid" id="subaddremsid" value="">
					<input type="hidden" name="addremtid" id="subaddremtid" value="">
					<input type="hidden" name="addremlid" id="subaddremlid" value="<?php echo $_SESSION['sectionnav'][0];?>">
					<button type="submit" form="addsubsec" name="addsect" value="addsect">Add a Sub-section</button><br>
				</form>
				</section>
				
				<section style="float:left;width:100px;">
				<form id="addlink" name="addlink" action="addlink.php" method="POST">
					<input type="hidden" name="lnkaddparid" id="lnkaddparid" value="<?php echo end($_SESSION['sectionnav']);?>">
					<input type="hidden" name="lnkaddsid" id="lnkaddsid" value="">
					<input type="hidden" name="lnkaddtid" id="lnkaddtid" value="<?php echo end($_SESSION['sectionnav']);?>">
					<input type="hidden" name="lnkaddlid" id="lnkaddlid" value="<?php echo $_SESSION['sectionnav'][0];?>">
					<button type="submit" name="addlink" form="addlink" value="Submit">Add a Link</button><br>
				</form>
				</section>
				
				<section style="clear:both;"></section>
			</section>
			
		</section>

	</section>
</section>	

<section id="overlay">
<section id="linkseditarea" class="attrbox">
	<form id="saveremlink" name="saveremlink" action="saveremlink.php" method="POST">
	<p><b>Edit link</b></p>
		Link Type
		<select id="linktype" name="linktype" value="0">
			<option value="0">PDF File</option>
			<option value="1">Web Page</option>
			<option value="2">Email</option>
		</select>
		
		Sort Order: <input type="text" name="lnksortord" id="lnksortord" value="" size="4"><br>
		
		Link Title <span id="linkid"></span><br>
		<input type="text" name="linktitle" id="linktitle" size="100"><br>
		Link Target<br>
		<input type="text" name="linktarg" id="linktarg" size="100"><br>
		
		<input type="hidden" name="edlnkparid" id="edlnkparid" value="<?php echo end($_SESSION['sectionnav']);?>">
		<input type="hidden" name="edlnksid" id="edlnksid" value="">
		<input type="hidden" name="edlnktid" id="edlnktid" value="<?php echo end($_SESSION['sectionnav']);?>">
		<input type="hidden" name="edlnklid" id="edlnklid" value="<?php echo $_SESSION['sectionnav'][0];?>">
		<input type="hidden" name="edlnkid" id="edlnkid" value=""> <!--Actual link id-->
		
		<button type="submit" name="savelink" id="savelink" class="subbut" value="savelink">Save this link</button>
		<button type="submit" name="remlink" id="remlink" class="subbut" value="remlink">Remove this link</button>
		
		<p><a href="#" onClick="javascript:document.getElementById('overlay').style.display = 'none';document.getElementById('linkseditarea').style.display = 'none';">close link edit screen</a></p>
	</form>
	
</section>
</section>



<?php
	if ($selsec!="na") {
			
		echo "<script language='javascript'>document.getElementById('secls').value=$selsec;showsecatts($selsec);</script>";
	}
?>

</body>

</html>