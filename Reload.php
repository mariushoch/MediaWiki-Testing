<?php
/**
 * Recreates a specific part of the wiki farm.
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

$startTime = microtime( true );

if ( $argc < 2 || in_array( $argv[1], array( '--help', '-help', '-h' ) ) ) {
	$msg = "Recreates a specific part of the wiki farm.\n" .
	"Usage: php Reload.php part\n" .
	"Available actions:\n" .
	"	LocalSettings: Rewrite the LocalSettings.php";
	die( $msg . "\n" );
}

require_once( __DIR__ . '/includes/Main.php' );

if ( $argv[1] === 'LocalSettings' ) {

	echo "Rewriting initial LocalSettings.php... ";
	if ( !file_put_contents( $mwtDocRoot . '/LocalSettings.php', Utilities::createLocalSettings() ) ) {
		throw new Exception( "Couldn't write LocalSettings.php" );
	}
	echo "done\n";

	// Extension part
	echo "Rewriting extension settings... ";
	foreach( $mwtExtensions as $ext ) {
		$ext->writeLocalSettings();
	}
	echo "done\n";

} else {
	die( "Can't reload $argv[1] (unknown action)." );
}

echo "\n\n" . round( microtime( true ) - $startTime, 2 ) . " seconds needed\n";
