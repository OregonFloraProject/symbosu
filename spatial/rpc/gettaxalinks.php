<?php
include_once('../shared/getTaxaData.php');
include_once('../../config/symbini.php');
include_once('../../config/dbconnection.php');
include_once($SERVER_ROOT.'/classes/SpatialModuleManager.php');

$taxaArrJson = array_key_exists('taxajson',$_REQUEST)?$_REQUEST['taxajson']:'';
$taxonType = array_key_exists('type',$_REQUEST)?$_REQUEST['type']:0;
$useThes = array_key_exists('thes',$_REQUEST)?$_REQUEST['thes']:false;

$tempTaxaArr = Array();
$taxaArr = Array();

if($taxaArrJson){
    $tempTaxaArr = json_decode($taxaArrJson);
}

if($tempTaxaArr){
    $taxaArr = getTaxaData($tempTaxaArr, $taxonType, $useThes);
}
echo json_encode($taxaArr);
?>