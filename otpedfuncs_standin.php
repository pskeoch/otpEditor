<?php

$GLOBALS['pubsrootdir'] = "../otpubs";
$GLOBALS['industrydirs'] = array(1=>"Automobile");
$GLOBALS['xmldir'] = array("assets","xml");
$GLOBALS['picsdir'] = array("assets","pics");
$GLOBALS['pubspath'] = array("assets", "publications");
$GLOBALS['defaultlangcode'] = "new";

function openmysql() {
	
	$username = '<dbuser>';
	$pasz = '<dbuserpassword>';
	$database = '<dbname>';

	$mysqli = new mysqli('localhost',$username,$pasz,$database);

	if ($mysqli->connect_errno) {

		echo "Error: Failed to make a MySQL connection, here is why: \n";
		echo "Errno: " . $mysqli->connect_errno . "\n";
		echo "Error: " . $mysqli->connect_error . "\n";
		
		exit;
	}
	
	/* change character set to utf8 */
	if (!$mysqli->set_charset("utf8")) {
		printf("Error loading character set utf8: %s\n", $mysqli->error);
		exit();
	} else {
		//printf("Current character set: %s\n", $mysqli->character_set_name());
	}
	
	return $mysqli;
}

function mysqlselect_encconv($varlist,$enctype,$table,$condvar,$cond,$ord) { //function to iteratively build mysql query for conversion of mysql database data to another character encoding (mainly utf8) before obtaining
//returns query string. $tmysql -> mysqli object, $varlist -> list of table fields to address in query, $enctype -> character encoding type (e.g. 'utf8')
//$table -> mysql table name, $condvar -> conditional arg field name, $cond -> conditional arg value, $ord (optional) -> sort order field for sorting rows correctly
	
	$tqrystr = "SELECT ";
	
	end($varlist);
	$lastelkey = key($varlist);
	
	foreach($varlist as $k=>$tvar) {
		if ($k == $lastelkey) {
			$tqrystr .= "CONVERT(CAST(" . $tvar . " as BINARY) USING " . $enctype . ") ";
		} else {
			$tqrystr .= "CONVERT(CAST(" . $tvar . " as BINARY) USING " . $enctype . "), ";
		}
	}
	if ($ord==NULL) {
		$tqrystr .= "FROM " . $table . " WHERE " . $condvar . "=" . $cond;
	} else {
		$tqrystr .= "FROM " . $table . " WHERE " . $condvar . "=" . $cond . " ORDER BY " . $ord;
	}
	
	return $tqrystr;
}

function mysqlselect_encconv_and($varlist,$enctype,$table,$condvars,$conds,$ord) { //Same as mysqlselect_encconv but with AND operator allowing multiple conditions
	
	$tqrystr = "SELECT ";
	
	end($varlist);
	$lastelkey = key($varlist);
	
	foreach($varlist as $k=>$tvar) {
		if ($k == $lastelkey) {
			$tqrystr .= "CONVERT(CAST(" . $tvar . " as BINARY) USING " . $enctype . ") ";
		} else {
			$tqrystr .= "CONVERT(CAST(" . $tvar . " as BINARY) USING " . $enctype . "), ";
		}
	}
	if ($ord==NULL) {
		$tqrystr .= "FROM " . $table . " WHERE " . $condvars[0] . "=" . $conds[0];
		for ($c=1; $c<count($condvars); $c++) {
			$tqrystr .= " AND " . $condvars[$c] . "=" . $conds[$c];
		}
	} else {
		$tqrystr .= "FROM " . $table . " WHERE " . $condvars[0] . "=" . $conds[0];
		for ($c=1; $c<count($condvars); $c++) {
			$tqrystr .= " AND " . $condvars[$c] . "=" . $conds[$c];
		}
		$tqrystr .=  " ORDER BY " . $ord;
	}
	
	return $tqrystr;
}



function gotourl($suffix) {
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
				$uri = 'https://';
			} else {
				$uri = 'http://';
			}
			$uri .= $_SERVER['HTTP_HOST'];
			header('Location: '.$uri.$suffix);
}

