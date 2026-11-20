<?php
include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");

const MIN_QUERY_LENGTH = 3;
const MAX_QUERY_LENGTH = 100;

function getMatches($query, array $familyList) {
	$matches = array("family" => array(), "genus" => array());
	if (!$familyList) {
		return $matches;
	}

	$genusExpression = "CASE WHEN g.rankid = :genusRank THEN g.sciname ELSE g.unitname1 END";
	$baseQuery = function ($select, $searchCondition) use ($genusExpression, $query) {
		return SymbosuEntityManager::getEntityManager()
			->createQueryBuilder()
			->select($select)
			->from("Taxstatus", "ts")
			->innerJoin("Taxa", "g", "WITH", "g.tid = ts.tidaccepted")
			->innerJoin("fmchklsttaxalink", "tl", "WITH", "tl.tid = g.tid")
			->where("ts.taxauthid = :taxauthid")
			->andWhere("ts.tid = ts.tidaccepted")
			->andWhere("g.rankid >= :genusRank")
			->andWhere("tl.clid = :checklistId")
			->andWhere("ts.family IS NOT NULL")
			->andWhere("ts.family <> :emptyFamily")
			->andWhere($genusExpression . " <> :emptyGenus")
			->andWhere($searchCondition)
			->setParameter("taxauthid", 1)
			// Rank genus is 180
			->setParameter("genusRank", 180)
			// Checklist 1 belongs to OregonFlora Vascular Plants
			->setParameter("checklistId", 1)
			->setParameter("emptyFamily", "")
			->setParameter("emptyGenus", "")
			->setParameter("search", $query . "%");
	};

	$familyRows = $baseQuery("DISTINCT ts.family AS family", "ts.family LIKE :search")
		->andWhere("ts.family IN (:familyList)")
		// Filter family name for the query result through existing pdfs
		->setParameter("familyList", $familyList)
		->orderBy("ts.family", "ASC")
		->setMaxResults(20)
		->getQuery()
		->getArrayResult();

	/* Doctrine ORM 2.7.3 does not support CASE WHEN ... LIKE, so keep
	 * genus matches in explicit rank branches.
	 */
	$genusCondition = "(g.rankid = :genusRank AND g.sciname LIKE :search) OR " .
		"(g.rankid > :genusRank AND g.unitname1 LIKE :search)";
	$genusRows = $baseQuery(
		"DISTINCT ts.family AS family, " . $genusExpression . " AS genus",
		$genusCondition
	)
		->andWhere("ts.family IN (:familyList)")
		// Filter family name for the query result through existing pdfs
		->setParameter("familyList", $familyList)
		->orderBy("ts.family", "ASC")
		->addOrderBy("genus", "ASC")
		->setMaxResults(20)
		->getQuery()
		->getArrayResult();

	foreach ($familyRows as $row) {
		$matches["family"][] = array(
			"label" => $row["family"],
			"family" => $row["family"],
			"genus" => null
		);
	}

	foreach ($genusRows as $row) {
		$matches["genus"][] = array(
			"label" => $row["genus"] . " - " . $row["family"] . " (family)",
			"family" => $row["family"],
			"genus" => $row["genus"]
		);
	}

	return $matches;
}

header("Content-Type: application/json; charset=utf-8");

$query = isset($_GET["query"]) ? trim($_GET["query"]) : "";

if ($query === "" ||
	strlen($query) > MAX_QUERY_LENGTH ||
	strlen($query) < MIN_QUERY_LENGTH
) {
	echo json_encode(array("family" => array(), "genus" => array()));
	exit;
}

$familyListJson = file_get_contents($SERVER_ROOT . "/pages/data/family-pdf-list.json");
$familyList = $familyListJson === false ? null : json_decode($familyListJson, true);

if (!is_array($familyList)) {
	http_response_code(500);
	echo json_encode(array("error" => "Unable to load the family list."));
	exit;
}

echo json_encode(getMatches($query, $familyList), JSON_INVALID_UTF8_SUBSTITUTE);
?>
