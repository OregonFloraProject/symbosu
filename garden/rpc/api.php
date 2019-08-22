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
      }

      $conn->close();
    }

    return $outResults;
  }

  /**
   * Returns the most prominent image for the given taxa ID
   */
  function get_image_for_tid($tid) {
    $sql = "SELECT i.thumbnailurl FROM images AS i WHERE tid = $tid ORDER BY i.sortsequence LIMIT 1";
    $res = run_query($sql);

    if (sizeof($res) > 0) {
      return $res[0]["thumbnailurl"];
    }

    return "";
  }

  $results = [];

  $sql = "SELECT t.tid, t.sciname, v.vernacularname FROM taxa as t ";
  $sql .= "LEFT JOIN taxavernaculars AS v ON t.tid = v.tid ";

  // Request vars
  $argSearch = "";
  $argSunlight = "";
  $argMoisture = "";

  // If 'search' is included in request vars, just search by name
  if (key_exists("search", $_GET)) {
    // Populate taxa data
    $sql .= "WHERE t.sciname LIKE '%" . $_GET['search'] . "%' GROUP BY t.tid ORDER BY t.tid;";
    $resultsTmp = run_query($sql);

    // Populate image urls
    foreach ($resultsTmp as $result) {
      $result["image"] = get_image_for_tid($result["tid"]);
      if ($result["image"] !== "") {
        array_push($results, $result);
      }
    }

  } else {
    // Validate request vars
    if (key_exists("sunlight", $_GET) && in_array($_GET["sunlight"], ["any", "sun", "part-shade", "full-shade"])) {
      $argSunlight = $_GET["sunlight"];
    } else {
      $argSunlight = "any";
    }

    if (key_exists("moisture", $_GET) && in_array($_GET["moisture"], ["any", "wet", "moist", "dry"])) {
      $argMoisture = $_GET["moisture"];
    } else {
      $argMoisture = "any";
    }

  }

  // Begin View
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($results, JSON_NUMERIC_CHECK);
?>

