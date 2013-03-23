<?php
namespace mwt\extensions;
use mwt\extension, mwt\Utilities;

class CentralAuth extends extension {
	/**
	 * Constructor
	 *
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	public function __construct( $wikis = null ) {
		parent::__construct( 'CentralAuth', $wikis );
	}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	public function getSettingsTemplate() {
		return 'CentralAuth.php';
	}

	/**
	 * Drop the centralauth database
	 */
	public function remove() {
		Utilities::dropDatabase( 'centralauth', true );
	}

	/**
	 * Get the SQL template to import
	 *
	 * @return array
	 */
	public function getSQLTemplates() {
		return array(
			array(
				'DB' => 'centralauth',
				'template' => 'CentralAuth.sql'
			)
		);
	}
}
