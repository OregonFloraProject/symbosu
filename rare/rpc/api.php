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
States are added in getFilterableChars() below
*/
#$CID_NURSERY = 209;
#$CID_REGION = 208;


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

	#$em = SymbosuEntityManager::getEntityManager();
	#$charStateRepo = $em->getRepository("Kmcs");

	$identManager = new IdentManager();

	$identManager->setClid(Fmchecklists::$CLID_RARE_ALL);
	// $identManager->setPid(Fmchecklists::$PID_GARDEN_ALL);

	/*hack: use Fmchklstprojlink|sortSequence to store cs values,
					which we use to look up the clid */
	$em = SymbosuEntityManager::getEntityManager();
	$checklistRepo = $em->getRepository("Fmchecklists");

	// $lookups = $identManager->getVendorLookups();
	// $clidLookup = $lookups->clidLookup;
	// $childLookup = $lookups->childLookup;

	$identManager->setTaxa();
	$cids = [];
	foreach ($FILTERABLE_CHARS as $idx => $group) {
		foreach ($group['characters'] as $gidx => $char) {
			if (empty($FILTERABLE_CHARS[$idx]['characters'][$gidx]['states'])) {
				$cids[] = $char['cid'];
			}
		}
	}
	$cresults = $identManager->getCharQuery($tids,$cids);
	#var_dump($cresults);

	foreach ($cresults as $cs) {
		foreach ($FILTERABLE_CHARS as $idx => $group) {
			foreach ($group['characters'] as $gidx => $char) {
				if ($char['cid'] == $cs['cid']) {
					$tmp = [];
					$tmp['cid'] = $char['cid'];
					$tmp['charstatename'] = $cs['charstatename'];#$cs->getCharstatename();
					$tmp['cs'] = $cs['cs'];#$cs->getCs();
					$tmp['numval'] = floatval(preg_replace("/[^0-9\.]/","",$tmp['charstatename']));

					// if (getRegionCid() == $char['cid']) {
					// #var_dump($char);//pass
					// 	if ($childLookup[$cs['cs']]) {
					// #var_dump('found child lookup');//fail
					// 		$tmp['children'] = $childLookup[$cs['cs']];
					// 	}
					// }
					// if (getNurseryCid() == $char['cid']) {
					// 	if (isset($clidLookup[$cs['cs']])) {
					// 		$tmp['clid'] = $clidLookup[$cs['cs']];
					// 		$tmp['pid'] = Fmchecklists::$PID_VENDOR_ALL;
					// 	}
					// }
					/*
						switch ($cs['cs']) {
							#$FILTERABLE_CHARS[$idx]['characters'][$gidx]['children'][] = [];
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
					$FILTERABLE_CHARS[$idx]['characters'][$gidx]['states'][] = $tmp;
				}
			}
		}
	}

	return $FILTERABLE_CHARS;
}



/**
 * Returns all unique taxa with thumbnail urls
 * @params $_GET
 */

function getTaxa($params) {

	$memory_limit = ini_get("memory_limit");
	ini_set("memory_limit", "1G");
	set_time_limit(0);

	$search = null;
	$results = getEmpty();

	$em = SymbosuEntityManager::getEntityManager();

	$identManager = new IdentManager();
	$rareClid = getRareClid();
	$identManager->setClid($rareClid);
	$results["clid"] = $rareClid;

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
		$results["characteristics"] = getFilterableChars($results['tids']);
	
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
		$model = $repo->find($rareClid);
		$checklist = ExploreManager::fromModel($model);
		$results["clid"] = $checklist->getClid();
		$results["title"] = $checklist->getTitle();
		$results["characteristics"] = getFilterableChars($results['tids']);
	}
	ini_set("memory_limit", $memory_limit);
	set_time_limit(30);
	return $results;
}
		
	
$searchResults = getTaxa($_GET);

// Begin View
array_walk_recursive($searchResults,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=UTF-8");
echo json_encode($searchResults, JSON_NUMERIC_CHECK);
?>
