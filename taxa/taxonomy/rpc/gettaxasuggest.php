<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/RpcTaxonomy.php');
header("Content-Type: application/json; charset=".$CHARSET);

$term = $_REQUEST['term'];
$taxAuthId = array_key_exists('taid',$_REQUEST)?$_REQUEST['taid']:1;
$rankLimit = array_key_exists('rlimit',$_REQUEST)?$_REQUEST['rlimit']:0;
$rankLow = array_key_exists('rlow',$_REQUEST)?$_REQUEST['rlow']:0;
$rankHigh = array_key_exists('rhigh',$_REQUEST)?$_REQUEST['rhigh']:0;
// Check whether autosuggest is restricted to Oregon vascular plants curated by OregonFlora
$oregonVascPlant = array_key_exists('oregon',$_REQUEST)?$_REQUEST['oregon']:0;

$retArr = array();
if($term){
	$rpcManager = new RpcTaxonomy();
	$rpcManager->setTaxAuthId($taxAuthId);
	// Restrict taxonomy to Oregon vascular plants curated by OregonFlora
	$rpcManager->setOregonVascPlant($oregonVascPlant);
	$retArr = $rpcManager->getTaxaSuggest($term,$rankLimit, $rankLow, $rankHigh);
}
echo json_encode($retArr);
?>