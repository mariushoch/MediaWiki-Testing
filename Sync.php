<?php
/**
 * Script to synch the Wikis served by the web server with the git repos.
 * Makes heavy use of rsync
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

if ( $argc > 1 && in_array( $argv[1], array( '--help', '-help', '-h' ) ) ) {
	$msg = "Script to synch the Wikis served by the web server with the git repos.";
	die( $msg . "\n" );
}

require_once( __DIR__ . '/includes/Main.php' );

echo "Syncing all git repos... ";
Utilities::syncAllGitRepos();
echo "done\n";

echo "\n\n" . round( microtime( true ) - $startTime, 2 ) . " seconds needed\n";
