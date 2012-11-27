<?php
/**
 * List all releases
 */

$version = get_input('version');

elgg_register_title_button();

$content = elgg_list_entities_from_metadata(array(
	'type' => 'object',
	'subtype' => 'elgg_release',
	'full_view' => false,
	'view_toggle_type' => false,
	'metadata_name' => 'release_branch',
	'metadata_value' => $version
));

if (!$content) {
	$content = "No releases for $version";
} else {
	elgg_push_breadcrumb("Elgg $version");
}

$title = "Elgg $version Releases";

$body = elgg_view_layout('content', array(
	'filter' => false,
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);