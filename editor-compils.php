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


function showaddedtitle(titleid,chkboxid) {
	//alert(titleid);
	var chkbox = document.getElementById(chkboxid);
	var disparea = document.getElementById("comptitles");
	
	if (chkbox.checked == true){
		ttlboxhtml = document.createElement("section");
		ttlboxhtml.setAttribute("id","ttlbox" + titleid);
		ttlboxhtml.setAttribute("class","compillangbox");
		ttltxt = document.createElement("p");
		ttltxt.setAttribute("class","compillangbox-h");
		ttltxt.innerHTML = chkboxid;
		ttlboxhtml.appendChild(ttltxt);
		
		//alert(langarr[titleid]);
		for (i=0;i<langarr[titleid].length;i++) {
			tlang = langarr[titleid][i];
			
			langchk = document.createElement("input");
			langchk.setAttribute("type","checkbox");
			langchk.setAttribute("class","chkbox");
			langchk.setAttribute("name",titleid + "_langinc_" + tlang[1]);
			langchk.setAttribute("value","1");
			langchk.setAttribute("id",titleid + "_langinc_" + tlang[1]);
			langchk.setAttribute("checked","checked");
			ttlboxhtml.appendChild(langchk);
			langchklab = document.createElement("label");
			langchklab.setAttribute("for","langinc" + tlang[3]);
			
			langchklab.innerHTML = tlang[3] + "&nbsp&nbsp|&nbsp&nbsp";
			ttlboxhtml.appendChild(langchklab);
			
			//seceditlnkp = document.createElement("p");
			seceditlnka = document.createElement("a");
			seceditlnka.setAttribute("href","javascript:openseceditor(" + tlang[0] + ")");
			seceditlnka.innerHTML = "Edit sections";
			//seceditlnkp.appendChild(seceditlnka);
			ttlboxhtml.appendChild(seceditlnka);
			
			brkit = document.createElement("br");
			ttlboxhtml.appendChild(brkit);
		}
		
		ttlref = document.createElement("input");
		ttlref.setAttribute("type","hidden");
		ttlref.setAttribute("name",chkboxid);
		ttlref.setAttribute("value",titleid);
		disparea.appendChild(ttlref);
		
		disparea.appendChild(ttlboxhtml);

	} else {
		document.getElementById("ttlbox" + titleid).remove();
	}
}

function showtmpltattrs(titleid) {
	var disparea = document.getElementById("tmpltdisp");
	disparea.textContent = "";
	for (i=0;i<langarr[titleid].length;i++) {
		tlang = langarr[titleid][i];
		langchk = document.createElement("input");
		langchk.setAttribute("type","checkbox");
		langchk.setAttribute("class","chkbox");
		langchk.setAttribute("name","tmplt_langinc_" + tlang[1]);
		langchk.setAttribute("value","1");
		langchk.setAttribute("id",titleid + "_langinc_" + tlang[1]);
		langchk.setAttribute("checked","checked");
		disparea.appendChild(langchk);
		langchklab = document.createElement("label");
		langchklab.setAttribute("for","langinc" + tlang[3]);
		langchklab.innerHTML = tlang[3];
		disparea.appendChild(langchklab);
		brkit = document.createElement("br");
		disparea.appendChild(brkit);
	}
}

function seceditor_getmainsecs(langid) {
	alert(langid);
}

function secedit_nextseclev(secid,secname) {
	var xhttp = new XMLHttpRequest();
	//alert(secid);
	//alert(secname);
	//alert("helo");
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
		
			secarrtxt = this.responseText;
			//alert(secarrtxt);
			secarr = JSON.parse(secarrtxt);	
			
			secselcolumn = document.createElement("section");
			secselcolumn.setAttribute("class","secedit-secselcol");
			secselcolumn_ident = document.createElement("p");
			secselcolumn_ident.setAttribute("class","secselcolumn-ident");
			secselcolumn_ident.innerHTML = secname;
			secselcolumn.appendChild(secselcolumn_ident);
			
			for (i=0;i<secarr.length;i++) {
				secselopt = document.createElement("input");
				secselopt.setAttribute("type","checkbox");
				secselopt.setAttribute("id","checkbox" + i);
				secselopt.setAttribute("class","secedit-checkbox");
				secselopt.setAttribute("name","secremovechks[]");
				secselopt.setAttribute("value",secarr[i][0]);
				if (compsecexls.indexOf(secarr[i][0].toString()) != -1) {
					secselopt.checked = true;
				}
				secsellab = document.createElement("label");
				secsellab.setAttribute("for","checkbox" + i);
				secsellab.innerHTML = "<a href='javascript:secedit_nextseclev(" + secarr[i][0] + ",\"" + secarr[i][1] + "\")'>" + secarr[i][1] + "</a>";
				
				secselcolumn.appendChild(secselopt);
				secselcolumn.appendChild(secsellab);
				breaktag = document.createElement("br");
				secselcolumn.appendChild(breaktag);
			}
			

			document.getElementById("seceditform").appendChild(secselcolumn);
			//document.getElementById("compilsectioneditor").style.display = "block";
		
		}
	};
	
	//alert(secid);
	xhttp.open("GET", "compilsecedit_fetchsubsecs.php?secid=" + secid, true);
	xhttp.send();
}

