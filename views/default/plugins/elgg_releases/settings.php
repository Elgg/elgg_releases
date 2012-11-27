<?php
/**
 * Settings for elgg.org admin
 */

$plugin = elgg_extract('entity', $vars);


?>
<fieldset>
	<legend>Automated Release Management</legend>

	<label>Elgg Scripts repo checkout path (trailing slash):</label>
<?php
	echo elgg_view('input/text', array(
		'name' => 'params[elgg_scripts_path]',
		'value' => $plugin->elgg_scripts_path,
	));
?>

	<label>Build output dir (trailing slash):</label>
<?php
	echo elgg_view('input/text', array(
		'name' => 'params[build_output_dir]',
		'value' => $plugin->build_output_dir,
	));
?>
</fieldset>

<br />