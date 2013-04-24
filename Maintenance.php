<?php
/**
 * Run a maintenance script for a wiki within the wiki farm
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

if ( $argc < 3 || in_array( $argv[1], array( '--help', '-help', '-h' ) ) ) {
	$msg = "Run a maintenance script for a given wiki.\n" .
	"Usage: php Maintenance.php wikiDBName maintenanceScript Parameter1 Parameter2 ...";
	die( $msg . "\n" );
}

require_once( __DIR__ . '/includes/Main.php' );

foreach( $mwtWikis as $wiki ) {
	if ( $argv[1] === $wiki->getDBName() ) {
		$_SERVER['REQUEST_URI'] = $mwtWikiPath . '/' . $wiki->getPath() . '/foo';
	}
}
if ( !isset( $_SERVER['REQUEST_URI'] ) || !$_SERVER['REQUEST_URI'] ) {
	die( "Inalid wiki: $argv[1]\n" );
}

// Everything seems fine from our site...
// unset temporary vars, rewrite $argv and $argc and run the maintenance script!
unset( $wiki );

$argc = $argc - 2;

// Unset old script name and wiki name
unset( $argv[0] );
unset( $argv[1] );
$argv = array_values( $argv );

if ( is_readable( $argv[0] ) ) {
	// Probably absolute path
	include( $argv[0] );
} else if ( is_readable( $mwtDocRoot . '/maintenance/' . $argv[0] ) ) {
	// Look into MediaWikis maintenance dir
	include( $mwtDocRoot . '/maintenance/' . $argv[0] );
} else {
	die( "Couldn't find maintenance script: $argv[0]\n" );
}
