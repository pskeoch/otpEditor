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

	function showtitleatts(titleid) {
		document.getElementById("tid").value = titleid;
		document.getElementById("addremtid").value = titleid;
		document.getElementById("upltid").value = titleid;
		document.getElementById("upltname").value = titlearr[titleid][2];
		
		document.getElementById("titleatt_tname").value = titlearr[titleid][2];
		document.getElementById("titleatt_lnavttl1").value = titlearr[titleid][3];
		document.getElementById("titleatt_lnavttl2").value = titlearr[titleid][4];
		document.getElementById("titleatt_lnavttl3").value = titlearr[titleid][5];
		document.getElementById("titleatt_lnavttl4").value = titlearr[titleid][6];
		
		document.getElementById("displaybox-comp").style.display = "none";
		
	}
	
	function exportcolors() {
		
	}
	
	function showcompilatts(compobj) {
		//alert(compobj[0]);
		document.getElementById("displaybox-comp").style.display = "block";
		document.getElementById("compilatt_cname").value = compobj[1];
		document.getElementById("addremcompid").value = compobj[0];
		document.getElementById("compid").value = compobj[0];
		
		document.getElementById("ctitleatt_lnavttl1").value = compobj[3];
		document.getElementById("ctitleatt_lnavttl2").value = compobj[4];
		document.getElementById("ctitleatt_lnavttl3").value = compobj[5];
		document.getElementById("ctitleatt_lnavttl4").value = compobj[6];
		
		document.getElementById("uplcid").value = compobj[0];
		document.getElementById("uplcname").value = compobj[1];
	}
	
	
</script>

</head>

<body>

<?php

//echo $_POST['brand'];

if (isset($_POST['brand'])) { //if coming here from editor.php, will pass brandid by POST
	$brandid = $_POST['brand'];
	$_SESSION['brandid'] = $brandid;
} else { //if coming here from addremtitle.php, will pass brandid by SESSION
	$brandid = $_SESSION['brandid'];
}
//echo $brandid;
//echo $_SESSION['brandid'];

if ($result = $mysqli->query("SELECT * FROM titles WHERE brand_id=" . $brandid . " ORDER BY title_id")) {
		
	echo "<script language='javascript'>";
		
	$row_cnt = $result->num_rows;
		
	echo "var rowcnt = " . $row_cnt . ";";
			
} else {
		
	echo "error: couldn't find titles in database here";
	exit;
}
	
echo "var titlearr = {};";
	
for ($i = 0; $i < $row_cnt; $i++) {
	$thistitle = $result->fetch_row();
		
	echo "titlearr[" . $thistitle[0] . "] = " . json_encode($thistitle) . ";";
}
	
echo "</script>";

?>

<section id="topnavbar">
<ul id="topnavlist">
	<?php 
	if ($result = $mysqli->query("SELECT brand_name FROM brands WHERE brand_id=" . $brandid)) {
		$row_cnt = $result->num_rows;
		if ($row_cnt>0) {
			$brandnam = $result->fetch_row();
		}
	} else {
		echo "error: couldn't find titles in database";
		exit;
	}
	
	echo "<li>" . "///<a href='editor.php' target='_self'>" . $GLOBALS['industrydirs'][1] . "</a>" . "</li>";
	echo "<li>" . "///<a href='editor-titles.php' target='_self'>" . $brandnam[0] . "</a>" . "</li>";	?>
</ul>
</section>

