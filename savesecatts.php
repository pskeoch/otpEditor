<?php

session_start();

include 'otpedfuncs.php';
$mysqli = openmysql();

function mysqlsavewhere($tabnam,$wherecond,$attslist,$lastattr) {
	
	$qrystr = "UPDATE sections SET ";
	foreach ($attslist as $attr => $tupdate) {
		$qrystr .= $attr . "='";
		$tupdate = str_replace("'","\'",$tupdate);
		if ($attr == $lastattr) {
			$qrystr .= $tupdate . "'";
		} else {
			$qrystr .= $tupdate . "',";
		}
	}
	foreach ($wherecond as $attr => $cond) {
		$qrystr .= " WHERE " . $attr . "='" . $cond . "'";
	}
	//echo $qrystr;

	return $qrystr;
}


function savesection($mysqli) {
	$tsaveid = $_POST['sid'];
	$tlangid = $_POST['lang'];
	$tsecid = $_POST['tsecid'];
	
	$tsecttl = $_POST['sectitle'];
	$tseccont = $_POST['seccont'];
	$tpanetype =$_POST['panetype'];
	$tsortord = $_POST['sortord'];
	$ttechpubssec = $_POST['techpubssec'];
	
	$tparsecid = $_POST['parsecid']; //want parent section id to pass back to editor_sections
	
	$wherecond = array("section_id"=>$tsaveid);
	$attslist =  array("name"=>$tsecttl,
						"pane_type"=>$tpanetype,
						"copy"=>$tseccont,
						"sort_order"=>$tsortord);
	
	$qrystr = mysqlsavewhere('sections',$wherecond,$attslist,"sort_order");
	
	if ($result = $mysqli->query($qrystr)) {
		//echo $result;
		//now look at 'is tech pubs?' checkbox and update langid_techpubslookup table if checked
		if (isset($ttechpubssec)) {
			$qry = "SELECT techpubs_secid FROM langid_techpubslookup WHERE langid=" . $tlangid;
			echo $qry;
			if ($testtechpubsresult = $mysqli->query($qry)) {
				if ($testtechpubsresult->num_rows > 0) {
					$qry = "UPDATE langid_techpubslookup SET techpubs_secid=" . $tsaveid . " WHERE langid=" . $tlangid;
					echo $qry;
					if ($settechpubsresult = $mysqli->query($qry)) {
					}  else {
						$mysqli->close();	
						echo "error: couldn't find techpubs lookup to save in database";
						exit;
					}
				} else {
					$qry = "INSERT INTO langid_techpubslookup(langid,techpubs_secid) VALUES(" . $tlangid . "," . $tsaveid . ")";
					echo $qry;
					if ($settechpubsresult = $mysqli->query($qry)) {
					}  else {
						$mysqli->close();	
						echo "error: couldn't find techpubs lookup to save in database";
						exit;
					}
				}
			}  else {
				$mysqli->close();	
				echo "error: couldn't find techpubs lookup to save in database";
				exit;
			}
		}
		
		if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
				$uri = 'https://';
			} else {
				$uri = 'http://';
			}
		$uri .= $_SERVER['HTTP_HOST'];
		$_SESSION['tsec'] = $tsecid;
		$_SESSION['parsecid'] = $tparsecid;
		$_SESSION['selsecid'] = $tsaveid;
		$_SESSION['lang'] = $tlangid;
		$mysqli->close();
		header('Location: '.$uri.'/pubseditorhtml/editor-sections.php');	
	}  else {
			
		$mysqli->close();	
		echo "error: couldn't find section to save in database";
		exit;
	}	

	
}

function removesection($mysqli) {
	$tremid = $_POST['sid'];
	$tlangid = $_POST['lang'];
	$tsecid = $_POST['tsecid'];

	$tparsecid = $_POST['parsecid']; //want parent section id to pass back to editor_sections
	
	if ($result = $mysqli->query("DELETE FROM sections WHERE section_id='" . $tremid . "'")) {
		//echo $result;
		
		if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
				$uri = 'https://';
			} else {
				$uri = 'http://';
			}
		$uri .= $_SERVER['HTTP_HOST'];
		$_SESSION['tsec'] = $tsecid;
		$_SESSION['parsecid'] = $tparsecid;
		//$_SESSION['selsecid'] = "na";
		$_SESSION['lang'] = $tlangid;
		$mysqli->close();
		header('Location: '.$uri.'/pubseditorhtml/editor-sections.php');	
	}  else {
			
		$mysqli->close();	
		echo "error: couldn't find section to save in database";
		exit;
	}		
	
}


if (isset($_POST['savesec'])) {
	//echo "save";
	savesection($mysqli);
} else {
	removesection($mysqli);
	//echo "remove?";
}





?>