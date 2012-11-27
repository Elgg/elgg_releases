<?php
/**
 * Add / edit release
 */

if ($release) {
	$title = "Edit release";
} else {
	$title = "Add release";
}

elgg_push_breadcrumb($title);

$vars = elgg_releases_prepare_form_vars($release);

$content = elgg_view_form('releases/save', array(), $vars);

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);