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
	 * @param $name string Extension name
	 * @param $wikis array Array with instance objects of wikis
	 * 		this extension should be enabled on. Defaults to all wikis.
	 */
	function __construct( $name, $wikis = null ) {
		global $mwtWikis;

		$this->name = $name;
		$this->enabledWikis = $wikis !== null ? $wikis : $mwtWikis;
	}

	/**
	 * Get the name of the extension
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Does the extension exist and is installable?
	 * (Do we have the git repo and the dependent ones)
	 *
	 * @return bool
	 */
	public function exists() {
		global $mwtGitPath, $mwtExtensions;

		$deps = $this->getDependencies();
		if ( count( $deps ) ) {
			foreach( $deps as $neededExt ) {
				if ( !$mwtExtensions[ $neededExt ] || !$mwtExtensions[ $neededExt ]->exists() ) {
					return false;
				}
			}
		}

		if( is_dir( $mwtGitPath . '/' . $this->getGitFolder() ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Return an array with wiki instance objects on which this extension is enabled
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
		global $mwtExtensions;

		$deps = $this->getDependencies();
		if ( count( $deps ) ) {
			foreach( $deps as $neededExt ) {
				if ( !$mwtExtensions[ $neededExt ] ) {
					throw new Exception( "Unknown dependency for $this->name: $neededExt" );
				}
				$mwtExtensions[ $neededExt ]->install();
			}
		}

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

		if ( !@file_put_contents( $mwtDocRoot . '/LocalSettings.php', $this->createLocalSettings(), FILE_APPEND ) ) {
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
	protected function getLocalSettings() {
		global $mwtTemplatePath, $mwtGitPath;
		$localSettings = '';

		if ( is_readable( $mwtGitPath . '/' . $this->getEntryPoint() ) ) {
			// Just try to include the default entry point if we can find one
			$localSettings = 'require_once( "$IP/extensions/' . $this->getEntryPoint() . '" );';
		}

		// Get template
		$localSettingsTemplate = @file_get_contents( $mwtTemplatePath . '/' . $this->getSettingsTemplate() );

		if ( $localSettingsTemplate === false && $localSettings === '' ) {
			throw new Exception( "Couldn't find default or custom entry point for " . $this->getName() );
		} elseif ( $localSettingsTemplate ) {
			if ( strpos( $localSettingsTemplate, '<?php' ) !== false ) {
				// Get rid of leading <?php as that can cause trouble
				$localSettingsTemplate = substr( $localSettingsTemplate, strpos( $localSettingsTemplate, "\n" ) + 1 );
			}

			$localSettings .= $localSettingsTemplate;
		}

		$localSettings = '// Settings for Extension: ' . $this->getName() . ":\n" . $localSettings;

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

		$repo = $mwtGitPath . '/' . $this->getGitFolder();

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

		return Utilities::settingTemplateVarSubstitution( $php );
	}

	/**
	 * Get the folder name (containing the git repo)
	 *
	 * @return string
	 */
	public function getGitFolder() {
		return $this->getName();
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
	 * Get the names of extensions this extension relies on
	 *
	 * @return array
	 */
	public function getDependencies() {
		return array();
	}

	/**
	 * Get the path of the entry point for the current extension relative to $mwtGitPath
	 * 
	 * @return string
	 */
	public function getEntryPoint() {
		return $this->getGitFolder() . '/' . $this->getName() . '.php';
	}

	/**
	 * Remove the extension (called from TearDown.php)
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
