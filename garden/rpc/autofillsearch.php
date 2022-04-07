<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once($SERVER_ROOT . "/config/SymbosuEntityManager.php");

$CLID_GARDEN_ALL = 54;
$RANK_GENUS = 180;
$results = [];

if (array_key_exists("q", $_REQUEST)) {
	$omit = [-1];
	if (array_key_exists("omit", $_REQUEST)) {
		$omit = array_map('intval',explode(',',$_REQUEST['omit']));
	}
  $em = SymbosuEntityManager::getEntityManager();
  $q = $em->createQueryBuilder();

  $sciNameResults = $em->createQueryBuilder()
    ->select("t.sciname as text, t.tid as value")
    ->from("Taxa", "t")
    ->innerJoin("Fmchklsttaxalink", "tl", "WITH", "t.tid = tl.tid")
    ->where("tl.clid = $CLID_GARDEN_ALL")
    ->andWhere("t.sciname LIKE :search")
    ->andWhere("t.rankid > $RANK_GENUS")
    ->andWhere("t.tid NOT IN (:omit)")
    ->groupBy("t.tid")
    ->setParameter("search",  "%" . $_REQUEST["q"] . '%')
    ->setParameter("omit",$omit)
    ->setMaxResults(3)
    ->getQuery()
    ->getArrayResult();

  $vernacularResults = $em->createQueryBuilder()
    ->select("v.vernacularname as text", "t.tid as value")
    ->from("Taxa", "t")
    ->innerJoin("Taxavernaculars", "v", "WITH", "t.tid = v.tid")
    ->innerJoin("Fmchklsttaxalink", "tl", "WITH", "t.tid = tl.tid")
    ->where("tl.clid = $CLID_GARDEN_ALL")
    ->andWhere("v.vernacularname LIKE :search")
    ->andWhere("t.rankid > $RANK_GENUS")
    ->andWhere("t.tid NOT IN (:omit)")
    ->groupBy("v.vernacularname")
    ->setParameter("search",  "%" . $_REQUEST["q"] . '%')
    ->setParameter("omit",$omit)
    ->orderBy("v.sortsequence")
    ->setMaxResults(3)
    ->getQuery()
    ->getArrayResult();

  $results = array_merge($sciNameResults, $vernacularResults);
  usort($results, function ($a, $b) {
    return strcmp($a["text"], $b["text"]);
  });
}

array_walk_recursive($results,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($results);
?>
