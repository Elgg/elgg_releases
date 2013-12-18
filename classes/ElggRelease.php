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

		$this->setDownloadCount(0);

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
			throw new UnexpectedValueException("Invalid Elgg version '$version'.");
		}

		$scripts_dir = elgg_get_plugin_setting('elgg_scripts_path', 'elgg_releases');
		$output_dir = elgg_get_plugin_setting('build_output_dir', 'elgg_releases');
		$cmd = "{$scripts_dir}build/build.sh $version $version $output_dir";

		$cmd_output = $cmd_return = null;

		exec($cmd, $cmd_output, $cmd_return);
		
		if ($cmd_return === 0) {
			// save the path
			$this->setFilename("elgg-$version.zip");
			$this->package_path = $output_dir . "elgg-$version.zip";
			return true;
		} else {
			$output = implode("\n", $cmd_output);
			throw new UnexpectedValueException($output);
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
	 * Get the download URL for this release
	 *
	 * @return string
	 */
	public function getDownloadURL() {
		if (!$this->guid) {
			return false;
		}

		return "/releases/download/{$this->getVersion()}";
	}

	/**
	 * Returns the download count
	 *
	 * @note This uses private settings so we can easily use atomic queries to
	 * increment / set the count. You'll run into problems if you try to use
	 * MD to do something like this.
	 *
	 * @return int
	 */
	public function countDownloads() {
		return $this->getPrivateSetting('download_count');
	}

	/**
	 * Sets the download count
	 *
	 * @return bool
	 */
	public function setDownloadCount($count) {
		$count = sanitize_int($count);
		return $this->setPrivateSetting('download_count', $count);
	}

	/**
	 * Increments the download count
	 *
	 * @return bool
	 */
	public function incrementDownloadCount() {
		// this needs to be an atomic op
		$prefix = elgg_get_config('dbprefix');
		
		$q = "UPDATE {$prefix}private_settings "
			. "SET value = value + 1 "
			. "WHERE entity_guid = $this->guid AND name = 'download_count'";

		return update_data($q);
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
			return array_pop($releases);
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