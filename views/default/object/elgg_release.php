<?php
/**
 * Elgg Release view
 */
$full = elgg_extract('full_view', $vars, FALSE);
$release = elgg_extract('entity', $vars, FALSE);

if (!$release) {
	return;
}

$description = elgg_view('output/longtext', array(
	'value' => $release->description,
	'class' => 'pbl'
));

$date = date('Y-m-d @ g:ia', $release->time_created);

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'releases',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$icon = elgg_view_entity_icon($release);

if ($full) {
	$params = array(
		'entity' => $release,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $date,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	$body = <<<HTML
<div class="release elgg-content mts">
	<span class="elgg-heading-basic mbs">$link</span>
	$description
</div>
HTML;

	echo elgg_view('object/elements/full', array(
		'entity' => $release,
//		'icon' => $icon,
		'summary' => $summary,
		'body' => $body
	));
} else {
	// brief view
	$excerpt = elgg_get_excerpt($release->description);

	$params = array(
		'entity' => $release,
		'metadata' => $metadata,
		'subtitle' => $date,
//		'content' => $excerpt,
	);
	$params = $params + $vars;
	$body = elgg_view('object/elements/summary', $params);

	echo elgg_view_image_block($icon, $body);
}