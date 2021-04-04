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


function get_garden_characteristics() {
	global $CUSTOM_GARDEN_CHARACTERISTICS;
	$em = SymbosuEntityManager::getEntityManager();
	$charStateRepo = $em->getRepository("Kmcs");
	
	foreach ($CUSTOM_GARDEN_CHARACTERISTICS as $idx => $group) {
		foreach ($group['characters'] as $gidx => $char) {
			if (empty($CUSTOM_GARDEN_CHARACTERISTICS[$idx]['characters'][$gidx]['states'])) {#skip if we hardcoded them above
				$csQuery = $charStateRepo->findBy([ "cid" => $char['cid'] ], ["sortsequence" => "ASC"]);
				foreach ($csQuery as $cs) {
					$tmp = [];
					$tmp['cid'] = $char['cid'];
					$tmp['charstatename'] = $cs->getCharstatename();
					$tmp['cs'] = $cs->getCs();
					$tmp['numval'] = floatval(preg_replace("/[^0-9\.]/","",$tmp['charstatename']));
				
					$CUSTOM_GARDEN_CHARACTERISTICS[$idx]['characters'][$gidx]['states'][] = $tmp;
				}
			}
		}
	}
	return $CUSTOM_GARDEN_CHARACTERISTICS;
}


/**
 * Returns canned searches for the react page
 */
function get_canned_searches() {
	$em = SymbosuEntityManager::getEntityManager();
	$checklistRepo = $em->getRepository("Fmchecklists");
	$gardenChecklists = $checklistRepo->findBy([ "parentclid" => Fmchecklists::$CLID_GARDEN_ALL ]);
	$results = [];

	foreach ($gardenChecklists as $cl) {
		array_push($results, [
			"clid" => $cl->getClid(),
			"name" => $cl->getName(),
			"iconUrl" => $cl->getIconurl(),
			"description" => ucfirst($cl->getTitle())
		]);
	}

	return $results;
}

/*
function get_garden_characteristics($cid) {
	$em = SymbosuEntityManager::getEntityManager();
	$charStateRepo = $em->getRepository("Kmcs");
	$csQuery = $charStateRepo->findBy([ "cid" => $cid ], ["sortsequence" => "ASC"]);
	$return = array_map(function($cs) { return $cs->getCharstatename(); }, $csQuery);
	return $return;
}
*/



/**
 * Returns all unique taxa with thumbnail urls
 * @params $_GET
 */
 /*
function get_garden_taxa($params) {
	$memory_limit = ini_get("memory_limit");
	ini_set("memory_limit", "1G");
	set_time_limit(0);

	$search = null;
	$results = [];

	if (key_exists("search", $params) && $params["search"] !== "" && $params["search"] !== null) {
		$search = strtolower(preg_replace("/[;()-]/", '', $params["search"]));
	}

	$em = SymbosuEntityManager::getEntityManager();
	$taxaRepo = $em->getRepository("Taxa");

	// All tids that belong to Garden checklist
	$gardenTaxaQuery = $taxaRepo->createQueryBuilder("t")
		->innerJoin("Fmchklsttaxalink", "tl", "WITH", "t.tid = tl.tid")
		#->innerJoin("Fmchecklists", "cl", "WITH", "tl.clid = cl.clid")
		->where("tl.clid = " . Fmchecklists::$CLID_GARDEN_ALL);

	if ($search !== null) {
		$gardenTaxaQuery
			->innerJoin("Taxavernaculars", "tv", "WITH", "t.tid = tv.tid")
			->andWhere($gardenTaxaQuery->expr()->orX(
				$gardenTaxaQuery->expr()->like("t.sciname", ":search"),
				$gardenTaxaQuery->expr()->like("tv.vernacularname", ":search")
			))
			->groupBy("t.tid")
			->setParameter("search", "$search%");
	}

	$gardenTaxaModels = $gardenTaxaQuery->getQuery()->execute();

	foreach ($gardenTaxaModels as $taxaModel) {
		$taxa = TaxaManager::fromModel($taxaModel);

		array_push($results, array_merge(
				$taxa->getCharacteristics(),
				[
					"tid" => $taxa->getTid(),
					"sciName" => $taxa->getSciname(),
					"vernacular" => [
						"basename" => $taxa->getBasename(),
						"names" => $taxa->getVernacularNames(),
					],
					"image" => $taxa->getThumbnail(),
					"checklists" => $taxa->getChecklists()
				]
			)
		);
	}

	ini_set("memory_limit", $memory_limit);
	set_time_limit(30);
	return $results;
}
*/

