<?php
/**
 * Utilities and classes for managing MediaWiki (testing) farms.
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

class Exception extends \Exception {}

class Utilities {
	/**
	 * Syncs all git repos into their respective targets
	 */
	public static function syncAllGitRepos() {
		global $mwtExtensions, $mwtDocRoot;
		// Core
		self::syncCoreGit();
		// Extensions
		foreach( $mwtExtensions as $extension ) {
			$extension->sync();
		}
	}

	/**
	 * Sync the program files for the MediaWiki core.
	 * Uses rsync, omits hidden directories, like .git for performance reasons.
	 * Furthermore this omits the /images directory
	 *
	 * @throws mwt\Exception
	 */
	public static function syncCoreGit() {
		global $mwtGitPath, $mwtDocRoot;

		if( !is_dir( $mwtDocRoot ) ) {
			if( !mkdir( $mwtDocRoot, 0755, true) ) {
				throw new Exception( "Can't create dir $mwtDocRoot" );
			}
		}

		$repo = $mwtGitPath . '/core';
		if( !is_dir( $repo ) ) {
			throw new Exception( "The MediaWiki git repo couldn't be found at $repo" );
		}

		shell_exec(
			// rltgoD is --archive without -p (preserve permissions)
			'rsync -rltgoD --delete ' . $repo . '/* ' . $mwtDocRoot . ' --exclude=images/*'
		);
	}

	/**
	 * Create and import the databases for all wikis
	 */
	public static function createAllDatabases() {
		global $mwtWikis;

		foreach( $mwtWikis as $wiki ) {
			// Import the database
			$wiki->createDatabase();
		}
	}

	/**
	 * Update all wikis
	 */
	public static function updateAll() {
		global $mwtWikis;

		foreach( $mwtWikis as $wiki ) {
			$wiki->update();
		}
	}

	/**
	 * Drop the databases for all wikis
	 *
	 * @param $ignoreErrors bool Don't throw exceptions in case of failure
	 */
	public static function dropAllDatabases( $ignoreErrors = false ) {
		global $mwtWikis;

		foreach( $mwtWikis as $wiki ) {
			// Drop the database
			$wiki->dropDatabase( $ignoreErrors );
		}
	}

	/**
	 * Get the switch statement which let's the wikis now which one they are.
	 *
	 * @return string
	 */
	protected static function getDatabaseSwitch() {
		global $mwtWikis, $mwtWikiPath;

		$php = '// Code to find out which (virtual) wiki was requested. Dynamically generated
		$virtualFolder = split(
			"/",
			str_replace( "' . str_replace( '"', '\"', $mwtWikiPath ) . '/", "", $_SERVER["REQUEST_URI"] )
		);
		$virtualFolder = $virtualFolder[0];
		switch( $virtualFolder ) {
		';

		foreach( $mwtWikis as $wiki ) {
			$php .= 'case "' . str_replace( '"', '\"', $wiki->getPath() ) . '":' . "\n";
			$php .= '$wgDBname = "' . str_replace( '"', '\"', $wiki->getDBName() ) . '";' . "\n";
			$php .= 'break;' . "\n";
		}

		$php .= 'default:
			die( "Invalid wiki" );
			break;
		}
		unset( $virtualFolder );' . "\n\n";

		// It would be great if we could syntax check this... but there's no way
		return $php;
	}

	/**
	 * Connect to the database server using PDO
	 *
	 * @throws mwt\Exception
	 *
	 * @param $db string (Optional) Database to use
	 * @return PDO
	 */
	public static function getDatabaseConnection( $db = false ) {
		global $mwtDBHost, $mwtDBUser, $mwtDBPassword;

		$tmp = 'mysql:host=' . $mwtDBHost . ';';
		if( $db ) {
			$tmp .= ';dbname=' . $db . ';';
		}

		static $conn = array();
		if ( isset( $conn[ $tmp ] ) ) {
			return $conn[ $tmp ];
		}

		try {
			$conn[ $tmp ] = new \PDO( $tmp, $mwtDBUser, $mwtDBPassword );
		} catch( PDOException $e ) {
			throw new Exception( "Couldn't connect to database server: $mwtDBHost" );
		}
		return $conn[ $tmp ];
	}

	/**
	 * Installs all known extensions
	 */
	public static function installAllExtensions() {
		global $mwtExtensions;
		foreach( $mwtExtensions as $ext ) {
			$ext->install();
		}
	}

	/**
	 * Remove all known extensions
	 */
	public static function removeAllExtensions() {
		global $mwtExtensions;

		foreach( $mwtExtensions as $ext ) {
			$ext->remove();
		}
	}

	/**
	 * Returns a shell command which connects to the mysql server
	 *
	 * @return string
	 */
	protected static function getShellMysqlLine() {
		global $mwtDBHost, $mwtDBUser, $mwtDBPassword;

		$line = 'mysql -u ' . escapeshellarg( $mwtDBUser );
		if ( $mwtDBHost ) {
			$line .= ' -h ' . escapeshellarg( $mwtDBHost );
		}
		if ( $mwtDBPassword ) {
			$line .= ' -p' . escapeshellarg(  $mwtDBPassword );
		}
		return $line;
	}

	/**
	 * Create the database and fill it with the SQL file $template
	 * Options:
	 *	mightExist:
	 * 		Don't throw an exception if the database already exists
	 *
	 * @throws mwt\Exception
	 *
	 * @param $database string Name of the database to be created and import
	 * @param $template string File name of the template file to be imported into the new db
	 * @param $options array
	 */
	public static function createAndImportDatabase( $database, $template, $options = array() ) {
		global $mwtTemplatePath;

		if ( !is_readable( $mwtTemplatePath . '/' . $template ) ) {
			throw new Exception( "Can't read SQL template $template" );
		}

		$dbw = self::getDatabaseConnection();

		$createDB = $dbw->query( 'CREATE DATABASE ' . $database );

		if ( !in_array( 'mightExist', $options ) && ( !$createDB || $createDB->rowCount() === 0 ) ) {
			// Something went wrong
			throw new Exception( "Creating database $database failed" );
		}
		$shellOut = shell_exec(
			self::getShellMysqlLine() . ' ' . escapeshellarg( $database ) . ' < ' . $mwtTemplatePath . '/' . $template . ' 2>&1'
		);
		if ( strpos( 'failed', $shellOut ) !== false ) {
			throw new Exception( "Importing database $database failed" );
		}
	}

	/**
	 * Drop the given database
	 *
	 * @throws mwt\Exception
	 *
	 * @param $database string Name of the database to be dropped
	 * @param $ignoreErrors bool Don't throw exceptions in case of failure
	 */
	public static function dropDatabase( $database, $ignoreErrors = false ) {
		$dbw = self::getDatabaseConnection();

		$dropDB = $dbw->query( 'DROP DATABASE ' . $database );

		if ( !$ignoreErrors && ( !$dropDB || $dropDB->rowCount() === 0 ) ) {
			// Something went wrong
			throw new Exception( "Dropping database $database failed" );
		}
	}

	/**
	 * Create a LocalSettings.php for the whole wiki farm and return it.
	 * Doesn't contain any extensions yet
	 *
 	 * @throws mwt\Exception
	 *
	 * @return string
	 */
	public static function createLocalSettings() {
		global $mwtTemplatePath, $mwtLocalSettingsTemplate, $mwtServer;

		// Get template
		$localSettings = file_get_contents( $mwtTemplatePath . '/' . $mwtLocalSettingsTemplate );
		if ( $localSettings === false ) {
			throw new Exception( "Couldn't read LocalSettings.php template: " . $mwtTemplatePath . '/' . $mwtLocalSettingsTemplate );
		}

		// Append database name switch... get rid of leading <?php as that can cause trouble
		$localSettings = substr( $localSettings, strpos( $localSettings, "\n" ) + 1 );
		$localSettings = "<?php\n" . self::getDatabaseSwitch() . $localSettings;

		return $localSettings;
	}

	/**
	 * Create a .htaccess file which rewrites all virtual wikis to a single MediaWiki install
	 *
	 * @return string
	 */
	public static function createHtaccess() {
		global $mwtWikis;

		// (Re)create .htaccess for rewrite rules
		$htaccess = 'RewriteEngine on';
		// Loop through all wikis
		foreach( $mwtWikis as $wiki ) {
			// Append rewrite rule
			$htaccess .= "\n" . $wiki->getRewriteRule();
		}
		return $htaccess;
	}
}

// Include all classes
foreach( glob( __DIR__ . '/includes/*.php' ) as $extensionClass ) {
	include_once( $extensionClass );
}

// Include all extension classes
foreach( glob( __DIR__ . '/extensions/*.php' ) as $extensionClass ) {
	include_once( $extensionClass );
}

// Configuration
require_once( __DIR__ . '/DefaultConfig.php' );