<section id="mainarea">

	<section id="controlbox">

		<p id="cb-head" class="boxhead">Select A Title</p>
		
		<section id="sec-seltitleform" class="formarea">
			<p>Select a title and click Continue. Double-click to edit.</p>
			
			<form id="seltitleform" class="otpeditor-form" name="seltitleform" method="POST" action="editor-langs.php">
			
			<?
			
			#find number of titles from mysql table
			if ($result = $mysqli->query("SELECT * FROM titles WHERE brand_id=" . $brandid . " ORDER BY title_name")) {
				
				$row_cnt = $result->num_rows;
				
			} else {
			
				echo "error: couldn't find titles in database";
				exit;
			}
			
			
			
			echo "<select class='otped-sel' name='title' size='10'>";
			
			for ($i = 0; $i < $row_cnt; $i++) {
				$thistitle = $result->fetch_row();
				
				echo "<option value='" . $thistitle[0] . "' onClick='javascript:showtitleatts($thistitle[0]);'>" . $thistitle[2] . "</option>";
				
			}
			echo "</select>";
			
				
			?>
			<br>
			
			<input type="hidden" name="bid" id="bid" value="<?php echo $brandid?>">
			<button type="submit" form="seltitleform" value="Submit">Continue</button>
			
			</form>
			
			<form name="addremtitle" id="addremtitle" action="addremtitle.php" method="POST">
				<input type="hidden" name="addremtid" id="addremtid" value="">
				<input type="hidden" name="addrembid" id="addrembid" value="<?php echo $brandid?>">
				<button type="submit" name="addtitle" value="addtitle">Add a Title</button>
				<button type="submit" name="remtitle" value="remtitle">Remove Title</button>
			</form>
			
			<p>
			<p class="boxhead">Select a Compilation</p>
			<form name="selectcompil" id="selectcompil" action="editor-compils.php" method="POST">
				<?
				
				#find number of titles from mysql table
				if ($result = $mysqli->query("SELECT * FROM compilations WHERE brandid='" . $brandid . "' ORDER BY brandid")) {
					
					$row_cnt = $result->num_rows;
					
				} else {
					echo "SELECT * FROM compil WHERE brand_id=" . $brandid . " ORDER BY brand_id";
					echo "error: couldn't find compilations in database";
					echo "Errno: " . $mysqli->errno . "\n";
					echo "Error: " . $mysqli->error . "\n";
					exit;
				}
				
				
				
				echo "<select class='otped-sel' name='compil' size='10'>";
				
				for ($i = 0; $i < $row_cnt; $i++) {
					$thiscompil = $result->fetch_row();
					$thiscompiljs = json_encode($thiscompil);
					echo "<option value='" . $thiscompil[0] . "' onClick='javascript:showcompilatts(" . $thiscompiljs . ");'>" . $thiscompil[1] . "</option>";
					
				}
				echo "</select>";
				
				$mysqli->close();
					
				?>
				<input type="hidden" name="bid" id="bid" value="<?php echo $brandid?>"><br>
				<button type="submit" form="selectcompil" value="Submit">Continue</button>
			</form>
			
			<form name="addremcompil" id="addremcompil" action="addremcompil.php" method="POST">
				<input type="hidden" name="addremcompid" id="addremcompid" value="">
				<input type="hidden" name="addrembid" id="addrembid" value="<?php echo $brandid?>">
				<button type="submit" name="addcompil" value="addcompil">Add a Compilation</button>
				<button type="submit" name="remcompil" value="remcompil">Remove Compilation</button>
			</form>
			</p>
			
		
		</section>
		
		


	</section>

	<section id="displaybox">

		<p id="dispbox-head">Title Attributes</p>

		<section class="attrbox" id="titleattrbox">
			
			<form id="titleatts" name="titleatts" method="POST" action="savetitleatts.php">
				<input type="hidden" name="tid" id="tid" value="">
				<input type="hidden" name="bid" id="bid" value="<?php echo $brandid;?>">
			
				Title <input type="text" id="titleatt_tname" name="tname" value="" onchange="javascript:this.value = "> <br>
				1. Left Nav Title Line 1 <input type="text" id="titleatt_lnavttl1" name="lnavttl1" value=""> <br>
				2. Left Nav Title Line 2 <input type="text" id="titleatt_lnavttl2" name="lnavttl2" value=""> <br>
				3. Left Nav Title Line 3 <input type="text" id="titleatt_lnavttl3" name="lnavttl3" value=""> <br>
				4. Left Nav Title Line 4 <input type="text" id="titleatt_lnavttl4" name="lnavttl4" value=""> <br>
				
				<button type="submit" form="titleatts" value="Submit">Save Attributes</button><br>
			</form>
			
			<br>
			<br>
			<form id="uplimages" name="uplimages" method="POST" action="uploadimages.php"  enctype="multipart/form-data">
				<label for="files">Upload Title Images</label>
				<input type="file" id="files" name="files[]" multiple><br>
				<input type="hidden" id="uplbid" name="uplbid" value="<?php echo $brandid;?>">
				<input type="hidden" id="upltid" name="upltid" value="">
				<input type="hidden" id="upltname" name="upltname" value="">
				<input type="submit">
			</form>
				
				<p id="uplimages-instruct">
					<b>intro.png</b> (750x200px)<br>
					<b>leftNav_bottom.png</b> (240x70px)<br>
					<b>leftNav_top.png</b> (240x70px)<br><br>
					
					Background images numbered in pairs as:
					<b>bg_[number].jpg</b> (750x500px)<br>
					<b>bg_[number]_thumb.jpg</b> (54x54px)<br>
					<b>bg_1.jpg</b><br>
					<b>bg_1_thumb.jpg</b>
				
				</p>
				
				
				<p>After uploading, the backgrounds XML file will be automatically created in the relevant directory</p>	
			
		</section>

	</section>
	
	<section id="displaybox-comp">
	
		<p id="dispbox-head">Compilation Attributes</p>
		
		<section class="attrbox" id="compilattrbox">
		
			<form id="compilatts" name="compilatts" method="POST" action="savecompilatts.php">
				<input type="hidden" name="compid" id="compid" value="">
				<input type="hidden" name="bid" id="bid" value="<?php echo $brandid;?>">
				Compilation title <input type="text" id="compilatt_cname" name="cname" value="" onchange="javascript:this.value = "> <br>
				1. Left Nav Title Line 1 <input type="text" id="ctitleatt_lnavttl1" name="lnavttl1" value=""> <br>
				2. Left Nav Title Line 2 <input type="text" id="ctitleatt_lnavttl2" name="lnavttl2" value=""> <br>
				3. Left Nav Title Line 3 <input type="text" id="ctitleatt_lnavttl3" name="lnavttl3" value=""> <br>
				4. Left Nav Title Line 4 <input type="text" id="ctitleatt_lnavttl4" name="lnavttl4" value=""> <br>
				
				<button type="submit" form="compilatts" value="Submit">Save Attributes</button><br>
			</form>
			
			
			<br>
			<br>
			<form id="uplimages" name="uplimages" method="POST" action="uploadimages.php"  enctype="multipart/form-data">
				<label for="files">Upload Title Images</label>
				<input type="file" id="files" name="files[]" multiple><br>
				<input type="hidden" id="uplbid" name="uplbid" value="<?php echo $brandid;?>">
				<input type="hidden" id="uplcid" name="uplcid" value="">
				<input type="hidden" id="uplcname" name="uplcname" value="">
				<input type="submit">
			</form>
				
				<p id="uplimages-instruct">
					<b>intro.png</b> (750x200px)<br>
					<b>leftNav_bottom.png</b> (240x70px)<br>
					<b>leftNav_top.png</b> (240x70px)<br><br>
					
					Background images numbered in pairs as:
					<b>bg_[number].jpg</b> (750x500px)<br>
					<b>bg_[number]_thumb.jpg</b> (54x54px)<br>
					<b>bg_1.jpg</b><br>
					<b>bg_1_thumb.jpg</b>
				
				</p>
				
				
				<p>After uploading, the backgrounds XML file will be automatically created in the relevant directory</p>

		</section>
	
	</section>
	
	
</section>

</body>

</html>