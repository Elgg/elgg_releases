<?php
/**
 * Elgg.org release management
 *
 * Downloads
 *	Receiver for git callbacks to start the release process
 *		Check annotations on tag for release info
 *		Zip the tag
 *		Add entity
 *		Check commit message for release notes.
 *	Admin page for downloads.
 *		Git information
 *			Callback URL
 *			Annotation format
 *
 * Remove activity page handler
 */

elgg_register_event_handler('init', 'system', 'elgg_releases_init');

function elgg_releases_init() {
	$actions_dir = dirname(__FILE__) . '/actions/releases';

	elgg_register_action('releases/delete', "$actions_dir/delete.php", 'admin');
	elgg_register_action('releases/save', "$actions_dir/save.php", 'admin');

	// release page handler
	elgg_register_page_handler('releases', 'elgg_releases_page_handler');

	// github web hook
	elgg_register_page_handler('github', 'elgg_releases_github_webhook');

	// menu modifications
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'elgg_releases_remove_access');
}

/**
 * Remove the access level from the entity menu if not logged in.
 */
function elgg_releases_remove_access($hook, $type, $value, $params) {
	if (elgg_is_logged_in()) {
		return $value;
	}
	
	foreach ($value as $i => $item) {
		if ($item->getName() == 'access') {
			unset($value[$i]);
		}
	}

	return $value;
}

/**
 * Receives a POST from GitHub with commit data on every commit.
 * Used to generate releases when tagged.
 *
 * @param type $page
 */
function elgg_releases_github_webhook($page) {
	// @todo add check for IP address!!!!
	// 207.97.227.253, 50.57.128.197, 108.171.174.178.

	$payload = elgg_extract('payload', $_POST);
	$payload = json_decode($payload);

	if (!$payload) {
		return true;
	}

	$regex = "|refs/tags/([0-9\.]+)|i";
	preg_match($regex, $payload->ref, $matches);
	$version = elgg_extract(1, $matches);

	if (!$version) {
		return true;
	}

	// GH doesn't expose the commit messages for annotated tags, so we have to drop down
	// to their low level API using the tag's SHA

	$repo_name = $payload->repository->name;
	$repo_owner = $payload->repository->owner->name;

	$sha = $payload->after;
	$owner = elgg_get_plugin_setting('github_repo_owner', 'elgg_org_theme');
	$repo = elgg_get_plugin_setting('github_repo_owner', 'elgg_org_theme');
	$url = "https://api.github.com/repos/$repo_owner/$repo_name/git/tags/$sha";
	$json = file_get_contents($url);
	$response = json_decode($json);

	if (!$response) {
		return true;
	}

	$message = $response->message;
	$tagger = $response->tagger;

	if (!$message || !$tagger) {
		return true;
	}

	// create release
	$ia = elgg_set_ignore_access(true);

	$release = new ElggRelease();
	$release->access_id = ACCESS_PUBLIC;
	$release->setVersion($version);

	if (!$release->package()) {
		elgg_add_admin_notice("build_{$sha}_failed", "Build for version $version ($sha) failed during packaging. Check logs.");
		return true;
	}

	$release->title = "Elgg $version";
	$release->description = $message;
	
	if ($release->save()) {
		$link = elgg_view('output/url', array(
			'text' => "Elgg $version",
			'href' => $release->getURL()
		));
		
		elgg_add_admin_notice("build_{$sha}", "Build for $link ($sha) completed on " . date('Y-m-d H:i:s') . '.');
	} else {
		elgg_add_admin_notice("save_{$sha}_failed", "Object save for version $version ($sha) failed.");
		return true;
	}

	exit;
}

/**
 * Serves pages for URLs like:
 *
 * /releases/all             List all downloads available
 * /releases/<miror_version> List all downloads for a minor version (1.7, 1.8)
 * /releases/view/<version>  View details about release.
 *
 * @param type $pages
 */
function elgg_releases_page_handler($pages) {
	$pages_dir = dirname(__FILE__) . '/pages/releases';
	$page = elgg_extract(0, $pages, 'all');

	elgg_push_breadcrumb("Elgg Releases", '/releases/');

	switch ($page) {
		case 'view':
			$version = elgg_extract(1, $pages);
			set_input('version', $version);
			include "$pages_dir/view.php";
			break;

		case 'list':
			$version = elgg_extract(1, $pages);
			set_input('version', $version);
			include "$pages_dir/list.php";
			break;

		case 'edit':
			$version = elgg_extract(1, $pages);
			$release = ElggRelease::getReleaseFromVersion($version);

			if (!$release) {
				// check if this is a guid
				$release = get_entity($version);
			}

			if (!elgg_instanceof($release, 'object', 'elgg_release') || !$release->canEdit()) {
				register_error("Unknown release");
				forward(REFERRER, 404);
			}
			
			set_input('version', $version);

		case 'add':
			include "$pages_dir/edit.php";
			break;

		case 'download':
			$version = elgg_extract(1, $pages);
			$release = ElggRelease::getReleaseFromVersion($version);

			if (!elgg_instanceof($release, 'object', 'elgg_release') || !$release->canEdit()) {
				register_error("Unknown release");
				forward(REFERRER, 404);
			}

			include "$pages_dir/download.php";
			break;

		case 'all':
		default:
			include "$pages_dir/all.php";
			break;
	}

	return true;
}

/**
 * Build default vars for the save release form.
 *
 * @param type $entity
 * @return type
 */
function elgg_releases_prepare_form_vars($entity) {
	$values = array(
		'title' => '',
		'description' => '',
		'version' => '',
		'build_package' => false,
		'package_path' => false,
		'access_id' => ACCESS_PUBLIC,
		'guid' => null,
		'entity' => $entity,
	);

	if ($entity) {
		foreach (array_keys($values) as $field) {
			if (isset($entity->$field)) {
				$values[$field] = $entity->$field;
			}
		}
	}

	if (elgg_is_sticky_form('elgg_release')) {
		$sticky_values = elgg_get_sticky_values('elgg_release');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('elgg_release');

	return $values;
}