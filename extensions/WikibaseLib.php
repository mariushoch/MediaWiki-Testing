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
		global $mwtWikis;
		$wikis = $wikis !== null ? $wikis : $mwtWikis;

		$runWikis = array();
		foreach ( $wikis as $name => $wiki ) {
			if ( $name === 'meta' ) {
				// Don't run us on meta!
				continue;
			}
			$runWikis[ $name ] = $wiki;			
		}

		parent::__construct( 'WikibaseLib', $runWikis );
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
	 * Get the path of the entry point for the current extension relative to $mwtGitPath
	 *
	 * @return string
	 */
	public function getEntryPoint() {
		return $this->getGitFolder() . '/lib/WikibaseLib.php';
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
