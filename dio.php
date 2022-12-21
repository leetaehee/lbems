<?
if (isset($_GET['gw']) && isset($_GET['dev']) && is_numeric($_GET['dev']) && isset($_GET['from']) && isset($_GET['val'])){
	

	$cc = new Redis();
	try {
		$cc->pconnect('127.0.0.1');
	} catch (Exception $e) {
		exit( "Cannot connect to redis server : ".$e->getMessage() );
	}


	$ttl = 86400;
	$gw = $_GET['gw'];
	$dev = $_GET['dev'];
	/* 2018-04-24 특수문자 한글 제외*/
	if(preg_match("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", $gw)) exit;
	if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$gw)) exit;
	if(!preg_match("/[\x30-\x39\x41-\x5A\x61-\x7A]/",$gw)) exit;


	if($_GET['from']>=40000) $from = $_GET['from']-40000;
	else $from = $_GET['from'];

	$val = explode(",", $_GET['val']);
	$data = array();
	

	if($old = json_decode($cc->get($gw), true)){
		$old[$dev][0] = time();
		for($i=0; $i<sizeof($val); $i++){
			$cnt = $i+$from;
			$old[$dev][$cnt] = $val[$i];
		}
		
		$save_data = json_encode($old);
		if(!empty($save_data)) $cc->set($gw, $save_data, $ttl);
	}else{
		$data[$dev][0] = time();
		for($i=0; $i<sizeof($val); $i++){
			$cnt = $i+$from;
			$data[$dev][$cnt] = $val[$i];
		}
		$save_data = json_encode($data);
		if(!empty($save_data)) $cc->set($gw, $save_data, $ttl);
	}

	echo '<sin>ok</sin>'; 
	
	if($err400 = $cc->get("__err400")){
		$errGWS = json_decode($err400, true);
		if(isset($errGWS[$gw])) {
			unset($errGWS[$GW]);
			$cc->set("__err400", $errGWS);
		}	
	}

}else echo '<sin>error</sin>';


if (isset($_GET['gw'])){
	$gw = $_GET['gw'];
	$id = "cmd@".$gw;
	$cmdData = $cc->get($id);
	if(!empty($cmdData)){
		$arr = json_decode($cmdData, true);
		if(is_array($arr)){
			echo '<sout>ok</sout>';
			echo '<gw>'.$gw.'</gw>';
			echo '<dev>'.$arr['dev'].'</dev>';
			echo '<tag>'.$arr['addr'].'</tag>';
			echo '<val>'.$arr['val'].'</val>';
			$cc->delete($id);
		}else echo '<sout>null</sout>';
	}else echo '<sout>null</sout>';
}else die('<sout>error</sout>');

exit;
?>
