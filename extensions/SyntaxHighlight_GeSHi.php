<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class SyntaxHighlight_GeSHi extends extension {
	/**
	 * Constructor
	 *
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	public function __construct( $wikis = null ) {
		parent::__construct( 'SyntaxHighlight_GeSHi', $wikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'SyntaxHighlight_GeSHi.php';
	}
}
