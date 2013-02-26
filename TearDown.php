<?php
/**
 * Destroys the wiki farm.
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
	$msg = 'Script which destroys the whole wiki farm. Add \'hard\' as argument to also delete the whole $mwtDocRoot' . "\n" .
	"Usage: php TearDown [hard]";
	die( $msg . "\n" );
}

$startTime = microtime( true );

require_once( __DIR__ . '/Utilities.php' );

// Drop all databases
echo "Dropping all databases... ";
Utilities::dropAllDatabases( true );
echo "done\n";

// Remove all extensions
echo "Removing all extensions... ";
Utilities::removeAllExtensions();
echo "done\n";

if ( isset( $argv[1] ) && in_array( $argv[1], array( '--hard', '-hard', 'hard' ) ) ) {
	echo "Running rm -rf on the whole doc root... ";
	shell_exec( 'rm -rf ' . $mwtDocRoot . '/*' );
	echo "done\n";
}

echo "\n\n" . round( microtime( true ) - $startTime, 2 ) . " seconds needed\n";