function builddirref_add($dirlist,$indref) { //function to build directory ref, adding parent directories and final target directory as needed
//dirlist should contain hierarchical directories in order, so parent directory first, child second etc., can be any size

		$curdirref = "";
		//echo $GLOBALS['pubsrootdir'];
		if (file_exists($GLOBALS['pubsrootdir'])) {
			
		} else {
			mkdir($GLOBALS['pubsrootdir']);
		}
		$curdirref .= $GLOBALS['pubsrootdir'];
		
		$curdirref .= "/" . $GLOBALS['industrydirs'][$indref];
		if (file_exists($curdirref)) {
			
		} else {
			mkdir($curdirref);
		}
		
		foreach ($dirlist as $dir) { //go through $dirlist in order, creating directories as it goes down the list if needed
			if (is_array($dir)) {	//allows for joining together one-dimension lists from multiple sources
				foreach ($dir as $indir) {
					$curdirref .= "/" . $indir;
					if (file_exists($curdirref)) {
					
					} else {
						mkdir($curdirref);
					}					
				}
			} else {
				$curdirref .= "/" . $dir;
				
				if (file_exists($curdirref)) {
				
				} else {
					mkdir($curdirref);
				}
			}
			
		}
		
		return $curdirref;
}

function builddirref_sel($dirlist,$indref,$reltitle) { //Builds and returns directory reference, does not make any changes
	//dirlist should contain hierarchical directories in order, so parent directory first, child second etc., can be any size
	//$reltitle set as 1 for relative links from publication dir rather than base dir
	
		//var_dump($dirlist);
		
		$curdirref = "";
		if ($reltitle==1) {
			
		} else {
			$curdirref .= $GLOBALS['pubsrootdir'];
			$curdirref .= "/" . $GLOBALS['industrydirs'][$indref];
		}
		
		foreach ($dirlist as $dir) { //go through $dirlist in order, creating directories as it goes down the list if needed
		
			if ($reltitle==1) {
				
					if (is_array($dir)) { //allows for joining together one-dimension lists from multiple sources
						foreach ($dir as $indir) {
							//echo $indir;
							$curdirref .= $indir . "/";
						}
					} else {
						$curdirref .= $dir . "/";
					}
				
			} else {
			
					if (is_array($dir)) { //allows for joining together one-dimension lists from multiple sources
						foreach ($dir as $indir) {
							//echo $indir;
							$curdirref .= "/" . $indir;
						}
					} else {
						$curdirref .= "/" . $dir;
					}
					
			}
		}
		//echo "targdir " . $curdirref;
		return $curdirref;
}

function sqlbuild_mysqlsel_one($mysqli,$qry,$duptest) { //function for running through getting mysql result when wanting one matching record, returns error if duplicate
//or returns array if successful  //  $mysqli -> mysqli object, $qry -> sql query string, $duptest -> array of two items, test factor and description of test factor
	#echo $qry;
	if ($result = $mysqli->query($qry)) {
		$row_cnt = $result->num_rows;
		if ($row_cnt == 1) {
			$thisrow = $result->fetch_array();
			
			return $thisrow;
		} else {
			echo "duplicate entries found for this " . $duptest[1] . ">" . $duptest[0];
			exit;
		}
	} else {	
		$mysqli->close();	
		echo "error: couldn't find " . $duptest[1] . ">" . $duptest[0] . " in database";
		exit;
	}
}

function sqlbuild_mysqlsel($mysqli,$qry,$errtest) { //function for running through getting mysql result when wanting multiple results, returns error if duplicate
//or returns array if successful  //  $mysqli -> mysqli object, $qry -> sql query string, $duptest -> array of two items, test factor and description of test factor
	#echo $qry;
	if ($result = $mysqli->query($qry)) {
		$row_cnt = $result->num_rows;
		if ($row_cnt > 0) {
			
			$tarr = array();
			while ($ftch = $result->fetch_array()) {
				array_push($tarr,$ftch);
			}
			
			return $tarr;
		} else {
			//echo "No entries found for this " . $errtest[1] . ">" . $errtest[0];
			return [];
			exit;
		}
	} else {	
		$mysqli->close();	
		echo "error: couldn't find " . $errtest[1] . ">" . $errtest[0] . " in database";
		exit;
	}

}

?>