<?php
include_once("../../config/symbini.php");

include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");

$result = [];

function getTaxon($tid, $queryType = "default") {
  $em = SymbosuEntityManager::getEntityManager();
  $taxaRepo = $em->getRepository("Taxa");
  $taxaModel = $taxaRepo->find($tid);
  $taxa = TaxaManager::fromModel($taxaModel);
  return taxaManagerToJSON($taxa,$queryType);
}

function searchTaxa($searchTerm) {
  $results = [];
  $searchTerm = trim($searchTerm);
  if (strlen($searchTerm) < 3) {
    return $results;
  }

  $em = SymbosuEntityManager::getEntityManager();
  $taxaRepo = $em->getRepository("Taxa");

  // first search for a single exact match
  $exactMatchResults = $taxaRepo->createQueryBuilder("t")
    ->where("t.sciname = :search")
    ->groupBy("t.tid")
    ->setParameter("search", $searchTerm)
    ->getQuery()
    ->getResult();

  // next search for coarse vernacularname matches; we can only use the exact match above if there
  // are no vernacularname matches
  $expr = $em->getExpressionBuilder();
  $vernacularResults = $taxaRepo->createQueryBuilder("t")
    ->where($expr->in(
      "t.tid",
      $em->createQueryBuilder()
        ->select("ts.tidaccepted")
        ->from("Taxavernaculars", "v")
        ->innerJoin("Taxstatus", "ts", "WITH", "v.tid = ts.tid")
        ->orWhere("v.vernacularname LIKE :search")
        ->groupBy("ts.tidaccepted")
        ->getDQL()
    ))
    ->orderBy("t.sciname")
    ->setParameter("search", '%' . $searchTerm . '%')
    ->getQuery()
    ->getResult();

  // if there is a single exact match, just return it; taxa/search.jsx will redirect.
  // if not, we need to do a broader sciname search as well, and return all the coarse results
  if ($exactMatchResults !== null
    && $vernacularResults !== null
    && count($exactMatchResults) === 1
    && count($vernacularResults) === 0
  ) {
    $t = $exactMatchResults[0];
    $tm = TaxaManager::fromModel($t);
    $tj = taxaManagerToJSON($tm,"default",true);

    // if this is a unique synonym, include tidaccepted so we can redirect appropriately
    $tsResults = getTaxStatus($t->getTid());
    if (count($tsResults) === 1 && $tsResults[0]->getTidAccepted() !== $t->getTid()) {
      $tj["tidaccepted"] = $tsResults[0]->getTidAccepted();
    }

    array_push($results, $tj);
  } else {
    $expr = $em->getExpressionBuilder();
    $sciNameResults = $taxaRepo->createQueryBuilder("t")
      ->where($expr->in(
        "t.tid",
        $em->createQueryBuilder()
          ->select("ts.tidaccepted")
          ->from("Taxa", "t0")
          ->innerJoin("Taxstatus", "ts", "WITH", "t0.tid = ts.tid")
          ->orWhere("t0.sciname LIKE :search")
          ->groupBy("ts.tidaccepted")
          ->getDQL()
      ))
      ->orderBy("t.sciname")
      ->setParameter("search", '%' . $searchTerm . '%')
      ->getQuery()
      ->getResult();

    if ($sciNameResults !== null && $vernacularResults !== null) {
      $taxaResults = array_merge($sciNameResults, $vernacularResults);
      usort($taxaResults, function ($a, $b) { return strcasecmp($a->getSciname(), $b->getSciname()); });

      // strip duplicates, since we're now searching for sciname and vernacular name separately
      $tids = [];
      foreach ($taxaResults as $t) {
        if (isset($tids[$t->getTid()])) continue;
        $tids[$t->getTid()] = true;
        $tm = TaxaManager::fromModel($t);
        $tj = taxaManagerToJSON($tm,"default",true);
        array_push($results, $tj);
      }
    }
  }

  return $results;
}

function getTaxStatus($tid) {
  $tsRepo = SymbosuEntityManager::getEntityManager()->getRepository("Taxstatus");
  $tsResults = $tsRepo->createQueryBuilder("ts")
    ->orWhere("ts.tid = :tid")
    ->setParameter(":tid", $tid)
    ->getQuery()
    ->getResult();
  return $tsResults;
}

#if a synonym value is passed, determine whether the synonym tid is unique in TaxStatus
#if not, we will redirect in taxa/main.jsx
function checkSynonym($synonym) {
  $tsResults = getTaxStatus(intval($synonym));
  return ["count" => sizeof($tsResults)];
}

function getSubTaxa($parentTid) {#not sure this happens anymore
  $results = [];
  $taxaRepo = SymbosuEntityManager::getEntityManager()->getRepository("Taxa");
  $taxaResults = $taxaRepo->createQueryBuilder("t")
    ->innerJoin("Taxaenumtree", "te", "WITH", "t.tid = te.tid")
    ->where("te.parenttid = :parenttid")
    ->groupBy("t.tid")
    ->setParameter("parenttid", $parentTid)
    ->getQuery()
    ->getResult();

  if ($taxaResults != null) {
    foreach ($taxaResults as $t) {
      $tm = TaxaManager::fromModel($t);
      $tj = taxaManagerToJSON($tm,"default");
      array_push($results, $tj);
    }
  }

  return $results;
}
  
