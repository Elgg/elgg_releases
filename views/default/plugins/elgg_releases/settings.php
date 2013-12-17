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

<label>Endpoint for GitHub API to POST to:</label>
<span class="elgg-text-help">
	Use as <?php echo elgg_get_config('site')->url; ?>&lt;endpoint&gt; Keep it as a secret to avoid
	spoofed IP addresses from being able to build packages.
</span>
<?php
	echo elgg_view('input/text', array(
		'name' => 'params[github_endpoint]',
		'value' => $plugin->github_endpoint,
	));
?>
</fieldset>

<br />