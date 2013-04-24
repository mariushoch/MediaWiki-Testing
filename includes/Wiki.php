<?php
/**
 * Represents a MediaWiki instance within the wiki farm
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

class instance {
	/**
	 * @var string
	 */
	protected $dbname;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * Constructor
	 *
	 * Options:
	 *	path:
	 *		Virtual path under which the wiki can be found.
	 * 		Defaults to database name
	 *
	 * @param $dbname string Database name
	 * @param $options array
	 */
	function __construct( $dbname, $options = array() ) {
		$this->dbname = $dbname;

		$this->path = isset( $options['path'] ) ? $options['path'] : $dbname;
	}

	/**
	 * Get the database name of the current wiki
	 *
	 * @return string
	 */
	public function getDBName() {
		return $this->dbname;
	}

	/**
	 * Get the path of the current wiki
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Does the wiki exist? (Does it have a DB)
	 *
	 * @return bool
	 */
	public function exists() {
		$db = Utilities::getDatabaseConnection();

		$showDB = $db->query( 'SHOW DATABASES LIKE ' .  $db->quote( $this->dbname ) );

		if ( $showDB && $showDB->rowCount() === 1 ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns a apache rewrite rule for this wiki
	 *
	 * @return string
	 */
	public function getRewriteRule() {
		global $mwtWikiPath;
		return 'RewriteRule ^' . $this->path . '/(.*) ' . $mwtWikiPath . '/$1';
	}

	/**
	 * Create the database for the wiki and fill it with the SQL file Template.sql
	 *
	 * @throws mwt\Exception
	 */
	public function createDatabase() {
		global $mwtSQLTemplate;
		Utilities::createAndImportDatabase( $this->dbname, $mwtSQLTemplate );
	}

	/**
	 * Run the given maintenance script with the given arguments for the current wiki.
	 * Returns the console output
	 *
	 * @param $name string Name of the maintenance script
	 * @param $args arrays Arguments passed to the maintenance script
	 * @return string
	 */
	public function runMaintenance( $name, $args = array() ) {
		$cmdLine = 'php ' . __DIR__ . '/../Maintenance.php ' . escapeshellarg( $this->dbname ) . ' ' . $name;
		if ( count( $args ) ) {
			foreach( $args as $arg ) {
				$cmdLine .= ' ' . escapeshellarg( $arg );
			}
		}
		return shell_exec( $cmdLine );
	}

	/**
	 * Run maintenance/update.php for the wiki. Returns the console output
	 *
	 * @return string
	 */
	public function update() {
		return $this->runMaintenance( 'update.php', array( '--quick' ) );
	}

	/**
	 * Drop the database for the wiki
	 *
	 * @throws mwt\Exception
	 *
	 * @param $ignoreErrors bool Don't throw exceptions in case of failure
	 */
	public function dropDatabase( $ignoreErrors = false ) {
		Utilities::dropDatabase( $this->dbname, $ignoreErrors );
	}
}
