<?php

require_once '../../engine/start.php';
//
//elgg_set_ignore_access(true);
//$options = array(
//	'type' => 'object',
//	'subtype' => 'elgg_release',
//	'limit' => 0
//);
//
//$releases = elgg_get_entities($options);
//foreach ($releases as $release) {
//	echo "Deleteing $release->title\n";
//	$release->delete();
//}
//
//exit;

function curl($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Elgg');
	// use this to avoid the 60 requests per hour (or so) rate limit.
//	curl_setopt($ch, CURLOPT_USERPWD, "username:password");
	$response = curl_exec($ch);
	curl_close($ch);

	return $response;
}

$response = curl('https://api.github.com/repos/elgg/elgg/tags');

if (!$response) {
	die("No response from GH.");
}

$tags = json_decode($response);

elgg_set_ignore_access(true);
$db_prefix = elgg_get_config('dbprefix');

foreach ($tags as $info) {
	echo "Creating release $info->name...";
	
	$release = new ElggRelease();
	if (!$release->setVersion($info->name)) {
		echo "version already exists!\n";
		continue;
	}
	$release->title = 'Elgg ' . $info->name;
	$release->description = "Update This";
	$release->owner_guid = 40;
	$release->access_id = ACCESS_PUBLIC;

	$path = elgg_get_plugin_setting('build_output_dir', 'elgg_releases')
			. "elgg-{$info->name}.zip";

//	$release->setPackagePath($path);

	if (file_exists($path)) {
		$release->setPackagePath($path);
	} else {
		Echo "No file $path. Building {$info->name}...";
		$release->package();
	}

	if ($release->save()) {
		$time = null;
		
		// get datetime for commit
		$commit_info = json_decode(curl($info->commit->url));

		if ($commit_info) {
			$date = $commit_info->commit->author->date;
			$time = strtotime($date);
		}

		if (!$time) {
			echo "Cannot set time. Be sure to change the time_created...";
		} else {
			$guid = $release->getGUID();
			$q = "UPDATE {$db_prefix}entities SET time_created = '$time' WHERE guid = $guid";
			if (!update_data($q)) {
				echo "Cannot set time. Be sure to change the time_created...";
			}
		}

		echo "Done!\n";
	} else {
		echo "FAIL.\n";
	}
}