<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceDuplicate.php');

// Form Variables
$collId = array_key_exists('collid',$_POST)?trim($_POST['collid']):'';
$occId = array_key_exists('occid',$_POST)?trim($_POST['occid']):'';
$currOccId = array_key_exists('curroccid',$_POST)?trim($_POST['curroccid']):'';
$tDeterm= array_key_exists('transferdeterm',$_POST)?trim($_POST['transferdeterm']):0;
$tMedia = array_key_exists('transfermedia',$_POST)?trim($_POST['transfermedia']):0;
$tAssoc = array_key_exists('transferassoc',$_POST)?trim($_POST['transferassoc']):0;

$isEditor = false;
if($IS_ADMIN) $isEditor = true;
elseif($collId){
	if(array_key_exists('CollEditor',$USER_RIGHTS) && in_array($collId, $USER_RIGHTS['CollEditor'])) $isEditor = true;
	elseif(array_key_exists('CollAdmin',$USER_RIGHTS) && in_array($collId, $USER_RIGHTS['CollAdmin'])) $isEditor = true;
}

// Only allow transfers to users with edit priviledges
if($isEditor){
	$dupeManager = new OccurrenceDuplicate();
	if($occId && $currOccId) {
		// Transfer determinations
		if($tDeterm) $dupeManager->transferDeterminations($occId, $currOccId);
		if($tMedia) $dupeManager->transferMedia($occId, $currOccId);
		if($tAssoc) $dupeManager->transferAssociatedOccurrences($occId, $currOccId);
	}
}

?>