function get_garden_taxa($params) {

	$search = null;
	$results = getEmpty();
	
	$em = SymbosuEntityManager::getEntityManager();

	$identManager = new IdentManager();
	$identManager->setClid($params['clid']);
	$identManager->setPid(3);
	$results["clid"] = 54;
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
		#attr, range, search/name handling copied from ident/rpc/api.php
		$attrs = array();
		if (key_exists("attr", $params)){
			foreach ($params['attr'] as $attr) {
				if(strpos($attr,'-') !== false) {
					$fragments = explode("-",$attr);
					$cid = intval($fragments[0]);
					$cs = intval($fragments[1]);
					if (is_numeric($cid) && is_numeric($cs)) {
						$attrs[$cid][] = $cs;
					}
				}
			}
		}#end attr
		if (isset($params['range'])) {
			$ranges = array();
			foreach ($params['range'] as $range) {
				if(strpos($range,'-') !== false) {
					$fragments = explode("-",$range);
					$cid = intval($fragments[0]);
					$type = $fragments[1];
					$cs = intval($fragments[2]);#cancelled for now: for min/max this is cs, but for i(ncrement), it's the increment val
					if (is_numeric($cid) && !empty($cs) && in_array($type,array("n","x"))) {#,"i"
						$ranges[$cid][$type] = $cs;
					}
				}
			}
			#var_dump($ranges);
			$charStateRepo = $em->getRepository("Kmcs");
			foreach ($ranges as $cid => $range) {
			#var_dump($range);
				$csQuery = $charStateRepo->findBy([ "cid" => $cid ], ["sortsequence" => "ASC"]);
				$csArr = array_map(function($cs) { return intval($cs->getCs()); }, $csQuery);
				#var_dump($csArr);
				foreach ($csArr as $_cs) {
					if ($_cs >= $range['n'] && $_cs <= $range['x']) {
						$attrs[$cid][] = $_cs;
					}
				}
			}
		}#end range
		#var_dump($attrs);
		$identManager->setAttrs($attrs);
		
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
		
	}else{#get full default checklist
	
		$identManager->setThumbnails(true);
		$identManager->setTaxa();
		$taxa = $identManager->getTaxa();
		/*getting the imgid in IdentManager and then getting the thumbnailURL from the model here seems to be faster
		than getting both imgid and thumbnailurl in IdentManager;
		likewise, this is faster than using TaxaManager to get both. 
		*/
		$em->flush();
		$imageRepo = $em->getRepository("Images");
		foreach ($taxa as $key => $taxon) {
			$model = $imageRepo->find($taxon['imgid']);
			$taxa[$key]['image'] = resolve_img_path($model->getThumbnailurl());
		}
		$em->flush();
		$results['taxa'] = $taxa;
		$tids = [];
		foreach ($results['taxa'] as $taxon) {
			$tids[] = $taxon['tid'];
		}
		$results['tids'] = $tids;
		$repo = $em->getRepository("Fmchecklists");
		$model = $repo->find(54);
		$checklist = ExploreManager::fromModel($model);
		$checklist->setPid(3);
		$results["clid"] = $checklist->getClid();
		$results["pid"] = $checklist->getPid();
		$results["title"] = $checklist->getTitle();

	}
	return $results;
}
		
	
$searchResults = [];
if (key_exists("canned", $_GET) && $_GET["canned"] === "true") {
	$searchResults = get_canned_searches();
} else if (key_exists("chars", $_GET) && $_GET['chars'] == 'true') {
	$searchResults = get_garden_characteristics();
	#var_dump($searchResults);
} /*else if (key_exists("attr", $_GET) && is_numeric($_GET['attr'])) {
	$searchResults = get_garden_characteristics(intval($_GET['attr']));
	#var_dump($searchResults);
} */ else {
	$searchResults = get_garden_taxa($_GET);

}

// Begin View
array_walk_recursive($searchResults,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=UTF-8");
echo json_encode($searchResults, JSON_NUMERIC_CHECK);
?>
