<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceDuplicate.php');
//include_once($SERVER_ROOT.'/classes/RpcUsers.php');
//header("Content-Type: application/json; charset=".$CHARSET);

// Variables
$collId = array_key_exists('collid',$_REQUEST)?trim($_REQUEST['collid']):'';
$occId = array_key_exists('occid',$_REQUEST)?trim($_REQUEST['occid']):'';
$currOccId = array_key_exists('curroccid',$_REQUEST)?trim($_REQUEST['curroccid']):'';
$tDeterm= array_key_exists('transferdeterm',$_REQUEST)?trim($_REQUEST['transferdeterm']):0;
$tMedia = array_key_exists('transfermedia',$_REQUEST)?trim($_REQUEST['transfermedia']):0;
$tAssoc = array_key_exists('transferassoc',$_REQUEST)?trim($_REQUEST['transferassoc']):0;

$isEditor = false;
if($IS_ADMIN) $isEditor = true;
elseif($collId){
	if(array_key_exists('CollEditor',$USER_RIGHTS) && in_array($collId, $USER_RIGHTS['CollEditor'])) $isEditor = true;
	elseif(array_key_exists('CollAdmin',$USER_RIGHTS) && in_array($collId, $USER_RIGHTS['CollAdmin'])) $isEditor = true;
}
echo "Pretest";
print_r($_POST);
//$retArr = array();
if($isEditor){
	echo "test";
	echo $tDeterm;
	$dupeManager = new OccurrenceDuplicate();

	if($occId && $currOccId) {
		// Transfer determinations
		if($tDeterm) $dupeManager->transferDeterminations($occId, $currOccId);
		if($tMedia) $dupeManager->transferMedia($occId, $currOccId);
		if($tAssoc) $dupeManager->transferAssociatedOccurrences($occId, $currOccId);
	}
	//$retStr = $dupeManager->getDupes($collName, $collNum, $collDate, $ometid, $exsNumber, $currentOccid);
}

//echo json_encode($retArr);
?>