<?php
include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/ProfileManager.php");

function getProfileData() {
  $pm = new ProfileManager();
  $pm->setUid($SYMB_UID);
  $person = $pm->getPerson();

  return [
    "firstName" => $person->getFirstName(),
    "lastName" => $person->getLastName(),
    "email" => $person->getEmail(),
    "title" => $person->getTitle(),
    "institution" => $person->getInstitution(),
    "department" => $person->getDepartment(),
  ];
}

function updateProfileData($params) {
  $pm = new ProfileManager();
  $pm->setUid($SYMB_UID);

  $data = [
    "firstname" => $params["firstName"],
    "lastname" => $params["lastName"],
    "email" => $params["email"],
    "title" => $params["title"],
    "institution" => $params["institution"],
    "department" => $params["department"],
  ];
  $status = $pm->updateProfileData($data);
}

function requestAccess($params) {
  // save reason string and request timestamp/etc
}

// $action = array_key_exists('action', $_REQUEST) ? htmlspecialchars($_REQUEST['action'], HTML_SPECIAL_CHARS_FLAGS) : '';
$type = $_SERVER['REQUEST_METHOD'];

$result = [];
if (isset($SYMB_UID)) {
  if ($type === "GET") {
    $result = getProfileData();
  } else if ($type === "POST") {
    updateProfileData($_POST); // TODO: set status etc from result
  }
}

array_walk_recursive($result, 'cleanWindowsRecursive'); #replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
?>
