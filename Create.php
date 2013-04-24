<?php
/**
 * Creates the wiki farm.
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

if ( $argc > 1 && in_array( $argv[1], array( '--help', '-help', '-h' ) ) ) {
	$msg = "Creates the wiki farm.";
	die( $msg . "\n" );
}

$startTime = microtime( true );

require_once( __DIR__ . '/includes/Main.php' );

// Loop through all wikis to see whether one exists
foreach( $mwtWikis as $wiki ) {
	if ( $wiki->exists() ) {
		die( "Please run TearDown.php to destroy existing wikis before trying to create them\n" );
	}
}

// Bring MediaWiki and all extensions in place
echo "Syncing MediaWiki and all known extensions... ";
Utilities::syncAllGitRepos();
echo "done\n";

// Import the database
echo "Importing the databases... ";
Utilities::createAllDatabases();
echo "done\n";

echo "Writing rewrite rules to the .htaccess file... ";
// (Re)create .htaccess for rewrite rules
if ( !file_put_contents( $mwtDocRoot . '/.htaccess', Utilities::createHtaccess() ) ) {
	throw new Exception( "Couldn't write .htaccess file" );
}
echo "done\n";

echo "Writing initial LocalSettings.php... ";
// Write the localsettings
if ( !file_put_contents( $mwtDocRoot . '/LocalSettings.php', Utilities::createLocalSettings() ) ) {
	throw new Exception( "Couldn't write LocalSettings.php" );
}
echo "done\n";

// Install all extensions
echo "Installing extensions... ";
Utilities::installAllExtensions();
echo "done\n";

// Run update.php
echo "Running update.php on all wikis... ";
Utilities::updateAll();
echo "done\n";

echo "\n\n" . round( microtime( true ) - $startTime, 2 ) . " seconds needed\n";
