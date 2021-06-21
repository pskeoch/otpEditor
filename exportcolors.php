<?php

include 'otpedfuncs.php';
$mysqli = openmysql();

$tbrandid = $_POST['expbid'];

if ($tbrandid=="") { //Check if a brand was selected in editor.php
//echo $tbrandid . "is brandid";
	
} else {

	if ($result = $mysqli->query("SELECT * FROM brands WHERE brand_id=" . $tbrandid)) {
		
		$row_cnt = $result->num_rows;
		if ($row_cnt==1) {
			
			$tcolors = $result->fetch_assoc();
			$colorsstr = "";
			
			//Associative array matching colors.txt variable names with mysql table names, colors.txt on left, mysql on right
			$colorattlist = Array("htmlColorBg"=>"htmlBgColor",
								"bgColorValue"=>"introBgColor",
								"centerImgBgColorValue"=>"introImgBgColor",
								"langPaneBgColorValue"=>"introLangPaneBgColor",
								"introBtnBgColor"=>"introBtnBgColor",
								"introBtnLabelColor"=>"introBtnLabelColor",
								"leftPaneColorValue"=>"leftPaneColor",
								"rightPaneColorValue"=>"rightPaneColor",
								"galleryPaneColorValue"=>"galleryPaneColor",
								"leftNavBtnColorValue"=>"leftNavBtnColor",
								"leftNavBtnOnColorValue"=>"leftNavBtnOnColor");
								
			foreach($colorattlist as $key=>$colitem) {
				$tvalue = $tcolors[$colitem];
				if ($tvalue=="") {
					$colorsstr .= $key . "=#&";
				} else {
					$colorsstr .= $key . "=0x" . $tvalue . "&";
				}
			}
			
			$colorsstr .= "done=1";
			
		} else {
			echo "no or duplicate results for brand id" . $tbrandid;
		}
		
	} else {
		echo "Couldn't run query in brands table";
	}

	//now write to colors.txt file in appropriate directory
	//first find brand_name for brand_id
	
	$targdir = builddirref_sel([$tcolors["brand_name"]],1,0);
	if (file_exists($targdir)) {
		//echo $targdir . "/colors.txt";
		$handle = fopen($targdir . "/colors.txt", "w");
	} else {
		$targdir = builddirref_add([$tbrandid],1);
		$handle = fopen($targdir . "/colors.txt", "w");
	}	

	fwrite($handle, $colorsstr);
	fclose($handle);
}

gotourl('/pubseditorhtml/editor.php');


?>