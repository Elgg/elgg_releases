<?php
/**
 * Save a release
 */

elgg_make_sticky_form('elgg_release');

$error = FALSE;
$error_forward_url = REFERER;

// edit or create a new entity
$guid = get_input('guid');

if ($guid) {
	$release = get_entity($guid);
	if (!elgg_instanceof($release, 'object', 'elgg_release') || !$release->canEdit()) {
		register_error("Invalid release.");
		forward(REFERER);
	}
} else {
	$release = new ElggRelease();
}

$values = array(
	'title' => '',
	'description' => '',
	'version' => '',
	'build_package' => false,
	'package_path' => false,
	'access_id' => ACCESS_PUBLIC
);

$required = array('title', 'description', 'version');

foreach ($values as $name => $default) {
	$value = get_input($name, $default);

	if (in_array($name, $required) && empty($value)) {
		$error = "$name cannot be empty.";
		break;
	}
	
	$values[$name] = $value;
}

if ($error) {
	register_error($error);
	forward(REFERER);
}

// need either build path or to repackage
if (!$values['build_package'] && !$values['package_path']) {
	$error = "Need to either build the package or to specify the package path.";
}


foreach ($values as $name => $value) {
	$release->$name = $value;
}

if ($values['build_package']) {
	if (!$release->package()) {
		register_error("Could not build package. Check that the version is tagged in GitHub.");
		forward(REFERER);
	}
} elseif ($values['package_path']) {
	$path = sanitise_filepath($values['package_path'], false);
	if (!file_exists($path)) {
		register_error("Package path is invalid or inaccessible.");
		forward(REFERER);
	}
	$release->setPackagePath($path);
}

if ($release->save()) {
	// remove sticky form entries
	elgg_clear_sticky_form('elgg_release');

	system_message("Release saved.");
	forward($release->getURL());
} else {
	register_error(elgg_echo('blog:error:cannot_save'));
	forward($error_forward_url);
}
