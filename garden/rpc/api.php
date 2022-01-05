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
$CID_NURSERY = 209;
$CID_REGION = 208;


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
		'subheading'	=> '',
		'characters' => [
			[		
				'charname' 		=> 'commercial availability by region',
				'cid'					=> $CID_REGION,
				'display'			=> 'groupfilter',
				'units'				=> '',
				'states'			=> [],
			],
			[		
				'charname' 		=> 'nursery availability',
				'cid'					=> $CID_NURSERY,
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
	global $CUSTOM_GARDEN_CHARACTERISTICS, $CID_REGION, $CID_NURSERY;
	
	#$em = SymbosuEntityManager::getEntityManager();
	#$charStateRepo = $em->getRepository("Kmcs");
	
	$identManager = new IdentManager();

	$identManager->setClid(Fmchecklists::$CLID_GARDEN_ALL);
	$identManager->setPid(Fmchecklists::$PID_GARDEN_ALL);
	
	/*hack: use Fmchklstprojlink|sortSequence to store cs values, 
					which we use to look up the clid */
	$em = SymbosuEntityManager::getEntityManager();
	#$qb = $em->createQueryBuilder();
	$checklistRepo = $em->getRepository("Fmchecklists");
	/*$vendor = $em->createQueryBuilder()
		->select(['proj.sortSequence','chil.clidChild'])
		->from("Fmchecklists","c")
		->innerJoin("Fmchklstprojlink","proj","WITH","c.clid = proj.clid")
		->innerJoin("Fmchklstchildren","chil","WITH","c.clid = chil.clidChild")	
		->where("proj.pid = :pid")
		->setParameter(":pid",Fmchecklists::$PID_VENDOR_ALL)
		->distinct()
	;*/
	
	$vendor = $em->createQueryBuilder()
		->select(['proj.clid','proj.sortSequence','chil.clidChild'])#
		->from("Fmchklstprojlink","proj")
		->leftJoin("Fmchklstchildren","chil","WITH","proj.clid = chil.clid")	
		->where("proj.pid = :pid")
		->setParameter(":pid",Fmchecklists::$PID_VENDOR_ALL)
	;

	$vquery = $vendor->getQuery();
	$vresults = $vquery->execute();
	
	$cisLookup = [];
	foreach ($vresults as $vres) {
		if (!$cisLookup[$vres['clid']]) {
			$cisLookup[$vres['clid']] = $vres['sortSequence'];
		}
	}
	foreach ($vresults as $vres) {
		if ($vres['clidChild'] != NULL) {
			$childLookup[$vres['sortSequence']][] = $cisLookup[$vres['clidChild']];
		}
	}

	#$identManager->setAttrs($attrs);
	$identManager->setTaxa();
	$cids = [];
	foreach ($CUSTOM_GARDEN_CHARACTERISTICS as $idx => $group) {
		foreach ($group['characters'] as $gidx => $char) {
			if (empty($CUSTOM_GARDEN_CHARACTERISTICS[$idx]['characters'][$gidx]['states'])) {
				$cids[] = $char['cid'];
			}
		}
	}
	$cresults = $identManager->getCharQuery($tids,$cids);
	#var_dump($cresults);
	
	foreach ($cresults as $cs) {
		foreach ($CUSTOM_GARDEN_CHARACTERISTICS as $idx => $group) {
			foreach ($group['characters'] as $gidx => $char) {
				if ($char['cid'] == $cs['cid']) {
					
					$tmp = [];
					$tmp['cid'] = $char['cid'];
					$tmp['charstatename'] = $cs['charstatename'];#$cs->getCharstatename();
					$tmp['cs'] = $cs['cs'];#$cs->getCs();
					$tmp['numval'] = floatval(preg_replace("/[^0-9\.]/","",$tmp['charstatename']));
					
					if ($CID_REGION == $char['cid']) {
						if ($childLookup[$cs['cs']]) {
							$tmp['children'] = $childLookup[$cs['cs']];
						}
					/*
						switch ($cs['cs']) {
							#$CUSTOM_GARDEN_CHARACTERISTICS[$idx]['characters'][$gidx]['children'][] = [];
							case 1:#14928 Portland Metro
								$tmp['children'] = [2,4];#14921 Aurora Nursery,14923 BeaverLake Nursery
								break;
							case 2:#14929 W Valley
								$tmp['children'] = [3,30];#14922 Balance Restoration,14924 Bloom River Gardens,14927 Katie's Native
								break;
							case 3:#14930 Eastern
								$tmp['children'] = [8];#14925 Clearwater
								break;
							case 4:#14931 Sisk
								$tmp['children'] = [1];#14920 Althouse
								break;
						}
						*/
						
					}						
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

	$memory_limit = ini_get("memory_limit");
	ini_set("memory_limit", "1G");
	set_time_limit(0);
	
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
		$results["characteristics"] = get_garden_characteristics($results['tids']);
		
	}else{#get full default checklist
	
		$identManager->setThumbnails(true);
		$identManager->setTaxa();
		$taxa = $identManager->getTaxa();
		/*getting the imgid in IdentManager and then getting the thumbnailURL from the model here seems to be faster
		than getting both imgid and thumbnailurl in IdentManager;
		likewise, this is faster than using TaxaManager to get both. 
		However, this frequently causes out-of-memory errors on the live server, 
		so abandoned for now.
		added flush() in attempt to speed up Doctrine 
		- ap
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
		$results["characteristics"] = get_garden_characteristics($results['tids']);

	}
	ini_set("memory_limit", $memory_limit);
	set_time_limit(30);
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
echo json_encode($searchResults, JSON_NUMERIC_CHECK);
?>
