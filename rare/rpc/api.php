<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");
include_once("$SERVER_ROOT/classes/IdentManager.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");
include_once("$SERVER_ROOT/tools/apicache.php");

/*
The RPG page characteristics have custom labels and structure,
hence this var to mimic 1) the structure returned by IdentManager.php
with 2) content custom to RPG.
States are added in getFilterableChars() below
*/


$FILTERABLE_CHARS = [
	[
		'hid' => 1,
		'headingname' => 'Context',
		'subheading'	=> '',
		'characters' => [
			[
				'charname' 		=> 'Habitat',
				'cid'					=> 163,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[
				'charname' 		=> 'Ecoregion',
				'cid'					=> 19,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[
				'charname' 		=> 'Flowering time',
				'cid'					=> 165,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			]
		]
	],
	[
		'hid' => 2,
		'headingname' => 'Elevation',
		'subheading'	=> '(Just grab the slider handles)',
		'characters' => [
			[
				'charname' 		=> '',
				'cid'					=> 820,
				'display'			=> 'slider',
				'units'				=> 'meters',
				'states'			=> [],
			],
		]
	],
	[
		'hid' => 3,
		'headingname' => 'Survey & Manage',
		'subheading'	=> '',
		'characters' => [
			[
				'charname' 		=> 'Best survey time',
				'cid'					=> 633,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[
				'charname' 		=> 'Threats',
				'cid'					=> 823,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[
				'charname' 		=> 'Management action',
				'cid'					=> 824,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			]
		]
	],
	[
		'hid' => 4,
		'headingname' => 'Conservation Status',
		'subheading'	=> '',
		'characters' => [
			[
				'charname' 		=> 'Federal',
				'cid'					=> 242,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[
				'charname' 		=> 'State',
				'cid'					=> 243,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[
				'charname' 		=> 'Heritage',
				'cid'					=> 244,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			]
		]
	]
];

function getEmpty() {
  return [
    "clid" => -1,
    "pid" => -1,
    "dynclid" => -1,
    "projName" => '',
    "title" => '',
    "intro" => '',
    "iconUrl" => '',
    "authors" => '',
    "abstract" => '',
    "taxa" => [],
    "tids" => [],
    "characteristics" => [],
    "lat" => 0,
    "lng" => 0,
    "locality" => '',
    "type" => ''
  ];
}

function getFilterableChars($tids) {
	global $FILTERABLE_CHARS;

	$identManager = new IdentManager();
	$identManager->setClid(Fmchecklists::$CLID_RARE_ALL);
	$identManager->setTaxa();

	return $identManager->getCharacteristicsForStructure($FILTERABLE_CHARS, $tids);
}



/**
 * Returns all unique taxa that match the query. If filtered search params are included in the
 * request, returns only `tid`s of matching taxa. Otherwise, returns full checklist and taxa data
 * including thumbnail urls.
 * @params $_GET
 */
function getTaxa($params) {

	$search = null;
	$results = getEmpty();
	$resultsString = '';

	$identManager = new IdentManager();
	$rareClid = getRareClid();
	$identManager->setClid($rareClid);
	$results["clid"] = $rareClid;

	/**
	 * if it's the initial page load, attr and range will not be set, and we return full checklist and
	 * taxa data.
	 * if it's a filtered search, then attr, range, search/name will be set,
	 * and we 1) include those params in query, and 2) request only tids
	 */

	if (
		key_exists("attr", $params)
		|| isset($params['range'])
		|| (array_key_exists("search", $params) && !empty($params["search"]))
	) {
		$identManager->setIDsOnly(true);
		$identManager->setAttrsFromParams($params);

		if (array_key_exists("search", $params) && !empty($params["search"])) {
			$identManager->setSearchTerm($params["search"]);
			if (array_key_exists("name", $params) && in_array($params['name'], array('sciname','commonname'))) {
				$identManager->setSearchName($params['name']);
			} else {
				$identManager->setSearchName('');#IdentManager defaults to sciname, so we explicitly set it empty here
			}
		}

		$identManager->setTaxa();
		$taxa = $identManager->getTaxa();
		foreach ($taxa as $taxon) {#flatten tids into an array
			$results['tids'][] = $taxon['tid'];
		}
		$results["characteristics"] = getFilterableChars($results['tids']);

		array_walk_recursive($results,'cleanWindowsRecursive');#replace Windows characters
		$resultsString = json_encode($results, JSON_NUMERIC_CHECK);

	} else {
		// This is the initial page load, so get the full checklist with thumbnails and all data.

		$cacheKey = 'rare-full';
		$resultsString = readFromCache($cacheKey);
		if ($resultsString === false) {
			// cache miss, we must generate all the data
			$identManager->setThumbnails(true);
			$identManager->setTaxa();
			$taxa = $identManager->getTaxa();
	
			$results['taxa'] = $taxa;
			$tids = [];
			foreach ($results['taxa'] as $taxon) {
				$tids[] = $taxon['tid'];
			}
			$results['tids'] = $tids;
			$results["characteristics"] = getFilterableChars($results['tids']);
	
			// RPG checklist metadata
			$em = SymbosuEntityManager::getEntityManager();
			$repo = $em->getRepository("Fmchecklists");
			$model = $repo->find($rareClid);
			$checklist = ExploreManager::fromModel($model);
			$results["clid"] = $checklist->getClid();
			$results["title"] = $checklist->getTitle();

			array_walk_recursive($results,'cleanWindowsRecursive');#replace Windows characters
			$resultsString = json_encode($results, JSON_NUMERIC_CHECK);
			writeToCache($cacheKey, $resultsString);
		}
	}
	return $resultsString;
}

$searchResults = '';
if (isset($RPG_FLAG) && $RPG_FLAG === 1) {
	$searchResults = getTaxa($_GET);
}

// Begin View
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: public, max-age=3600");
echo $searchResults;
?>
