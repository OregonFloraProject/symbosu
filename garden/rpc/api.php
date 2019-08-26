<?php
  include_once("../../config/symbini.php");
  include_once($SERVER_ROOT . "/config/dbconnection.php");

  /**
   * Runs the given query & returns the results as an array of associative arrays
   */
  function run_query($sql) {
    $conn = MySQLiConnectionFactory::getCon("readonly");
    $outResults = [];

    if ($conn !== null) {
      $res = $conn->query($sql);
      if ($res) {
        while($row = $res->fetch_assoc()) {
          array_push($outResults, $row);
        }
        $res->free();
      }

      $conn->close();
    }

    return $outResults;
  }

  /**
   * Returns the most prominent image for the given taxa ID
   */
  function get_image_for_tid($tid) {
    $sql = "SELECT i.thumbnailurl FROM images AS i WHERE tid = $tid ORDER BY i.sortsequence LIMIT 1;";
    $res = run_query($sql);

    if (count($res) > 0 && key_exists("thumbnailurl", $res[0])) {
      $result = $res[0]["thumbnailurl"];

      if (substr($result, 0, 4) !== "http") {
        if (key_exists("IMAGE_DOMAIN", $GLOBALS) && $GLOBALS["IMAGE_DOMAIN"] !== "") {
          $result = $GLOBALS["IMAGE_DOMAIN"] . $result;
        } else if (key_exists("IMAGE_ROOT_URL", $GLOBALS)) {
          $result = $GLOBALS["IMAGE_ROOT_URL"] . $result;
        }
      }

      return $result;
    }

    return "";
  }

  /**
   * Returns all unique taxa with thumbnail urls
   */
  function get_taxa($params) {
    if (!key_exists("search", $params)) { $params["search"] = null; }
    if (!key_exists("sunlight", $params)) { $params["sunlight"] = null; }
    if (!key_exists("moisture", $params)) { $params["moisture"] = null; }
    if (!key_exists("width", $params)) { $params["width"] = null; }
    if (!key_exists("height", $params)) { $params["height"] = null; }

    # If all args are null, quit here
    if ($params["search"] === null &&
        $params["sunlight"] === null &&
        $params["moisture"] === null &&
        $params["width"] === null &&
        $params["height"] === null) {
      return [];
    }

    # Select all species & below (t.rankid >= 200) that have some sort of name
    $sql = "SELECT t.tid, t.sciname, v.vernacularname FROM taxa as t ";
    $sql .= "LEFT JOIN taxavernaculars AS v ON t.tid = v.tid ";
    $sql .= "WHERE t.rankid >= 220 AND (t.sciname IS NOT NULL OR v.vernacularname IS NOT NULL) ";

    if ($params["search"] !== null) {
      $search = $params["search"];
      $sql .= "AND (t.sciname LIKE '%$search%' OR v.vernacularname LIKE '%$search%') ";
    }

    $sql .= "GROUP BY t.tid ORDER BY t.sciname;";

    $resultsTmp = run_query($sql);
    $results = [];

    // Populate image urls
    foreach ($resultsTmp as $result) {
      $result["image"] = get_image_for_tid($result["tid"]);
      if ($result["image"] !== "") {
        array_push($results, $result);
      }
    }

    return $results;
  }

  // Begin View
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode(get_taxa($_GET), JSON_NUMERIC_CHECK);
?>

