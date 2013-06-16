<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class WikibaseRepo extends extension {
	/**
	 * Constructor
	 */
	public function __construct() {
		global $mwtWikis;

		parent::__construct( 'WikibaseRepo', array( $mwtWikis['repo'] ) );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'WikibaseRepo.php';
	}

	/**
	 * Get the folder name (containing the git repo)
	 *
	 * @return array
	 */
	public function getGitFolder() {
		return 'Wikibase';
	}

	/**
	 * Get the names of extensions this extension relies on
	 *
	 * @return array
	 */
	public function getDependencies() {
		return array(
			'Ask',
			'Diff',
			'DataValues',
			'UniversalLanguageSelector',
			'WikibaseLib'
		);
	}

	/**
	 * Get the path of the entry point for the current extension relative to $mwtGitPath
	 *
	 * @return string
	 */
	public function getEntryPoint() {
		return $this->getGitFolder() . '/repo/Wikibase.php';
	}

	/**
	 * Installs the extension
	 */
	public function install() {
		global $mwtDocRoot;
		parent::install();

		foreach( $this->enabledWikis as $extWikis ) {
			// Wikibase Repo enables the ContentHandler... run update.php to make the wiki aware of that
			$extWikis->update();
			// Import sample items
			$extWikis->runMaintenance(
				$mwtDocRoot . '/extensions/Wikibase/repo/maintenance/importInterlang.php',
				array(
					'--ignore-errors',
					'simple',
					$mwtDocRoot . '/extensions/Wikibase/repo/maintenance/simple-elements.csv'
				)
			);
			// Import sample properties
			$extWikis->runMaintenance(
				$mwtDocRoot . '/extensions/Wikibase/repo/maintenance/importProperties.php',
				array(
					'en',
					$mwtDocRoot . '/extensions/Wikibase/repo/maintenance/en-elements-properties.csv'
				)
			);
		}
	}
}
