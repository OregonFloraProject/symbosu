<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");
include_once("$SERVER_ROOT/classes/IdentManager.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");

/*
The Natives page characteristics have custom labels and structure, 
hence this var to mimic 1) the structure returned by IdentManager.php
with 2) content custom to Natives.
States are added in get_garden_characteristics() below
*/
#$CID_NURSERY = 209;
#$CID_REGION = 208;


$CUSTOM_GARDEN_CHARACTERISTICS = [
	[
		'hid' => 1,
		'headingname' => 'Plant Needs',
		'subheading'	=> '',
		'characters' => [
			[		
				'charname' 		=> 'Sunlight',
				'cid'					=> 680,
				'display'			=> 'select',
				'units'				=> '',
				'states'			=> [#custom terms for Natives, so hardcoded here
					[
						"cid"	=> 680,
						"charstatename" => "Sun",
						"cs" => 1,
						"numval" => 0					
					],
					[
						"cid"	=> 680,
						"charstatename" => "Part-Shade",
						"cs" => 2,
						"numval" => 0					
					],
					[
						"cid"	=> 680,
						"charstatename" => "Full-Shade",
						"cs" => 3,
						"numval" => 0					
					],
				],
			],
			[		
				'charname' 		=> 'Moisture',
				'cid'					=> 683,
				'display'			=> 'select',
				'units'				=> '',
				'states'			=> [#custom terms for Natives, so hardcoded here
					[
						"cid"	=> 683,
						"charstatename" => "Dry",
						"cs" => 1,
						"numval" => 0					
					],
					[
						"cid"	=> 683,
						"charstatename" => "Moderate",
						"cs" => 2,
						"numval" => 0					
					],
					[
						"cid"	=> 683,
						"charstatename" => "Wet",
						"cs" => 3,
						"numval" => 0					
					],
				],
			],
		]
	],
	[
		'hid' => 2,
		'headingname' => 'Mature Size',
		'subheading'	=> '(Just grab the slider handles)',
		'characters' => [
			[		
				'charname' 		=> 'Height',
				'cid'					=> 140,
				'display'			=> 'slider',
				'units'				=> 'ft',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'Width',
				'cid'					=> 738,
				'display'			=> 'slider',
				'units'				=> 'ft',
				'states'			=> [],
			],
		]
	],
	[
		'hid' => 3,
		'headingname' => 'Plant Features',
		'subheading'	=> '',
		'characters' => [
			[		
				'charname' 		=> 'flower color',
				'cid'					=> 612,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'bloom months',
				'cid'					=> 165,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'wildlife support',
				'cid'					=> 685,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'lifespan',
				'cid'					=> 136,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'foliage type',
				'cid'					=> 100,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'plant type',
				'cid'					=> 137,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			]
		]
	
	],
	[
		'hid' => 4,
		'headingname' => 'Growth & Maintenance',
		'subheading'	=> '',
		'characters' => [
			[		
				'charname' 		=> 'landscape uses',
				'cid'					=> 679,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'cultivation preferences',
				'cid'					=> 767,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'behavior',
				'cid'					=> 688,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'propagation',
				'cid'					=> 740,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'ease of growth',
				'cid'					=> 684,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			]
		]
	],
	[
		'hid' => 5,
		'headingname' => 'Beyond the Garden',
		'subheading'	=> '',
		'characters' => [
			[		
				'charname' 		=> 'ecoregion',
				'cid'					=> 19,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'habitat',
				'cid'					=> 163,
				'display'			=> '',
				'units'				=> '',
				'states'			=> [],
			],
		]
	],
	[
		'hid' => 5,
		'headingname' => 'Commercial Availability',
		'subheading'	=> '',//'<a href="' . $CLIENT_ROOT . '/projects/index.php?pid=4">View all participating nurseries and the native species they carry</a>',
		'characters' => [
			[		
				'charname' 		=> 'click to auto-select nurseries from a region below',
				'cid'					=> getRegionCid(),
				'display'			=> 'groupfilter',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'nursery availability',
				'cid'					=> getNurseryCid(),
				'display'			=> '',#vendor
				'units'				=> '',
				'states'			=> [],
			],
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

function get_garden_characteristics($tids) {
	global $CUSTOM_GARDEN_CHARACTERISTICS;
	
	$identManager = new IdentManager();

	$identManager->setClid(Fmchecklists::$CLID_GARDEN_ALL);
	$identManager->setPid(Fmchecklists::$PID_GARDEN_ALL);

	$identManager->setTaxa();

	return $identManager->getCharacteristicsForStructure($CUSTOM_GARDEN_CHARACTERISTICS, $tids);
}

	
/**
 * Returns canned searches for the react page
 */

function get_canned_searches() {
	$em = SymbosuEntityManager::getEntityManager();
	$checklistRepo = $em->getRepository("Fmchecklists");
	#$gardenChecklists = $checklistRepo->findBy([ "parentclid" => Fmchecklists::$CLID_GARDEN_ALL ]);
	$canned = $em->createQueryBuilder()
		->select(['c.clid'])
		->from("Fmchecklists","c")
		->innerJoin("Fmchklstprojlink","proj","WITH","c.clid = proj.clid")
		->where("proj.pid = :pid")
		->andWhere("c.parentclid = " . Fmchecklists::$CLID_GARDEN_ALL)
		->setParameter(":pid",3)
		->distinct()
	;
	$cquery = $canned->getQuery();
	$cresults = $cquery->execute();
	
	$results = [];

	foreach ($cresults as $cresult) {
		$cl = $checklistRepo->findBy([ "clid" => $cresult['clid']])[0];
		array_push($results, [
			"clid" => $cl->getClid(),
			"name" => $cl->getName(),
			"iconUrl" => $cl->getIconurl(),
			"description" => ucfirst($cl->getTitle())
		]);
	}

	return $results;
}



/**
 * Returns all unique taxa with thumbnail urls
 * @params $_GET
 */

function get_garden_taxa($params) {

	$search = null;
	$results = getEmpty();

	$identManager = new IdentManager();
	$identManager->setClid($params['clid']);
	$identManager->setPid(3);
	$results["clid"] = getGardenClid();
	$results["pid"] = 3;
	
/*
	if it's the initial page load, attr and range will not be set, and we return full checklist and taxa data.
	if it's a filtered search, then attr, range, search/name will be set, 
	and we 1) include those params in query, and 2) request only tids
*/
	
	if (
		(key_exists("attr", $params))
		|| (isset($params['range']))
		|| ( array_key_exists("search", $params) && !empty($params["search"]) )
	) {
		$identManager->setIDsOnly(true);
		$identManager->setAttrsFromParams($params);
		
		if (	array_key_exists("search", $params) && !empty($params["search"]) ) {
			$identManager->setSearchTerm($params["search"]);
			if (array_key_exists("name", $params) && in_array($params['name'],array('sciname','commonname'))) {
				$identManager->setSearchName($params['name']);
			}else{
				$identManager->setSearchName('');#IdentManager defaults to sciname, so we explicitly set it empty here
			}
		}
		
		$identManager->setTaxa();
		$taxa = $identManager->getTaxa();
		foreach ($taxa as $taxon) {#flatten tids into an array
			$results['tids'][] = $taxon['tid'];
		}
		$results["characteristics"] = get_garden_characteristics($results['tids']);
		
	}else{#get full default checklist
	
		$identManager->setThumbnails(true);
		$identManager->setTaxa();
		$taxa = $identManager->getTaxa();
		
		$results['taxa'] = $taxa;
		$tids = [];
		foreach ($results['taxa'] as $taxon) {
			$tids[] = $taxon['tid'];
		}
		$results['tids'] = $tids;

		$em = SymbosuEntityManager::getEntityManager();
		$repo = $em->getRepository("Fmchecklists");
		$model = $repo->find(getGardenClid());
		$checklist = ExploreManager::fromModel($model);
		$checklist->setPid(3);
		$results["clid"] = $checklist->getClid();
		$results["pid"] = $checklist->getPid();
		$results["title"] = $checklist->getTitle();
		$results["characteristics"] = get_garden_characteristics($results['tids']);

	}
	return $results;
}
		
	
$searchResults = [];
if (key_exists("canned", $_GET) && $_GET["canned"] === "true") {
	$searchResults = get_canned_searches();
}/* else if (key_exists("chars", $_GET) && $_GET['chars'] == 'true') {
	$searchResults = get_garden_characteristics();
	#var_dump($searchResults);
}*/ else {
	$searchResults = get_garden_taxa($_GET);

}

// Begin View
array_walk_recursive($searchResults,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: public, max-age=86400");
echo json_encode($searchResults, JSON_NUMERIC_CHECK);
?>
