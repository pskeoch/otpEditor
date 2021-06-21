<?php

include 'otpedfuncs.php';
$mysqli = openmysql();

//Go through all language ids, continue only with English language ones, identify the Wallpaper section id
//Create record in title_wallpaperlookup table

$qry = "SELECT language_id, language FROM languages";
if ($result = $mysqli->query($qry)) {
	
	while ($languageinfo = $result->fetch_assoc()) {
		
		if (substr($languageinfo["language"],0,3)=="Eng") {
			echo $languageinfo["language_id"] . ":::" . substr($languageinfo["language"],0,3);
			
			$qry = "SELECT section_id, name FROM sections WHERE parent_id=" . $languageinfo["language_id"] . " AND parent_is_language_id=1";
			if ($sectionresult = $mysqli->query($qry)) {
				echo "::processed section find query";
				
				while ($sectioninfo = $sectionresult->fetch_assoc()) {
					echo "::section: " . $sectioninfo["name"];
				
					if (substr($sectioninfo["name"],0,10) == "Wallpapers" OR substr($sectioninfo["name"],0,9) == "Downloads") {
						echo ":::" . "wallpaper section found";
						//Now need to find the title id for this language id
						$qry = "SELECT title_id FROM languages WHERE language_id=" . $languageinfo["language_id"];
						if ($titleresult = $mysqli->query($qry)) {
							$titleinfo = $titleresult->fetch_assoc();
							
							echo ":::titleid:" . $titleinfo["title_id"];
							
							$qry = "INSERT INTO title_wallpaperlookup(title_id,wallpaper_sectionid) VALUES(" . $titleinfo["title_id"] . "," . $sectioninfo["section_id"] . ")";
							if ($titleresult = $mysqli->query($qry)) {
								#echo $titleinfo["title_id"] . "||" . $sectioninfo["section_id"];
							} else {
								echo "error " . $qry;
							}
							
						} else {
							echo "error " . $qry;
						}

					}
				}
				
			} else {
				echo "error " . $qry;
			}
			
		} else {
		}
		echo "<br>";
	}
	
} else {
	echo "error " . $qry;
}

?>