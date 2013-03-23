<?php
/**
 * Do NOT alter this file!
 * All variables can be overriden in Config.php
 *
 */

// Absolute path to the folder which holds
// all git repos for MediaWiki and extensions
$mwtGitPath = '/home/foo/wikimedia-git-repos';

// Absolute path to the folder containing the configuration and data templates
$mwtTemplatePath = __DIR__ . '/templates';

// Name of the SQL template containing the tables for a plain MW install
$mwtSQLTemplate = 'MediaWiki.sql';

// Name of the LocalSettings.php template for MW
$mwtLocalSettingsTemplate = 'LocalSettings.php';

// Host name of the database server
$mwtDBHost = 'localhost';

// Database user which has to have the permissions
// to DROP and CREATE tables and databases
$mwtDBUser = 'root';

// Password for the database user
$mwtDBPassword = '';

// Path to the folder in which the wikis should be stored in
$mwtDocRoot = '/var/www/html/wikis';

// Like $wgServer
$mwtServer = 'http://localhost';

// Path under which all wikis are accesible (relative to $mwtServer)
$mwtWikiPath = '/wikis';

// Wikis to be created
$mwtWikis = array(
	'meta' => new mwt\instance(
		'metawiki',
		array(
			'path' => 'metawiki'
		)
	),
	'de' => new mwt\instance(
		'dewiki',
		array(
			'path' => 'dewiki'
		)
	),
	'he' => new mwt\instance(
		'hewiki',
		array(
			'path' => 'hewiki'
		)
	),
);

// Known extensions
$mwtExtensions = array();
foreach( get_declared_classes() as $class ) {
	if ( strpos( $class, 'mwt\extensions\\' ) !== 0 ) {
		continue;
	}
	$extension = new $class();
	$mwtExtensions[ $extension->getName() ] = $extension;
}

if ( is_readable( __DIR__ . '/Config.php' ) ) {
	// Custom configuration
	include_once( __DIR__ . '/Config.php' );
}

// Remove extensions we don't have the git repo of
// Note: We can't do that before loading Config.php as we don't have the directory with the git repos before
foreach( $mwtExtensions as $name => $ext ) {
	if ( !$ext->exists() ) {
		unset( $mwtExtensions[ $name ] );
	}
}
