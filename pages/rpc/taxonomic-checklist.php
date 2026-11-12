<?php
include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");

header("Content-Type: application/json; charset=utf-8");

$mode = isset($_GET["mode"]) ? $_GET["mode"] : "";
$query = isset($_GET["query"]) ? trim($_GET["query"]) : "";

if (!in_array($mode, array("family", "genus"), true)) {
	http_response_code(400);
	echo json_encode(array("error" => "Invalid search mode."));
	exit;
}

if ($query === "" || strlen($query) > 100) {
	echo json_encode(array());
	exit;
}

$genusExpression = "CASE WHEN g.rankid = 180 THEN g.sciname ELSE g.unitname1 END";
$searchCondition = $mode === "family"
	? "ts.family LIKE :search"
	: "(g.rankid = :genusRank AND g.sciname LIKE :search) OR (g.rankid > :genusRank AND g.unitname1 LIKE :search)";
$validGenusCondition = "(g.rankid = :genusRank AND g.sciname <> :emptyGenus) OR (g.rankid > :genusRank AND g.unitname1 <> :emptyGenus)";
$em = SymbosuEntityManager::getEntityManager();
$queryBuilder = $em->createQueryBuilder();
$rows = $queryBuilder
	->select("DISTINCT ts.family AS family", "$genusExpression AS genus")
	->from("Taxstatus", "ts")
	->innerJoin("Taxa", "g", "WITH", "g.tid = ts.tidaccepted")
	->innerJoin("fmchklsttaxalink", "tl", "WITH", "tl.tid = g.tid")
	->where("ts.taxauthid = :taxauthid")
	->andWhere("ts.tid = ts.tidaccepted")
	->andWhere("g.rankid >= :genusRank")
	->andWhere("tl.clid = :checklistId")
	->andWhere("ts.family IS NOT NULL")
	->andWhere("ts.family <> :emptyFamily")
	->andWhere($validGenusCondition)
	->andWhere($searchCondition)
	->setParameter("taxauthid", 1)
	->setParameter("genusRank", 180)
	->setParameter("checklistId", 1)
	->setParameter("emptyFamily", "")
	->setParameter("emptyGenus", "")
	->setParameter("search", $query . "%")
	->orderBy("ts.family", "ASC")
	->addOrderBy("genus", "ASC")
	->setMaxResults(30)
	->getQuery()
	->getArrayResult();

$matches = array();

foreach ($rows as $row) {
	$matches[] = array(
		"label" => $row["family"] . " - " . $row["genus"],
		"name" => $mode === "family" ? $row["family"] : $row["genus"]
	);
}

echo json_encode($matches, JSON_INVALID_UTF8_SUBSTITUTE);
?>
