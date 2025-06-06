<?php
#ini_set('display_errors', '1');
#ini_set('display_startup_errors', '1');
#error_reporting(E_ALL);

include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/SOLRManager.php');
include_once($SERVER_ROOT.'/classes/ProfileManager.php');

$pArr = Array();
$pArr["q"] = (isset($_REQUEST["q"])?$_REQUEST["q"]:'*:*');
if(isset($_REQUEST["fq"])) $pArr["fq"] = $_REQUEST["fq"];
if(isset($_REQUEST["pt"])) $pArr["pt"] = $_REQUEST["pt"];
if(isset($_REQUEST["d"])) $pArr["d"] = $_REQUEST["d"];
if(isset($_REQUEST["rows"])) $pArr["rows"] = $_REQUEST["rows"];
if(isset($_REQUEST["start"])) $pArr["start"] = $_REQUEST["start"];
if(isset($_REQUEST["fl"])) $pArr["fl"] = $_REQUEST["fl"];
if(isset($_REQUEST["wt"])) $pArr["wt"] = $_REQUEST["wt"];
if(isset($_REQUEST["action"])) $pArr["action"] = $_REQUEST["action"];

$solrManager = new SOLRManager();

ProfileManager::refreshUserRights();
$canReadRareSpp = false;
if($GLOBALS['USER_RIGHTS']){
		if($GLOBALS['IS_ADMIN'] || array_key_exists("CollAdmin", $GLOBALS['USER_RIGHTS']) || array_key_exists("RareSppAdmin", $GLOBALS['USER_RIGHTS']) || array_key_exists("RareSppReadAll", $GLOBALS['USER_RIGHTS'])){
				$canReadRareSpp = true;
		}
}

/*
SOLRManager.php handles filtering results by security level, while giving no indication that it's doing so.
Thus numFound == 0 whether it's because 1) the user doesn't have permission to see those results, or 2) there are genuinely no results.
This is unacceptable.
Therefore, the following terrible hack:
We do the query once as if the user has permission, in order to get the real total.
If they don't have permission, we do it again with security turned on.  
We then compare and add "hiddenFound" to the response so that spatial.module.js can deal with it.

*/


if (!isset($pArr["action"]) || $pArr["action"] != 'getsolrreccnt') {#if it's not getsolrreccnt, handle as usual
	$pArr["q"] = $solrManager->checkQuerySecurity($pArr["q"]);
}


if($pArr["wt"] == 'geojson'){
		$pArr["geojson.field"] = 'geo';
		$pArr["omitHeader"] = 'true';
}

$headers = array(
		'Content-Type: application/x-www-form-urlencoded',
		'Accept: application/json',
		'Cache-Control: no-cache',
		'Pragma: no-cache',
		'Content-Length: '.strlen(http_build_query($pArr))
);

$ch = curl_init();

$options = array(
		CURLOPT_URL => $SOLR_URL.'/select',
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_TIMEOUT => 90,
		CURLOPT_POSTFIELDS => http_build_query($pArr),
		CURLOPT_RETURNTRANSFER => true
);
curl_setopt_array($ch, $options);
$result = curl_exec($ch);
curl_close($ch);
$JSON = $result;

if (!$canReadRareSpp && isset($pArr["action"]) && $pArr["action"] == 'getsolrreccnt') {#get results filtered by security
	$full = json_decode($JSON);
	$full->response->hiddenFound = 0;
	$fullJSON = json_encode($full);#re-encode 
	$JSON = $fullJSON;

	$pArr["q"] = $solrManager->checkQuerySecurity($pArr["q"]);#partial results
	$headers = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Accept: application/json',
			'Cache-Control: no-cache',
			'Pragma: no-cache',
			'Content-Length: '.strlen(http_build_query($pArr))
	);

	$ch = curl_init();

	$options = array(
			CURLOPT_URL => $SOLR_URL.'/select',
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => 90,
			CURLOPT_POSTFIELDS => http_build_query($pArr),
			CURLOPT_RETURNTRANSFER => true
	);
	curl_setopt_array($ch, $options);
	$partialJSON = curl_exec($ch);
	curl_close($ch);
	$partial = json_decode($partialJSON);
	#var_dump($full);
	if ($full->response->numFound > $partial->response->numFound) {#some results have been suppressed
		$partial->response->hiddenFound = ($full->response->numFound - $partial->response->numFound);#add hiddenFound
		$partialJSON = json_encode($partial);#re-encode 
	}
	#var_dump($partial);
	$JSON = $partialJSON;
}


header("Content-Type: application/json; charset=utf-8");
echo $JSON;

/*
object(stdClass)#5 (2) {
  ["responseHeader"]=>
  object(stdClass)#3 (3) {
    ["status"]=>
    int(0)
    ["QTime"]=>
    int(1)
    ["params"]=>
    object(stdClass)#4 (4) {
      ["q"]=>
      string(203) "(((sciname:Howellia\ aquatilis) OR (sciname:Howellia\ aquatilis\ *)) OR (tidinterpreted:(5665))) AND (decimalLatitude:[* TO *] AND decimalLongitude:[* TO *] AND sciname:[* TO *]) AND (localitySecurity:0)"
      ["start"]=>
      string(1) "0"
      ["rows"]=>
      string(1) "0"
      ["wt"]=>
      string(4) "json"
    }
  }
  ["response"]=>
  object(stdClass)#6 (3) {
    ["numFound"]=>
    int(13)
    ["start"]=>
    int(0)
    ["docs"]=>
    array(0) {
    }
  }
}
*/
?>