<?php

/**
 * Returns API response data cached under $key if it exists and is not stale (i.e. was written less
 * than 24 hours ago), and false otherwise.
 */
function readFromCache($key) {
	global $TEMP_DIR_ROOT;
  	$cacheDir = $TEMP_DIR_ROOT . '/api-caches/';
	$cacheFile = $cacheDir . $key . '.json';
	$SECONDS_IN_24_HOURS = 86400;

	if (!is_dir($cacheDir)) {
		mkdir($cacheDir);
	}

	if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $SECONDS_IN_24_HOURS) {
		// cache hit, return contents
		return file_get_contents($cacheFile);
	} else {
		// cache miss
		return false;
	}
}

/**
 * Saves API response data to the cache under $key, overwriting any data that already exists. The
 * cache is shared among all requests this server handles, so any data written to the cache should
 * NOT be liable to change per session (for example, depending upon permissions of a logged-in
 * user).
 */
function writeToCache($key, $data) {
	global $TEMP_DIR_ROOT;
  $cacheDir = $TEMP_DIR_ROOT . '/api-caches/';
	$cacheFile = $cacheDir . $key . '.json';

	if (!is_dir($cacheDir)) {
		mkdir($cacheDir);
	}

  file_put_contents($cacheFile, $data, LOCK_EX);
}

?>
