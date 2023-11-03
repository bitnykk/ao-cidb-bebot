<?php

if ( isset($_GET['search']) ) {
	
	// all variable initializing
	$search = urldecode($_GET['search']);
	$search = str_replace("'", "''", $search);
	$search = str_replace('"', '\"', $search);
	if ( isset($_GET['ql']) && is_numeric($_GET['ql']) && $_GET['ql']>=0 && $_GET['ql']<=300  ) $ql = $_GET['ql'];
	else $ql = 0;
	if ( isset($_GET['output']) ) $output = $_GET['output'];
	else $output = "aoml";	
	if ( isset($_GET['bot']) ) $bot = $_GET['bot'];
	else $bot = "Unknown";
	if ( isset($_GET['max']) && is_numeric($_GET['max']) && $_GET['max']<51 & $_GET['max']>0 ) $max = $_GET['max'];
	else $max = 50;
	if ( isset($_GET['icons']) ) $icons = $_GET['icons'];
	else $icons = false;
	if ( isset($_GET['color_header']) ) $color_header = $_GET['color_header'];
	else $color_header = "DFDF00";
	if ( isset($_GET['color_highlight']) ) $color_highlight = $_GET['color_highlight'];
	else $color_highlight = "97BE37";
	if ( isset($_GET['color_normal']) ) $color_normal = $_GET['color_normal'];
	else $color_normal = "CCF0AD";

	// values load & db connecting
	require_once("_credentials.php");
	$mysqli = new mysqli($srv,$uzr,$pwd,$dbn);
	if ($mysqli -> connect_errno) {
	  echo "Failed to connect to MySQL ...";
	  exit();
	}
	
	// search preparation & querying
	$check = "SELECT COUNT(*) FROM aorefs WHERE name LIKE '%".$search."%'";
	$number = $mysqli -> query($check);
	$total = $number -> fetch_row();
	if ($total[0]>0) {
		$query = "SELECT * FROM aorefs WHERE name LIKE '%".$search."%' LIMIT ".$max;
	} else {
		$keywords = explode(" ", $search);
		$like = "name LIKE '%".$search."%'";
		foreach($keywords as $keyword) {
			$like = $like." OR name LIKE '%".$keyword."%'";
		}
		$query = "SELECT * FROM aorefs WHERE ".$like." LIMIT ".$max;
	}
	if ($results = $mysqli -> query($query)) {
		$all = array();
	  foreach($results AS $result) {
		  $rid = $result['id'];
		  $rql = $result['ql'];
		  $ricon = $result['icon'];
		  $rname = $result['name'];
		if ( !isset($all[$rname]) ) {
			$all[$rname]['lowid'] = $rid;
			$all[$rname]['highid'] = $rid;
			$all[$rname]['ql'] = $rql;
			$all[$rname]['lowql'] = $rql;
			$all[$rname]['highql'] = $rql;
			$all[$rname]['icon'] = $ricon;
		} else {
			if($rql>$all[$rname]['ql']) {
				$all[$rname]['highid'] = $rid;
				$all[$rname]['highql'] = $rql;
			} elseif($rql<$all[$rname]['ql']) {
				$all[$rname]['lowid'] = $rid;
				$all[$rname]['lowql'] = $rql;
			}
			$all[$rname]['ql'] = 0;
		}
	  }
	  $results -> free_result();
	}	

	// text output formatting
	$cnt = count($all);
	$search = str_replace("''", "'", $search);
	$search = str_replace('\"', "''", $search);
	if ( $output == "aoml" ) {
		echo "<a href=\"text://<font color=#".$color_header.">Found item(s) for '<font color=#".$color_highlight.">".$search."</font>' :</font><br><br><font color=#".$color_normal.">";
		
		if($cnt==0) echo "No corresponding item was found ...<br>";
		foreach($all AS $one => $val) {
			if($icons) echo "<img src='rdb://".$val['icon']."'><br>";
			$rep = str_replace('"', "''", $one);
			echo $rep."<br>QL ";
			if($val['ql']!=0) {
				echo "<a href='itemref://".$val['lowid']."/".$val['highid']."/".$val['ql']."'>".$val['ql']."</a>";
			} else {
				echo "<a href='itemref://".$val['lowid']."/".$val['highid']."/".$val['lowql']."'>".$val['lowql']."</a> | ";
				if($ql!=0 && $ql>=$val['lowql'] && $ql<=$val['highql']) echo "<a href='itemref://".$val['lowid']."/".$val['highid']."/".$ql."'>".$ql."</a> | ";
				echo "<a href='itemref://".$val['lowid']."/".$val['highid']."/".$val['highql']."'>".$val['highql']."</a>";
			}
			echo "<br><br>";
		}
		
		echo "</font>\">".$cnt." item(s) found</a>";		
	}
	
	// link closing
	$mysqli -> close();
	
} else {
	
	// disclaimer displaying
	echo "<center>
<h1>AO light CIDB for BEBOT</h1>
<code>	
////////<br>  
////@@@///<br>
//@@@@@@@@@//<br>
///@@///////@@///<br>
/({/@@/(o/o)/@@/})/<br>
///@@///////@@///<br>
//@@@@@@@@@//<br>
///@@@////<br>
///////<br>
</code>
<br>
This is an AO light cidb aimed to provide Bebots !items command results.<br>
It's based on frequently updated aorefs datas that Bebot also provides.<br>
You'll need Apache/PHP + Bebot & its SQL DataBase to make this work properly.<br>
Once your Bebot & Apache run fine, just edit credentials accordingly to DB.<br><br>
The url passed values beyond ? can be the following :<br>
_ search (<b>mandatory</b> url-encoded text that will be searched for)<br>
_ ql (<i>optionnal</i> quality level digit from 1 to 300)<br>
_ bot (<i>optionnal</i> bot identification that can help debugging)<br>
_ max (<i>optionnal</i> number of results, 50 by default & maximally)<br>
_ icons (<i>optionnal</i> icons selection, false by default for pure text)<br>
_ output (<i>optionnal</i> format, aoml by defaut but may request more support)<br>
_ color_header, color_highlight, color_normal (<i>optionnal</i> text colors)<br>
<h5>Coded by Bitnykk for Bebot.link</h5>
	</center>";
	
}

?>
