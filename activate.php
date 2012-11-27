<?php
/**
 * Set the subtypes for ElggRelease
 */

if (get_subtype_id('object', 'elgg_release')) {
	update_subtype('object', 'elgg_release', 'ElggRelease');
} else {
	add_subtype('object', 'elgg_release', 'ElggRelease');
}