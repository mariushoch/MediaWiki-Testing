<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class Scribunto extends extension {
	/**
	 * Constructor
	 *
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	public function __construct( $wikis = null ) {
		parent::__construct( 'Scribunto', $wikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'Scribunto.php';
	}
}
