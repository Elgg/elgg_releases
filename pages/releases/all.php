<?php
/**
 * List all releases
 */

elgg_register_title_button();

$content = elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'elgg_release',
	'full_view' => false,
	'view_toggle_type' => false,
	'order_by' => 'e.time_created desc'
));

if (!$content) {
	$content = "No releases";
}

$title = "Elgg Releases";

$body = elgg_view_layout('content', array(
	'filter' => false,
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);