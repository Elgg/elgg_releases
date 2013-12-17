<?php
/**
 * Set the subtypes for ElggRelease
 */

if (get_subtype_id('object', 'elgg_release')) {
	update_subtype('object', 'elgg_release', 'ElggRelease');
} else {
	add_subtype('object', 'elgg_release', 'ElggRelease');
}

if (!elgg_get_plugin_setting('github_endpoint', 'elgg_releases')) {
	$endpoint = substr(md5(rand()), 0, 8);
	elgg_set_plugin_setting('github_endpoint', $endpoint, 'elgg_releases');
}