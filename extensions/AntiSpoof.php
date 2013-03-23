<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class AntiSpoof extends extension {
	/**
	 * Constructor
	 *
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	public function __construct( $wikis = null ) {
		parent::__construct( 'AntiSpoof', $wikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'AntiSpoof.php';
	}

	/**
	 * Installs the extension
	 */
	public function install() {
		global $mwtDocRoot;
		parent::install();

		foreach( $this->enabledWikis as $extWikis ) {
			// Create the spoofuser table
			$extWikis->update();
			// Run extensions/AntiSpoof/maintenance/batchAntiSpoof.php
			$extWikis->runMaintenance( $mwtDocRoot . '/extensions/AntiSpoof/maintenance/batchAntiSpoof.php' );
		}
	}
}
