<?php
/**
 * Save a release
 */

$title = elgg_extract('title', $vars);
$description = elgg_extract('description', $vars);
$version = elgg_extract('version', $vars);
$build_package = elgg_extract('build_package', $vars);
$package_path = elgg_extract('package_path', $vars);

$guid = elgg_extract('guid', $vars);

?>
<div>
	<label>Title ("Elgg 1.X.X")</label><br />
	<?php
		echo elgg_view('input/text', array(
			'name' => 'title',
			'value' => $title
			));
	?>
</div>

<div>
	<label>Version ("1.8.10")</label><br />
	<?php
		echo elgg_view('input/text', array(
			'name' => 'version',
			'value' => $version
		));
	?>
</div>

<div>
	<label>Release Notes (in MD.)</label>
	<?php
		echo elgg_view('input/plaintext', array(
			'name' => 'description',
			'value' => $description
		));
	?>
</div>

<div>
	<label>Pre-built package location (Full path to zip file. Not required if building a package.)</label><br />
	<?php
		echo elgg_view('input/text', array(
			'name' => 'package_path',
			'value' => $package_path
		));
	?>
</div>

<div>
	<label>
	<?php
		echo elgg_view('input/checkbox', array(
			'name' => 'build_package',
			'value' => 1
		));
	?>

	Build package using the version tag.
	
	</label>
</div>

<div class="elgg-foot">
<?php

if ($guid) {
	echo elgg_view('input/hidden', array('name' => 'guid', 'value' => $guid));
}

echo elgg_view('input/submit', array('value' => 'Save'));

?>
</div>