function openseceditor(langid) {
	//alert(compsecexls);
	var xhttp = new XMLHttpRequest();
	//alert("helo");
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			
			secarrtxt = this.responseText;
			//alert(langarrtxt);
			secarr = JSON.parse(secarrtxt);
			//alert(langarr);
			
			secselcolumn = document.createElement("section");
			secselcolumn.setAttribute("class","secedit-secselcol");
			secselintrotxt = document.createElement("p");
			secselintrotxt.setAttribute("id","secselintrotxt");
			secselintrotxt.innerHTML = "Select sections to be excluded from compilation";
			
			
			for (i=0;i<secarr.length;i++) {
				secselopt = document.createElement("input");
				secselopt.setAttribute("type","checkbox");
				secselopt.setAttribute("id","checkbox" + i);
				secselopt.setAttribute("class","secedit-checkbox");
				secselopt.setAttribute("name","secremovechks[]");//secarr[i][1]);
				secselopt.setAttribute("value",secarr[i][0]);
				if (compsecexls.indexOf(secarr[i][0].toString()) != -1) {
					secselopt.checked = true;
				}
				
				secsellab = document.createElement("label");
				secsellab.setAttribute("for","checkbox" + i);
				secsellab.innerHTML = "<a href='javascript:secedit_nextseclev(" + secarr[i][0] + ",\"" + secarr[i][1] + "\")'>" + secarr[i][1] + "</a>";
				
				secselcolumn.appendChild(secselopt);
				secselcolumn.appendChild(secsellab);
				breaktag = document.createElement("br");
				secselcolumn.appendChild(breaktag);
			}
			//alert("done loop");
			
			document.getElementById("seceditform").appendChild(secselintrotxt);
			document.getElementById("seceditform").appendChild(secselcolumn);
			document.getElementById("compilsectioneditor-container").style.display = "block";
			//alert("done all");
		}
	};
	xhttp.open("GET", "compilsecedit_fetchmainsecs.php?langid=" + langid, true);
	xhttp.send();
}

function closeseceditor() {
	document.getElementById("compilsectioneditor-container").style.display = "none";
	document.getElementById("seceditform").innerHTML = "<button type=\"submit\" form=\"seceditform\" value=\"Submit\">Update section edit</button>";
}

</script>

</head>

<body>

<?php

//echo $_SESSION['compilid'];

if (isset($_POST['compil'])) {
	$_SESSION['compilid'] = $_POST['compil'];
}
if (isset($_POST['bid'])) {
	$_SESSION['brandid'] = $_POST['bid'];
}

//build javascript reference database containing languages in each title
echo "<script language='javascript'>";
echo "var langarr = {};";

$langarr = array();

if ($result = $mysqli->query("SELECT title_id, title_name FROM titles WHERE brand_id=" . $_SESSION['brandid'] . " ORDER BY title_id")) {
					$row_cnt = $result->num_rows;
					if ($row_cnt>0) {
						for ($j = 0; $j < $row_cnt; $j++) {
							$titlearr = $result->fetch_row();

							if ($langresult = $mysqli->query("SELECT language_id, language, langlookupid FROM languages WHERE title_id=" . $titlearr[0] . " ORDER BY language_id")) {	
								$langrow_cnt = $langresult->num_rows;
								//echo "var rowcnt = " . $langrow_cnt . ";";
								
								echo "langarr[" . $titlearr[0] . "] = [];";
								
								for ($i = 0; $i < $langrow_cnt; $i++) {
									$thislang = $langresult->fetch_row();
									
									if ($langlookresult = $mysqli->query("SELECT language FROM language_lookup WHERE langid=" . $thislang[2])) {	
										$langlookrow_cnt = $langlookresult->num_rows;
										$thislanglookup = $langlookresult->fetch_row();
										
									} else {
										echo "error: couldn't lookup languages in database here";
										exit;
									}
									$thislang[3] = $thislanglookup[0];
									
									array_push($langarr, $thislang);
									
									echo "langarr[" . $titlearr[0] . "].push(" . json_encode($thislang) . ");";
								}
								
							} else {
								echo "error: couldn't find languages in database here";
								exit;
							}
						}
					}
}


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
	if ($result = $mysqli->query("SELECT compname,compsecexempt FROM compilations WHERE compid=" . $_SESSION['compilid'])) {
		$row_cnt = $result->num_rows;
		if ($row_cnt>0) {
			$compdeets = $result->fetch_assoc();
		}
	} else {
		echo "error: couldn't find compilation title in database";
		exit;
	}
	
	echo "<li>" . "///<a href='editor.php' target='_self'>" . $GLOBALS['industrydirs'][1] . "</a>" . "</li>";
	echo "<li>" . "///<a href='editor-titles.php' target='_self'>" . $brandnam[0] . "</a>" . "</li>";
	echo "<li>" . "///<a href='editor-langs.php' target='_self'>" . $compdeets["compname"] . "</a>" . "</li>";
	
	//get exempt sections list from database and convert to js list
	$compsecexls = explode(",",$compdeets["compsecexempt"]);
	echo "<script language='javascript'>";
	echo "var compsecexls = " . json_encode($compsecexls) . ";";
	echo "</script>";
	
	
	?>
