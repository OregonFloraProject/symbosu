<?php
include_once("../../config/symbini.php");

include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");
include_once("$SERVER_ROOT/classes/InventoryManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");
include_once("$SERVER_ROOT/classes/IdentManager.php");

$result = [];

function getEmpty() {
  return [
    "clid" => -1,
    "projName" => '',
    "title" => '',
    "intro" => '',
    "iconUrl" => '',
    "authors" => '',
    "abstract" => '',
		'notes'=>'',
    "lat" => 0,
    "lng" => 0,
    "taxa" => [],
    "tids" => []
  ];
}

function buildResult($params) {
  $em = SymbosuEntityManager::getEntityManager();
  $repo = $em->getRepository("Fmchecklists");
  $model = $repo->find($params["clid"]);
  $checklistObj = ExploreManager::fromModel($model);
  if (array_key_exists("pid", $params) && $params["pid"] > -1) {
    $checklistObj->setPid($params["pid"]);
  }

  $result = getEmpty();

  if ($checklistObj !== null) {
    if ($checklistObj->getPid() > -1) {
      $projRepo = SymbosuEntityManager::getEntityManager()->getRepository("Fmprojects");
      $model = $projRepo->find($checklistObj->getPid());
      $project = InventoryManager::fromModel($model);
      $result["projName"] = $project->getProjname();
    }
    $result["clid"] = $checklistObj->getClid();
    $result["title"] = $checklistObj->getTitle();
    $result["intro"] = ($checklistObj->getIntro()? $checklistObj->getIntro() :'') ;
    $result["iconUrl"] = ($checklistObj->getIconUrl()? $checklistObj->getIconUrl() :'') ;
    $result["authors"] = ($checklistObj->getAuthors()? $checklistObj->getAuthors() :'') ;
    $result["abstract"] = ($checklistObj->getAbstract()? $checklistObj->getAbstract() :'') ;
    $result["locality"] = ($checklistObj->getLocality()? $checklistObj->getLocality() :'') ;
    $result["publication"] = ($checklistObj->getPublication()? $checklistObj->getPublication() :'') ;
    $result["notes"] = ($checklistObj->getNotes()? $checklistObj->getNotes() :'') ;
    $result["pointradiusmeters"] = ($checklistObj->getPointRadius()? $checklistObj->getPointRadius() :'') ;
    $result["lat"] = ($checklistObj->getLatcentroid()? $checklistObj->getLatcentroid() :'') ;
    $result["lng"] = ($checklistObj->getLongcentroid()? $checklistObj->getLongcentroid() :'') ;

    $identManager = new IdentManager();
    $identManager->setClid($checklistObj->getClid());
    $identManager->setOrderBySciname(true);
    $identManager->setIncludeChecklistNotes(true);

    if (
      (array_key_exists("search", $params) && !empty($params["search"])) &&
      (array_key_exists("name", $params) && in_array($params['name'], array('sciname', 'commonname')))
    ) {
      $identManager->setSearchTerm($params["search"]);
      $identManager->setSearchName($params['name']);
      $identManager->setSearchSynonyms((isset($params['synonyms']) && $params['synonyms'] == 'on') ? true : false);
    } else {
      // no search terms, so this is the initial page load; in this case we want thumbnails
      $identManager->setThumbnails(true);
    }

    $identManager->setTaxa();
    $taxa = $identManager->getTaxa();
    if (sizeof($taxa)) {
      $vouchers = $checklistObj->getVouchers();
      foreach ($taxa as $rowArr){
        $rowArr['vouchers'] = ($vouchers && isset($vouchers[$rowArr['tid']]) ? $vouchers[$rowArr['tid']] : '');
        $rowArr['checklistNotes'] = str_replace(',', ';', $rowArr['checklistNotes']); //can't change comma to semi-colon in Doctrine, so doing it here

        if (array_key_exists('image', $rowArr)) {
          $rowArr['thumbnail'] = $rowArr['image'];
          unset($rowArr['image']);
        }

        $result['taxa'][] = $rowArr;
        $result['tids'][] = $rowArr['tid'];
      }
    }
    $result['totals'] = TaxaManager::getTaxaCounts($result['taxa']);
  }
  return $result;
}

function buildDynResult($params) {
  $dynclid = $params["dynclid"];

  $result = getEmpty();

	$em = SymbosuEntityManager::getEntityManager();
	$repo = $em->getRepository("Fmdynamicchecklists");
	$model = $repo->find($dynclid);
	$dynamic_checklist = ExploreManager::fromModel($model);
	if ($dynamic_checklist !== null) {

		$result["title"] = $dynamic_checklist->getTitle();
		$result["abstract"] = '';
		$result["authors"] = '';
		$result["projName"] = '';
		$a = explode(" ",$result['title']);#parse the lat/lng out of the title - why are there no fields for lat/lng?
		$result['lat'] = is_numeric($a[0])? floatval($a[0]) : '';
		$result['lng'] = is_numeric($a[1])? floatval($a[1]) : '';

		$identManager = new IdentManager();
		$identManager->setDynClid($dynclid);
		$identManager->setOrderBySciname(true);
		$identManager->setIncludeChecklistNotes(true);

		if (array_key_exists("search", $params) && !empty($params["search"])) {
			$identManager->setSearchTerm($params["search"]);
			//$identManager->setIDsOnly(true);
			if (array_key_exists("name", $params) && !empty($params["name"])) {
				$identManager->setSearchName($params["name"]);
			}
			$identManager->setSearchSynonyms((isset($params['synonyms']) && $params['synonyms'] == 'on') ? true : false);
		} else {
			// no search terms, so this is the initial page load; in this case we want thumbnails
			$identManager->setThumbnails(true);
		}

		$identManager->setTaxa();
		$result["taxa"] = $identManager->getTaxa(); 
		foreach ($result["taxa"] as $taxonKey => $taxon) {
			// replace 'image' key with 'thumbnail'
			if (array_key_exists('image', $taxon)) {
				$result["taxa"][$taxonKey]['thumbnail'] = $taxon['image'];
				unset($result["taxa"][$taxonKey]['image']);
			}
			#flatten tids into an array
			$result['tids'][] = $taxon['tid'];
		}
		$result['totals'] = TaxaManager::getTaxaCounts($result['taxa']);

  }
  return $result;
}

$result = [];
if (array_key_exists("clid", $_GET) && $_GET["clid"] > -1) {
	$result = buildResult($_GET);
} elseif (array_key_exists("dynclid", $_GET) && $_GET["dynclid"] > -1) {
	$result = buildDynResult($_GET);
} else {
	#todo: generate error or redirect
}

array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
