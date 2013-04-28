<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class WikibaseClient extends extension {
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
			if ( $name === 'repo' || $name === 'meta' ) {
				// Don't run us on the repo or on meta!
				continue;
			}
			$runWikis[ $name ] = $wiki;			
		}

		parent::__construct( 'WikibaseClient', $runWikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'WikibaseClient.php';
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
			'Diff',
			'DataValues',
			'UniversalLanguageSelector',
			'WikibaseLib'
		);
	}

	/**
	 * Installs the extension
	 */
	public function install() {
		global $mwtDocRoot;
		parent::install();

		foreach( $this->enabledWikis as $extWikis ) {
			// Run extensions/Wikibase/client/maintenance/populateInterwiki.php
			$extWikis->runMaintenance( $mwtDocRoot . '/extensions/Wikibase/client/maintenance/populateInterwiki.php' );
		}
	}
}
