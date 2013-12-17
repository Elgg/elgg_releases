<?php
/**
* Delete release
*/

$guid = (int) get_input('guid');
$release = get_entity($guid);

if (!elgg_instanceof($release, 'object', 'elgg_release')) {
	register_error("Cannot delete release: Invalid entity.");
}

if (!$release->canEdit()) {
	register_error("Cannot delete release: Unauthorized.");
}

if ($release->delete()) {
	system_message("Deleted release.");
}

forward('releases/');