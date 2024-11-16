<?php

include_once("../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once($SERVER_ROOT . "/config/SymbosuEntityManager.php");

$RANK_FAMILY = 140;
$RANK_GENUS = 180;

$MAX_RESULTS_PER_TYPE = 15;

$results = [];

function createSciNameQueryBuilder($em) {
  global $RANK_FAMILY;
  return $em->createQueryBuilder()
    ->select("t.sciname as text", "t.tid as taxonId", "t.rankid as rankId", "ts.tidaccepted")
    ->from("Taxa", "t")
    ->innerJoin("Taxstatus", "ts", "WITH", "t.tid = ts.tid")
    // Restrict to taxa that are contained in a checklist
    ->innerJoin("fmchklsttaxalink", "cl", "WITH", "t.tid = cl.tid")
    ->where("t.sciname LIKE :search")
    ->andWhere("t.rankid >= $RANK_FAMILY")
    // Restrict to Oregon vascular plant taxa contained in the State of Oregon checklist (clid=1)
    ->andWhere("cl.clid = 1")
    ->groupBy("t.tid");
}

function createVernacularNameQueryBuilder($em) {
  global $RANK_FAMILY;
  return $em->createQueryBuilder()
    ->select("v.vernacularname as text", "t.tid as taxonId", "t.rankid as rankId", "ts.tidaccepted")
    ->from("Taxa", "t")
    ->innerJoin("Taxavernaculars", "v", "WITH", "t.tid = v.tid")
    ->innerJoin("Taxstatus", "ts", "WITH", "t.tid = ts.tid")
    // Restrict to taxa that are contained in a checklist
    ->innerJoin("fmchklsttaxalink", "cl", "WITH", "t.tid = cl.tid")
    ->where("v.vernacularname LIKE :search")
    ->andWhere("t.rankid >= $RANK_FAMILY")
    // Restrict to Oregon vascular plant taxa contained in the State of Oregon checklist (cl=1)
    ->andWhere("cl.clid = 1")
    ->groupBy("v.vernacularname")
    ->orderBy("v.sortsequence");
}

if (array_key_exists("q", $_REQUEST)) {
	$query = trim($_REQUEST["q"]);

  $em = SymbosuEntityManager::getEntityManager();

  $queryWords = explode(" ", $query);
  $queryWords = array_map(function($word) { return $word.'%'; }, $queryWords);
  $queryGeneralized = implode(" ", $queryWords);


  $sciNameResults = createSciNameQueryBuilder($em)
    ->setParameter("search", $queryGeneralized)
    ->setMaxResults($MAX_RESULTS_PER_TYPE)
    ->getQuery()
    ->getArrayResult();

  // keep mid-string results separate so we can sort them separately
  $extraSciNameResults = [];
  if (count($sciNameResults) < $MAX_RESULTS_PER_TYPE) {
    $numResultsNeeded = min($MAX_RESULTS_PER_TYPE, $MAX_RESULTS_PER_TYPE - count($sciNameResults));
    $qb = createSciNameQueryBuilder($em)
      ->setParameter("search", '%' . $query . '%')
      ->setMaxResults($numResultsNeeded);

    // if we already have results, avoid duplicates
    if (count($sciNameResults) > 0) {
      $tids = [];
      foreach ($sciNameResults as $result) {
        $tids[] = $result["taxonId"];
      }
      $qb->andWhere("t.tid NOT IN (:tids)")
        ->setParameter("tids", $tids);
    }

    $extraSciNameResults = array_merge($extraSciNameResults, $qb->getQuery()->getArrayResult());
  }


  $vernacularResults = createVernacularNameQueryBuilder($em)
    ->setParameter("search", $queryGeneralized)
    ->setMaxResults($MAX_RESULTS_PER_TYPE)
    ->getQuery()
    ->getArrayResult();

  // keep mid-string results separate so we can sort them separately
  $extraVernacularResults = [];
  if (count($vernacularResults) < $MAX_RESULTS_PER_TYPE) {
    $numResultsNeeded = min($MAX_RESULTS_PER_TYPE, $MAX_RESULTS_PER_TYPE - count($vernacularResults));
    $qb = createVernacularNameQueryBuilder($em)
      ->setParameter("search", '%' . $query . '%')
      ->setMaxResults($numResultsNeeded);

    // if we already have results, avoid duplicates
    if (count($vernacularResults) > 0) {
      $tids = [];
      foreach ($vernacularResults as $result) {
        $tids[] = $result["text"];
      }
      $qb->andWhere("v.vernacularname NOT IN (:tids)")
        ->setParameter("tids", $tids);
    }

    $extraVernacularResults = array_merge($extraVernacularResults, $qb->getQuery()->getArrayResult());
  }


  // keep the results beginning with the search string at the top of the list, then sort the two
  // sub-lists in alphabetical order
  $resultsQueryBegin = array_merge($sciNameResults, $vernacularResults);
  $resultsQueryMiddle = array_merge($extraSciNameResults, $extraVernacularResults);
  usort($resultsQueryBegin, 'sortByTextValues');
  usort($resultsQueryMiddle, 'sortByTextValues');

  $duplicates = array_uintersect($sciNameResults, $vernacularResults,'compareTextValues');
  $duplicates = array_merge($duplicates, array_uintersect($extraSciNameResults, $extraVernacularResults,'compareTextValues'));
  $duplicateIndexes = [];

  $results = array_merge($resultsQueryBegin, $resultsQueryMiddle);
	if ($duplicates) {#overlap between sciname and common name 
		foreach ($results as $idx => $result) {
			foreach ($duplicates as $dupIdx => $duplicate) {
				if (strcasecmp($result['text'],$duplicate['text']) == 0) {
					#remove all dupes
					unset($results[$idx]);
          // since the array is already sorted, save the first index so we can re-insert a generic
          // search entry at the same place
          if (!isset($duplicateIndexes[$dupIdx])) {
            $duplicateIndexes[$dupIdx] = $idx;
          }
				}
			}
		}
		foreach ($duplicates as $dupIdx => $duplicate) {#re-add one entry for dupe as generic search
			$results[$duplicateIndexes[$dupIdx]] = array(
				"text"	=>	$duplicate['text'],
				"taxonId" => null,
				"rankId" => null
			);
		}
	}elseif (sizeof($vernacularResults) > 1) {#check for overlap within common
		#find the shortest value and remove its taxonId value, so that home/main.jsx treats it as generic text search
		$text_lengths = array_map("strlen",array_column($vernacularResults,"text"));
		$target_length = min($text_lengths);
		foreach ($results as $idx => $result) {
			if (strlen($result['text']) === $target_length ) {
				$results[$idx]['taxonId'] = null;
				$results[$idx]['rankId'] = null;
			}
		}
	}

  // re-sort the array by keys, since we've now added new items out of order, and then use
  // array_values to strip out missing keys from duplicates
  ksort($results, SORT_NUMERIC);
  $results = array_values($results);
}

function stripNonAlpha($str) {
	return preg_replace("/[^A-Za-z]/", '', $str);
}
#https://stackoverflow.com/questions/5653241/using-array-intersect-on-a-multi-dimensional-array#5653507
function compareTextValues($val1,$val2) {
	return strcasecmp($val1['text'],$val2['text']);
}
function sortByTextValues($a, $b) {
  return strcasecmp(stripNonAlpha($a["text"]), stripNonAlpha($b["text"]));
}

array_walk_recursive($results,'cleanWindowsRecursive');
header("Content-Type: application/json; charset=utf-8");
echo json_encode($results);
?>