function taxaManagerToJSON($taxaObj,$queryType = "default",$minimalData = false) {
	$result = TaxaManager::getEmptyTaxon();
  $taxaRepo = SymbosuEntityManager::getEntityManager()->getRepository("Taxa");

	if ($taxaObj !== null) {
		$result["tid"] = $taxaObj->getTid();
		$result["sciname"] = $taxaObj->getSciname();
		$result["parentTid"] = $taxaObj->getParentTid(); 
		$result["author"] = $taxaObj->getAuthor();
		$result['imagesBasis'] = [];
		$result['imagesBasis']['HumanObservation'] = [];
		$result['imagesBasis']['PreservedSpecimen'] = [];
		$result['imagesBasis']['LivingSpecimen'] = [];
		$taxaRankId = $taxaObj->getRankId();
		$result["rankId"] = $taxaRankId;
		$result["vernacular"] = [
			"basename" => $taxaObj->getBasename(),
			"names" => $taxaObj->getVernacularNames()
		];

		if ($minimalData === false) {#default
			$spp = $taxaObj->getSpp(); 
			foreach($spp as $rowArr){
				$taxaModel = $taxaRepo->find($rowArr['tid']);
				$taxa = TaxaManager::fromModel($taxaModel);
				$tj = taxaManagerToJSON($taxa,$queryType,true);
				if (!isset($result["spp"])) {
					$result['spp'] = [];
				}
				$result["spp"][] = $tj;
			}
			$result["synonyms"] = $taxaObj->getSynonyms();
			$result["acceptedSynonyms"] = $taxaObj->getAcceptedSynonyms();
			$vernacular = [];#flatten the vernacular array
			foreach ($result["acceptedSynonyms"] as $tid => $arr) {
				if (empty($result['vernacular']['basename']) && !empty($arr['vernacular']['basename'])) {
					$result['vernacular']['basename'] = $arr['vernacular']['basename'];
				}
				$vernacular = array_merge($vernacular,$arr['vernacular']['names']);
			}
    	$result['vernacular']['names'] = array_merge($result['vernacular']['names'],$vernacular);
			$result['vernacular']['names'] = array_unique($result['vernacular']['names']);
			
			$result["origin"] = $taxaObj->getOrigin();
			$result["family"] = $taxaObj->getFamily();
			$result["rarePlantFactSheet"] = $taxaObj->getRarePlantFactSheet();
			global $RPG_FLAG;
			if ($RPG_FLAG) {
				$result["accessRestricted"] = $taxaObj->getAccessRestricted();

				if ($queryType === 'rare') {
					$result["associations"] = $taxaObj->getAssociations();
				}
			}
			if ($queryType !== 'default') {
				$result["characteristics"] = $taxaObj->getCharacteristics($queryType);
			}
			$taxaObj->setSpecialChecklists();
			$result["specialChecklists"] = $taxaObj->getSpecialChecklists();
			$result["descriptions"] = $taxaObj->getDescriptions();
			$result["gardenDescription"] = $taxaObj->getGardenDescription();
			$result["gardenId"] = $taxaObj->getGardenId();
			$result["taxalinks"] = $taxaObj->getTaxalinks();
			foreach ($result["taxalinks"] as $idx => $taxalink) {
				$result["taxalinks"][$idx]['url'] = str_replace("--SCINAME--",$result["sciname"],$taxalink['url']);
			}
			if ($taxaRankId > 180) {
				$taxaObj->setImages();
				$allImages = $taxaObj->getImagesByBasisOfRecord();
				$result["imagesBasis"]['HumanObservation'] = (isset($allImages['HumanObservation']) ? $allImages['HumanObservation'] : []);
				$result["imagesBasis"]['PreservedSpecimen'] = (isset($allImages['PreservedSpecimen']) ? $allImages['PreservedSpecimen'] : []);
				$result["imagesBasis"]['LivingSpecimen'] = (isset($allImages['LivingSpecimen']) ? $allImages['LivingSpecimen'] : []);
			}

		}elseif($minimalData === true){#just need one image, as in SPP or searchTaxa()
			$taxaObj->setSingleImage();
			$images = $taxaObj->getImage();
			if (sizeof($images) === 0 || $images[0] === null) {
				$spp = $taxaObj->getSpp();
				foreach($spp as $rowArr){
					$sppTaxaModel = $taxaRepo->find($rowArr['tid']);
					$sppTaxa = TaxaManager::fromModel($sppTaxaModel);
					$sppImages = $sppTaxa->getImage();
					if (is_array($sppImages) && isset($sppImages[0]) && isset($sppImages[0]['imgid'])) {
						$result['imagesBasis'][$sppImages[0]['basisofrecord']] = $sppImages;
						break;
					}
				}	
			}else{
				$result['imagesBasis'][$images[0]['basisofrecord']] = $images;
			}
		}
	}
	return $result;
}
$result = [];
if (array_key_exists("search", $_GET)) {
  $result = searchTaxa(trim($_GET["search"]));
} else if (array_key_exists("taxon", $_GET) && is_numeric($_GET["taxon"])) {
  if (array_key_exists("type", $_GET) && $_GET["type"] === "rare") {
    if (isset($RPG_FLAG) && $RPG_FLAG === 1) {
      $result = getTaxon($_GET["taxon"], "rare");
    }
  } else if (array_key_exists("type", $_GET) && $_GET["type"] === "garden") {
    $result = getTaxon($_GET["taxon"], "garden");
  } else {
    $result = getTaxon($_GET["taxon"]);
  }
} else if (array_key_exists("family", $_GET) && is_numeric($_GET["family"])) {
  $result = getSubTaxa($_GET["family"]);
} else if (array_key_exists("genus", $_GET) && is_numeric($_GET["genus"])) {
  $result = getSubTaxa($_GET["genus"]);
} else if (array_key_exists("synonym", $_GET) && is_numeric($_GET["synonym"])) {
  $result = checkSynonym($_GET["synonym"]);
}

array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
?>