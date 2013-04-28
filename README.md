MediaWiki-Testing
=================

MediaWiki testing environment with support for the CentralAuth, AbuseFilter and Wikibase extensions.
Suitable for advanced developers only!

This can build a variable number of MediaWiki wikis on one host with various extensions.
The databases and the settings will be dynamically compiled from templates.

My main motivations to write this were to have a portable development environment so
that I can use all of my machines for developing and to be able to recreate wikis in
the blink of an eye in case the databases are messed.

Set up
--------------
As told above this is only aimed at advanced developers who know what they're
doing. Due to this and my limited time the below manual is very rough!

After you cloned this repo, the MediaWiki core and all extensions you want
to use you have to do the following:

* Download the settings and SQL templates from http://toolserver.org/~hoo/stuff/MediaWiki-Testing-Templates.zip
You can either place the template folder into the root directory of MediaWiki-Testing or set $mwtTemplatePath to it.

* Take a look at DefaultConfig.php and set all variables you want/ need to alter in your Config.php
You'll almost certainly need to alter $mwtGitPath and probably many of the database and web server settings.

* Have a look at the templates. Maybe you need to alter the LocalSettings.php one

* Run Create.php to create your new wikis


Included scripts
--------------
Use --help to get details about the script.

* Create.php
Creates the wiki farm.
* TearDown.php
Destroys the wiki farm (drops all databases). Add 'hard' as argument to also delete the whole $mwtDocRoot
* Reload.php
Recreates a specific part of the wiki farm.
* Sync.php
Script to synch the Wikis served by the web server with the git repos.
* Maintenance.php
Run a maintenance script for a wiki within the wiki farm.
