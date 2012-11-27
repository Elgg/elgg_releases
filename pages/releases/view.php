<?php
/**
 * View a release
 */

$release = ElggRelease::getReleaseFromVersion(get_input('version'));

if (!elgg_instanceof($release, 'object', 'elgg_release')) {
	register_error(elgg_echo('noaccess'));
	$_SESSION['last_forward_from'] = current_page_url();
	forward('');
}

$branch = $release->getReleaseBranch();
elgg_push_breadcrumb("Elgg $branch", "/release/list/$branch");

$title = $release->title;
elgg_push_breadcrumb($title);

elgg_register_menu_item('title', array(
	'name' => 'download',
	'text' => 'Download',
	'href' => "releases/download/{$release->getVersion()}",
	'link_class' => 'elgg-button elgg-button-action',
));

$content = elgg_view_entity($release, array(
	'full_view' => true
));

// @todo add sidebar menu items for the release branches
$body = elgg_view_layout('content', array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
));

echo elgg_view_page($title, $body);
