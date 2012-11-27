<?php
/**
 * Icon for an Elgg Release
 */

$release = elgg_extract('entity', $vars);

$src = elgg_normalize_url('mod/elgg_releases/graphics/elgg_e_logo.png');
echo "<img src=\"$src\" width=\"40\" height=\"40\" />";