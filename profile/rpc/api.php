<?php
include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/ProfileManager.php");

ProfileManager::refreshUserRights();

function loggedInUserHasAccess() {
  global $USER_RIGHTS;
  if (isset($USER_RIGHTS) &&
    (array_key_exists('SuperAdmin', $USER_RIGHTS) ||
    array_key_exists('CollAdmin', $USER_RIGHTS) ||
    array_key_exists('RareSppAdmin', $USER_RIGHTS) ||
    array_key_exists('RareSppReadAll', $USER_RIGHTS))
  ) {
    // TODO: check for specific collection IDs with CollEditor and RareSppReader?
    return true;
  }
  return false;
}

function getProfileDataForRequestingAccess($uid) {
  if (loggedInUserHasAccess()) {
    return ["accessGranted" => true];
  }

  $pm = new ProfileManager();
  $pm->setUid($uid);

  if ($pm->hasRequestedRareSpeciesAccess()) {
    return ["accessRequested" => true];
  }

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

function updateProfileAndRequestAccess($uid, $params) {
  if (loggedInUserHasAccess()) {
    return ["accessGranted" => true];
  }

  $pm = new ProfileManager();
  $pm->setUid($uid);

  /**
   * 2024-09-20(eric): for testing only, remove before deploying
   */
  if (array_key_exists("delete", $params)) {
    $pm->deleteRareSpeciesAccessRequest();
    return [];
  }

  if (
    !array_key_exists("firstName", $params) ||
    !array_key_exists("lastName", $params) ||
    !array_key_exists("email", $params) ||
    !array_key_exists("title", $params) ||
    !array_key_exists("institution", $params) ||
    !array_key_exists("reason", $params)
  ) {
    return ["error" => "missing required field"];
  }

  if (strlen($params["reason"]) > 250) {
    return ["error" => "reason must be 250 characters or less"];
  }

  $data = [
    "firstName" => $params["firstName"],
    "lastName" => $params["lastName"],
    "email" => $params["email"],
    "title" => $params["title"],
    "institution" => $params["institution"],
    "department" => array_key_exists("department", $params) ? $params["department"] : null,
  ];

  $status = $pm->requestRareSpeciesAccess($params["reason"], time(), $data);
  if (!$status) {
    return ["error" => "error requesting access"];
  }

  return ["accessRequested" => true];
}

$type = $_SERVER['REQUEST_METHOD'];

$result = [];
if (isset($SYMB_UID) && $SYMB_UID) {
  if ($type === "GET") {
    $result = getProfileDataForRequestingAccess($SYMB_UID);
  } else if ($type === "POST") {
    $result = updateProfileAndRequestAccess($SYMB_UID, $_POST);
  }
}

array_walk_recursive($result, 'cleanWindowsRecursive'); #replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
?>
