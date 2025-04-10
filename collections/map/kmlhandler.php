<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceMapManager.php');
include_once($SERVER_ROOT.'/collections/download/solr.php');
header("Content-Type: text/html; charset=".$CHARSET);

$recLimit = (isset($_REQUEST['reclimit'])?$_REQUEST['reclimit']:250000);
$kmlFields = array_key_exists('kmlFields',$_POST)?$_POST['kmlFields']:null;
$occIds = null;
$solrqString= array_key_exists('solrqstring', $_REQUEST) ? $_REQUEST['solrqstring'] : '';
if ($solrqString) {
	$solrqString = str_replace('&amp;', '&', $solrqString);
  $occIds = getOccIdsFromSOLR($solrqString, $recLimit);
}

$mapManager = new OccurrenceMapManager();
$mapManager->writeKMLFile($recLimit,$kmlFields,$occIds);
?>