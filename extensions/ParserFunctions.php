<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class ParserFunctions extends extension {
	/**
	 * Constructor
	 *
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	public function __construct( $wikis = null ) {
		parent::__construct( 'ParserFunctions', $wikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'ParserFunctions.php';
	}
}
