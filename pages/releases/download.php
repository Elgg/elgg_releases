<?php
/**
 * Download release.
 */

if (!$mime) {
	$mime = "application/zip";
}

$package_path = $release->getPackagePath();
$bits = explode('/', $package_path);
$filename = array_pop($bits);

// fix for IE https issue
header("Pragma: public");
header("Content-type: $mime");
header("Content-Disposition: attachment; filename=\"$filename\"");

ob_clean();
flush();
//readfile($file->getFilenameOnFilestore());
readfile($release->getPackagePath());
