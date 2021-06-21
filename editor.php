<?php

require("../OTPManager2/otpmgr_setup.php");
require_once(APPLICATION_DIR."otp_manager_secure.php");
include 'otpedfuncs.php';
$mysqli = openmysql();

unset($_SESSION['sectionnav'])

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">
<link rel="stylesheet" href="otpedcss.css">

<script language="javascript">

	function showbrandatts(brandid) {
		document.getElementById("bid").value = brandid;
		document.getElementById("expbid").value = brandid;
		document.getElementById("addrembid").value = brandid;
		
		document.getElementById("brandatt_bname").value = brandarr[brandid][2];
		document.getElementById("brandatt_introback").value = brandarr[brandid][4];
		document.getElementById("brandatt_introhorz").value = brandarr[brandid][5];
		document.getElementById("brandatt_introlang").value = brandarr[brandid][6];
		document.getElementById("brandatt_introbuts").value = brandarr[brandid][7];
		document.getElementById("brandatt_introbutlabs").value = brandarr[brandid][8];
		document.getElementById("brandatt_leftnavpan").value = brandarr[brandid][9];
		document.getElementById("brandatt_rightpan").value = brandarr[brandid][10];
		document.getElementById("brandatt_gallerypan").value = brandarr[brandid][11];
		document.getElementById("brandatt_leftnavbut").value = brandarr[brandid][12];
		document.getElementById("brandatt_leftnavbuthov").value = brandarr[brandid][13];
		
	}
	
	function exportcolors() {
		
	}
	
	function addBrand() {
		
	}
	
</script>

<?php

	if ($result = $mysqli->query("SELECT * FROM brands ORDER BY brand_id")) {
		
		echo "<script language='javascript'>";
		
		$row_cnt = $result->num_rows;
		
		echo "var rowcnt = " . $row_cnt . ";";
			
	} else {
		
		echo "error: couldn't find brands in database";
		exit;
	}
	
	echo "var brandarr = {};";
	
	for ($i = 0; $i < $row_cnt; $i++) {
		$thisbrand = $result->fetch_row();
		
		echo "brandarr[" . $thisbrand[0] . "] = " . json_encode($thisbrand) . ";";
	}
	
	echo "</script>";

?>


</head>

<body>

<section id="topnavbar">
<ul id="topnavlist">
	<?php echo "<li>" . "///<a href='editor.php' target='_self'>" . $GLOBALS['industrydirs'][1] . "</a>" . "</li>" ?>
</ul>
</section>

<section id="mainarea">

	<section id="controlbox">
		<p id="cb-head" class="boxhead">Select A Brand</p>
		
		<section id="sec-selbrandform" class="formarea">
			<p>Select a brand and click Continue.</p>
			
			<form id="selbrandform" class="otpeditor-form" name="selbrandform" method="POST" action="editor-titles.php">
			
			<?
			
			#find number of brands from mysql table
			if ($result = $mysqli->query("SELECT * FROM brands ORDER BY brand_id")) {
				
				$row_cnt = $result->num_rows;
				
			} else {
			
				echo "error: couldn't find brands in database";
				exit;
			}
			
			
			
			echo "<select class='otped-sel' name='brand' size='" . $row_cnt . "'>";
			
			for ($i = 0; $i < $row_cnt; $i++) {
				$thisbrand = $result->fetch_row();
				
				echo "<option value='" . $thisbrand[0] . "' onClick='javascript:showbrandatts($thisbrand[0]);'>" . $thisbrand[2] . "</option>";
				
			}
			echo "</select>";
			
			$mysqli->close();
				
			?>
			<br>
			<button type="submit" form="selbrandform" value="Submit">Continue</button>
			
			</form>
			
			<form name="addrembrand" id="addrembrand" action="addrembrand.php" method="POST">
				<input type="hidden" name="addrembid" id="addrembid" value="">
				<button type="submit" name="addbrand" value="addbrand">Add a Brand</button>
				<button type="submit" name="rembrand" value="rembrand">Remove Brand</button>
			</form>
			
		
		</section>


	</section>

	<section id="displaybox">

		<p id="dispbox-head">Brand Attributes</p>

		<section id="brandattrbox">
			
			<form id="brandatts" name="brandatts" method="POST" action="savebrandatts.php">
				<input type="hidden" name="bid" id="bid" value="">
			
				Brand Name <input type="text" id="brandatt_bname" name="bname" value="" onchange="javascript:this.value = "> <br>
				1. Intro Background <input type="text" id="brandatt_introback" name="introback" value=""> <br>
				2. Intro Horizontal <input type="text" id="brandatt_introhorz" name="introhorz" value=""> <br>
				3. Intro Language Pane <input type="text" id="brandatt_introlang" name="introlang" value=""> <br>
				4. Intro Buttons <input type="text" id="brandatt_introbuts" name="introbuts" value=""> <br>
				5. Intro Button Labels <input type="text" id="brandatt_introbutlabs" name="introbutslab" value=""> <br>
				6. Left Nav Pane <input type="text" id="brandatt_leftnavpan" name="leftnavpan" value=""> <br>
				7. Right Pane <input type="text" id="brandatt_rightpan" name="rightpan" value=""> <br>
				8. Gallery Pane <input type="text" id="brandatt_gallerypan" name="gallerypan" value=""> <br>
				9. Left Nav Button <input type="text" id="brandatt_leftnavbut" name="leftnavbut" value=""> <br>
				10. Left Nav Button Over <input type="text" id="brandatt_leftnavbuthov" name="leftnavbuthov" value=""> <br>
				
				<button type="submit" form="brandatts" value="Submit">Save Attributes</button>
				
			</form>
			
			<form name="expcolors" id="expcolors" action="exportcolors.php" method="POST">
				<input type="hidden" name="expbid" id="expbid" value="">
				<button type="submit" value="export">Export Colors File</button>
			</form>
			
			
		</section>

	</section>
	
</section>


<!--
<hr>
<hr>
<hr>
<section>
<h1>Test editor xml output (jtp1001 English) = title id 68, lang id 46</h1>

<form id="outmainxml" name="outmainxml" action="outmainxml_oop.php" method="POST">
	<input type="hidden" name="langtitlid" value="46">
	<button type="submit" form="outmainxml" value="Submit">Output XML</button>
</form>

</section>-->

</body>


</html>