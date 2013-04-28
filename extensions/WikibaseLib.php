<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class WikibaseLib extends extension {
	/**
	 * Constructor
	 *
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	public function __construct( $wikis = null ) {
		parent::__construct( 'WikibaseLib', $wikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'WikibaseLib.php';
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
	 * Installs the extension
	 */
	public function install() {
		global $mwtDocRoot;
		parent::install();

		foreach( $this->enabledWikis as $extWikis ) {
			// Run extensions/Wikibase/lib/maintenance/populateSitesTable.php
			$extWikis->runMaintenance( $mwtDocRoot . '/extensions/Wikibase/lib/maintenance/populateSitesTable.php' );
		}
	}
}
