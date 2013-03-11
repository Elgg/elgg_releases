<?php
/**
 * A release of Elgg.
 *
 * Title is the release name.
 * Desc is the release notes.
 */

class ElggRelease extends ElggFile {
	public function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = 'elgg_release';
		return true;
	}

	public function save() {
		if (!$this->getVersion()) {
			return false;
		}

		if (!$this->setReleaseBranch(self::getReleaseBranchFromVersion($this->getVersion()))) {
			return false;
		}

		return parent::save();
	}

	public function setVersion($version) {
		// only allowed one version
		if (self::getReleaseFromVersion($version)) {
			return false;
		}

		return $this->version = $version;
	}

	public function getVersion() {
		return $this->version;
	}

	/**
	 * Build a download package from a tag
	 */
	public function package() {
		// have to know what version this is to build it.
		$version = $this->getVersion();
		if (!$version) {
			return false;
		}

		$scripts_dir = elgg_get_plugin_setting('elgg_scripts_path', 'elgg_org_theme');
		$output_dir = elgg_get_plugin_setting('build_output_dir', 'elgg_org_theme');
		$cmd = "{$scripts_dir}build/build.sh $version $version $output_dir";
		
		exec($cmd, $cmd_output, $cmd_return);
		
		if ($cmd_return === 0) {
			// save the path
			$this->setFilename("elgg-$version.zip");
			$this->package_path = $output_dir . "/elgg-$version.zip";
			$t = new ElggFile();
			return true;
		} else {
			elgg_log("ElggPackage build failed: $cmd_output");
			return false;
		}
	}

	public function setPackagePath($path) {
		return $this->package_path = $path;
	}

	public function getPackagePath() {
		return $this->package_path;
	}

	/**
	 * Sets the release branch (1.7, 1.8, 1.9)
	 *
	 * @param type $branch
	 */
	public function setReleaseBranch($branch) {
		return $this->release_branch = $branch;
	}

	public function getReleaseBranch() {
		return $this->release_branch;
	}

	/**
	 * Override for entity URL.
	 *
	 * @return false|string
	 */
	public function getURL() {
		if (!$this->guid) {
			return false;
		}

		$version = $this->getVersion();
		return "/releases/view/$version";
	}
	
	/**
	 * Return an ElggRelease object by its version.
	 *
	 * @param string $version A full version number
	 * @return false|ElggRelease
	 */
	public static function getReleaseFromVersion($version) {
		$options = array(
			'type' => 'object',
			'subtype' => 'elgg_release',
			'metadata_name' => 'version',
			'metadata_value' => $version
		);

		$releases = elgg_get_entities_from_metadata($options);
		if ($releases) {
			return $releases[0];
		}

		return false;
	}

	/**
	 * Returns the release branch (x.y) from a full version (x.y or x.y.z)
	 *
	 * @param string $version
	 * @return string
	 */
	public static function getReleaseBranchFromVersion($version) {
		preg_match("|^[0-9]+\.[0-9]+|", $version, $matches);
		if ($matches) {
			return $matches[0];
		}
		return false;
	}

	/**
	 * Return the most recently added release for a branch.
	 *
	 * @note This is _most recently added_ to the database. It doesn't check the version itself.
	 * If you release 1.8.17 and then release 1.8.16 you're a bad person.
	 *
	 * @param string $branch
	 */
	public static function getLatestReleaseFromBranch($branch) {
		$options = array(
			'type' => 'object',
			'subtype' => 'elgg_release',
			'metadata_name' => 'release_branch',
			'metadata_value' => $branch
		);

		$releases = elgg_get_entities_from_metadata($options);
		if ($releases) {
			return $releases[0];
		}
	}
}