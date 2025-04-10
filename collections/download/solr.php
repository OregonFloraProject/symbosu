<?php
include_once('../../config/symbini.php');

/**
 * Helper function that returns an array of occIds given a SOLR query string and record limit.
 *
 * Polygon searches in MySQL using ST_WITHIN are extremely slow compared to SOLR, so in these cases
 * it is significantly faster to use SOLR to do the actual search (returning a list of occIds) and
 * then use MySQL to select the data for these occIds.
 *
 * It may be faster still to rewrite the data download modules to read all data directly from SOLR
 * and bypass MySQL entirely, but that's a much larger project, and for now this compromise works
 * sufficiently well.
 */
function getOccIdsFromSOLR($solrqString, $recLimit) {
  global $SOLR_URL;
	$body = $solrqString . '&rows=' . $recLimit . '&start=0&fl=occid&wt=json';

	$headers = array(
		'Content-Type: application/x-www-form-urlencoded',
		'Accept: application/json',
		'Cache-Control: no-cache',
		'Pragma: no-cache',
		'Content-Length: ' . strlen($body)
	);
	$ch = curl_init();
	$options = array(
		CURLOPT_URL => $SOLR_URL . '/select',
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_TIMEOUT => 90,
		CURLOPT_POSTFIELDS => $body,
		CURLOPT_RETURNTRANSFER => true
	);
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close($ch);

	$recArr = json_decode($result, true)['response']['docs'];
	unset($result);

	$occIds = [];
	foreach ($recArr as $k) {
			$occIds[] = $k['occid'];
	}
	return $occIds;
}

?>
