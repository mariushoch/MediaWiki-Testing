<?php
/**
 * Represents a MediaWiki extension within the wiki farm
 *
 * Copyright (c) 2013 Marius Hoch
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace mwt;

abstract class extension {
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $enabledWikis;

	/**
	 * Constructor
	 *
	 * @param $name string Extension name (has to match the name of the folder containing the git repo)
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	function __construct( $name, $wikis = null ) {
		global $mwtWikis;

		$this->name = $name;

		$this->enabledWikis = $wikis !== null ? $wikis : $mwtWikis;
	}

	/**
	 * Does the extension exist? (Do we have the git repo)
	 *
	 * @return bool
	 */
	public function exists() {
		global $mwtGitPath;

		if( is_dir( $mwtGitPath . '/' . $this->name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Return an aray with extension objects on which this extension is enabled
	 *
	 * @return array
	 */
	public function getEnabledWikis() {
		return $this->enabledWikis;
	}

	/**
	 * Installs the extension
	 *
	 * @throws mwt\Exception
	 */
	public function install() {
		$this->sync();
		$this->writeLocalSettings();

		$sqlTemplates = $this->getSQLTemplates();
		if ( count( $sqlTemplates ) ) {
			foreach( $sqlTemplates as $sqlTemplate ) {
				Utilities::createAndImportDatabase( $sqlTemplate['DB'], $sqlTemplate['template'], array( 'mightExist' ) );
			}
		}
	}

	/**
	 * Append the settings to the local settings file.
	 * Assumes any old settings have been removed!
	 *
	 * @throws mwt\Exception
	 */
	public function writeLocalSettings() {
		global $mwtDocRoot;

		if ( !file_put_contents( $mwtDocRoot . '/LocalSettings.php', $this->createLocalSettings(), FILE_APPEND ) ) {
			throw new Exception( "Couldn't write LocalSettings.php" );
		}
	}

	/**
	 * Get the settings template for this extension
	 *
	 * @throws mwt\Exception
	 *
	 * @return string
	 */
	public function getLocalSettings() {
		global $mwtTemplatePath;

		// Get template
		$localSettings = file_get_contents( $mwtTemplatePath . '/' . $this->getSettingsTemplate() );
		if ( $localSettings === false ) {
			throw new Exception( "Couldn't read template: " . $mwtTemplatePath . '/' . $this->getSettingsTemplate() );
		}

		$localSettings = '// Settings for Extension: ' . $this->name . ":\n" .
			// Get rid of leading <?php as that can cause trouble
			substr( $localSettings, strpos( $localSettings, "\n" ) + 1 );

		return $localSettings;
	}

	/**
	 * Sync the git repo of the current extension with the one served by the web server
	 * Uses rsync omits .git for performance reasons.
	 *
	 * @throws mwt\Exception
	 */
	public function sync() {
		global $mwtDocRoot, $mwtGitPath;

		$destinantion = $mwtDocRoot . '/extensions/';

		if( !is_dir( $destinantion ) ) {
			throw new Exception( "The extensions dir doesn't seem to exist" );
		}

		if( !$this->exists() ) {
			throw new Exception( "The git repo of $this->name couldn't be found" );
		}

		$repo = $mwtGitPath . '/' . $this->name;

		shell_exec(
			'rsync -a --delete ' . $repo . ' ' . $destinantion . ' --exclude=.git'
		);
	}

	/**
	 * Create if statement which conditionally includes the extension
	 *
	 * @return string
	 */
	public function createLocalSettings() {
		$php = 'if ( in_array( $wgDBname, array( ';

		foreach( $this->enabledWikis as $extWikis ) {
			$php .= '"' . str_replace( '"', '\"', $extWikis->getDBName() ) . '", ';
		}

		$php .= ") ) ) {\n";

		$php .= $this->getLocalSettings();

		$php .= "\n}\n";

		return $php;
	}

	/**
	 * Get the SQL template(s) to import, if any.
	 *
	 * This function returns either an empty array or an array of arrays
	 * with 'DB' => database name and 'template' => sql template file
	 *
	 * @return array
	 */
	public function getSQLTemplates() {
		return array();
	}

	/**
	 * Remove the extension
	 *
	 * @throws mwt\Exception
	 */
	public function remove() {}

	/**
	 * Get the name of the settings template file for this extension
	 *
	 * @return string
	 */
	abstract public function getSettingsTemplate();
}
