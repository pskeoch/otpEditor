<?php

require("../OTPManager2/otpmgr_setup.php");
require_once(APPLICATION_DIR."otp_manager_secure.php");

include 'otpedfuncs.php';
$mysqli = openmysql();

/* change character set to utf8 */
if (!$mysqli->set_charset("utf8")) {
	printf("Error loading character set utf8: %s\n", $mysqli->error);
} else {
	//printf("Current character set: %s\n", $mysqli->character_set_name());
}

unset($_SESSION['sectionnav'])
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">
<link rel="stylesheet" href="otpedcss.css">

<script language="javascript">

	function showlangatts(langid) {
		//alert(langarr[langid]);
		document.getElementById("lid").value = langid;
		document.getElementById("addremlid").value = langid;
		document.getElementById("expxmllid").value = langid;
		
		document.getElementById("langatt_lname").value = langarr[langid][2];
		document.getElementById("langatt_sortord").value = langarr[langid][11];
		document.getElementById("langatt_datfile").value = langarr[langid][3];
		document.getElementById("langatt_contlabel").value = langarr[langid][5];
		document.getElementById("langatt_returnlabel").value = langarr[langid][6];
		document.getElementById("langatt_exitlabel").value = langarr[langid][7];
		document.getElementById("langatt_gallbutlab").value = langarr[langid][9];
		document.getElementById("langatt_gallnote").value = langarr[langid][10];
		document.getElementById("langatt_termsbutlab").value = langarr[langid][4];
		document.getElementById("langatt_termscont").value = langarr[langid][8];
		
	}
	
	function exportcolors() {
		
	}
	
</script>

</head>

<body>

<?php

if (isset($_POST['title'])) {
	$_SESSION['titleid'] = $_POST['title'];
}
if (isset($_POST['bid'])) {
	$_SESSION['brandid'] = $_POST['bid'];
}

/*if (isset($_SESSION['brandid'])) { //if coming here from addremlang.php, will pass brandid and titleid by SESSION
	$brandid = $_SESSION['brandid'];
	$titleid = $_SESSION['titleid'];
	
} else { //if coming here from editor-titles.php, will pass brandid and titleid by POST
	$titleid = $_POST['title'];
	$brandid = $_POST['bid'];
}*/

if ($result = $mysqli->query("SELECT * FROM languages WHERE title_id=" . $_SESSION['titleid'] . " ORDER BY language_id")) {
		
	echo "<script language='javascript'>";
		
	$langrow_cnt = $result->num_rows;
		
	echo "var rowcnt = " . $langrow_cnt . ";";
			
} else {
		
	echo "error: couldn't find languages in database here";
	exit;
}
	
echo "var langarr = {};";

$langarr = array();

for ($i = 0; $i < $langrow_cnt; $i++) {
	$thislang = $result->fetch_row();
	
	foreach ($thislang as $key=>$item) {
		$thislang[$key] = str_replace(chr(241),"&ntilde;",$thislang[$key]);
	}
	
	array_push($langarr, $thislang);
	echo "langarr[" . $thislang[0] . "] = " . json_encode($thislang) . ";";
}
	
	
//echo "alert('hello');";
echo "</script>";

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
	?>
</ul>
</section>

<section id="mainarea">

	<section id="controlbox">

		<p id="cb-head" class="boxhead">Select A Language</p>
		
		<section id="sec-sellangform" class="formarea">
			<p>Select a language and click Continue.</p>
			
			<form id="sellangform" class="otpeditor-form" name="sellangform" method="POST" action="editor-sections.php">
			
				<?

				$modrow_cnt = $langrow_cnt + 2;
				echo "<select class='otped-sel' name='lang' size='" . $modrow_cnt . "'>";
				
				for ($i = 0; $i < $langrow_cnt; $i++) {
					$thislang = $langarr[$i];
					
					echo "<option value='" . $thislang[0] . "' onClick='javascript:showlangatts($thislang[0]);'>" . $thislang[2] . "</option>";
					
				}
				echo "<option value='" . "dummy" . "' onClick='javascript:showlangatts(100);'>" . "    " . "</option>";
				echo "<option value='" . "dummy" . "' onClick='javascript:showlangatts(100);'>" . "    " . "</option>";
				echo "</select>"
					
				?>
				<br>
				<input type="hidden" name="fromlang" value="true">
				
				<button type="submit" form="sellangform" value="Submit">Continue</button>
			</form>
			
			<form id="addremlang" name="addremlang" action="addremlang.php" method="POST">
				<input type="hidden" id="addrembid" name="addrembid" value="<?php echo $_SESSION['brandid'];?>">
				<input type="hidden" id="addremtid" name="addremtid" value="<?php echo $_SESSION['titleid'];?>">
				<input type="hidden" id="addremlid" name="addremlid" value="">
				<button type="submit" name="addlang" value="addlang">Add a Language</button>
				<button type="submit" name="remlang" value="remlang">Remove Language</button>
				<button type="submit" name="duplang" value="duplang">Duplicate Language</button>
			</form>
				
			<form id="explangxml" name="explangxml" action="explangxml.php" method="POST">
				<input type="hidden" id="expxmllid" name="expxmllid" value="">
				<input type="hidden" id="expxmltid" name="expxmltid" value="<?php echo $_SESSION['titleid'];?>">
				<input type="hidden" id="expxmlbid" name="expxmlbid" value="<?php echo $_SESSION['brandid']?>">
			
				<p><button type="submit" value="explangxml">Export languages.xml File</button></p>
			
			</form>
			
		
		</section>


	</section>

	<section id="displaybox">

		<p id="dispbox-head">Language Attributes</p>

		<section class="attrbox" id="langattrbox">
			
			<form id="langatts" name="langatts" method="POST" action="savelangatts.php">
				<input type="hidden" name="lid" id="lid" value="">
				<input type="hidden" name="bid" id="bid" value="<?php echo $_SESSION['brandid']?>">
				<input type="hidden" name="tid" id="tid" value="<?php echo $_SESSION['titleid']?>">
			
				Language <input type="text" id="langatt_lname" name="lname" value="" onchange="javascript:this.value = "> <br>
				0. Sort Order <input type="text" id="langatt_sortord" name="sortord" value=""> <br>
				1. Data File <input type="text" id="langatt_datfile" name="datfile" value=""> <br>
				3. Continue Button Label <input type="text" id="langatt_contlabel" name="contlabel" value=""> <br>
				4. Return Button Label <input type="text" id="langatt_returnlabel" name="returnlabel" value=""> <br>
				5. Exit Button Label <input type="text" id="langatt_exitlabel" name="exitlabel" value=""> <br>
				5. Gallery Button Label <input type="text" id="langatt_gallbutlab" name="gallbutlab" value=""> <br>
				6. Gallery Pane Note <input type="text" id="langatt_gallnote" name="gallnote" value=""> <br>
				7. Terms Button Label <input type="text" id="langatt_termsbutlab" name="termsbutlab" value=""> <br>
				8. Terms Content <input type="text" id="langatt_termscont" name="termscont" value=""> <br>
				
				<button type="submit" form="langatts" value="Submit">Save Attributes</button><br>
				
				
			</form>
			
		</section>

	</section>
	
</section>

</body>

</html>