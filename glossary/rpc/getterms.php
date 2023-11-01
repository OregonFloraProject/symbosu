<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/GlossaryManager.php');

// Set ID if it's been set, and sanitize
$id = array_key_exists('id',$_REQUEST) ? $_REQUEST['id'] : 0;
if (!is_numeric($id)) $id = 0;

// Initialize array to return
$retArr = array();

// Create a new instance of the glossary manager class
$glosManager = new GlossaryManager();

// If a glossary term ID is set, then return data for that term
if ($id) {

	// Set the glossary term ID and then get the data for it
	$glosManager->setGlossId($id);
	$retArr = $glosManager->getTermArr();

	// Get any synonyms for this term
	$synonymArr = $glosManager->getSynonyms();

	// Create a variable to hold the original term if this is a synonym
	$retArr['redirect'] = "";

	// This is a synonym without its own definition, so get a defintion from the first synonym with one
	if(!$retArr['definition'] && $synonymArr){

		// Cycle through the synonyms
		foreach($synonymArr as $newid => $arr){

			// Found a new definition, so use this
			if($arr['definition']) {

				// Set the original term
				$origTerm = $retArr['term'];

				// Get the data for the synonym term
				$glosManager->setGlossId($newid);
				$retArr = $glosManager->getTermArr();

				// Save the original term
				$retArr['redirect'] = $origTerm;

				// Stop checking synonyms; this uses the first found with a description
				break;
			}
		}
	}

	// Get any images associated with a glossary entry
	$retArr['images'] = $glosManager->getImgArr();

	// Get the thumbnail sizes for each glossary image
	foreach($retArr['images'] as $key => $img) {

		// Get size
		$size = getimagesize($IMAGE_ROOT_PATH . str_replace($IMAGE_ROOT_URL, "", $img['thumbnailurl']));

		// Add thumbnail width and height to the returned JSON
		$retArr['images'][$key]['tn_width'] = $size[0];
		$retArr['images'][$key]['tn_height'] = $size[1];
	}

// No glossary term ID set, so return an object of all of them instead
} else {
	$retArr = array_flip($glosManager->getTermSearch('', '', 0));
}

// Return as JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($retArr);
?>