</ul>
</section>

<section id="mainarea">

	<section id="controlbox">

		<p id="cb-head" class="boxhead">Select Titles in Compilation</p>
		
		<section id="sec-selcompilform" class="formarea">
			<p>Check all titles to feature in compilation:</p>
			
			<form id="selcompilform" class="otpeditor-form" name="selcompilform" method="POST" action="savecompilconfig.php">
			
				<?php
				
				$curcomptitles = array();
				
				if ($result = $mysqli->query("SELECT title_id, title_name FROM titles WHERE brand_id=" . $_SESSION['brandid'] . " ORDER BY title_id")) {
					$row_cnt = $result->num_rows;
					if ($row_cnt>0) {
						for ($j = 0; $j < $row_cnt; $j++) {
							$titlearr = $result->fetch_row();
							//var_dump($titlearr);
							
							#look up compilation title list to fill in checked boxes
							$qry = "SELECT comptitle_ref FROM comptitles WHERE titleid=" . $titlearr[0] . " AND compid=" . $_SESSION['compilid'];
							if ($compmatchresult = $mysqli->query($qry)) {
								if ($compmatchresult->num_rows > 0) {
									echo "<input type='checkbox' class='chkbox' id='" . $titlearr[1] . "' name='" . $titlearr[0] . "' value='" . $titlearr[1] . "' onClick=\"showaddedtitle(" . $titlearr[0] . ",'" . $titlearr[1] . "');\" checked>";
									$curcomptitles[] = array($titlearr[0],$titlearr[1]);
								} else {
									echo "<input type='checkbox' class='chkbox' id='" . $titlearr[1] . "' name='" . $titlearr[0] . "' value='" . $titlearr[1] . "' onClick=\"showaddedtitle(" . $titlearr[0] . ",'" . $titlearr[1] . "');\">";
								}
							} else {
								echo "error: couldn't find compilation title cross-references in database";
								exit;
							}
							echo "<label for='" . $titlearr[1] . "'>" . $titlearr[1] . "</label><br>";
						}
						
					}
				} else {
					echo "error: couldn't find titles in database";
					exit;
				}
					
				
				echo "<br>";
				
				echo "<button type='submit' form='selcompilform' value='Submit'>Update compilation</button>";
			echo "</form>";
			
			?>
		
		</section>


	</section>
	
	<section id="displaybox">

		<p id="dispbox-head">Compilation setup</p>
		
		<form id="compconfig" name="compconfig" action="buildcompil.php" method="POST">
			<section id="comptmpltsel">
				<p>Select template title for compilation:</p>
				
				<?php
				if ($result = $mysqli->query("SELECT title_id, title_name FROM titles WHERE brand_id=" . $_SESSION['brandid'] . " ORDER BY title_id")) {
					$row_cnt = $result->num_rows;
					if ($row_cnt>0) {
						$titlearr = $result->fetch_row();
						echo "<select id='comptmplt' name='comptmplt' onChange='showtmpltattrs(this.value);' value='" . $titlearr[0] . "'>";
						echo "<option value=" . $titlearr[0] . ">" . $titlearr[1] . "</option>";
						
						for ($j = 0; $j < $row_cnt; $j++) {
							$titlearr = $result->fetch_row();
							echo "<option value=" . $titlearr[0] . ">" . $titlearr[1] . "</option>";
						}
						
					}
				} else {
					echo "error: couldn't find titles in database";
					exit;
				}
				?>
				
				</select>
				<button type="submit" form="compconfig" value="Submit">Build compilation</button>
				
				<section id="tmpltdisp">
				</section>
				
			</section>
			
			<hr>
			
			<section id="comptitles">
			<p>Select languages to feature in compilation</p>
			</section>
		</form>
			
	</section>
	
	
</section>

<section id="compilsectioneditor-container">
	
	<section id="compilsectioneditor">
		
		<p id="seceditclose-but"><a href="javascript:closeseceditor()">Close section editor</a></p>
		
		<form id="seceditform" name="seceditform" action="compilsecedit_update.php" method="POST">
			<button type="submit" form="seceditform" value="Submit">Update section edit</button>
		</form>
	
	</section>
	
	
</section>

<?php
			echo "<script language='javascript'>";
			
			foreach($curcomptitles as $tcomptitle) {
				echo "showaddedtitle(" . $tcomptitle[0] . ",'" . $tcomptitle[1] . "');";
			}
			echo "</script>";
?>

</body>

</html>