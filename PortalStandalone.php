<?php
/**********************************************************************************
* PortalStandalone.php                                                            *
***********************************************************************************
* SimplePortal                                                                    *
* SMF Modification Project Founded by [SiNaN] (sinan@simplemachines.org)          *
* =============================================================================== *
* Software Version:           SimplePortal 2.3.4                                  *
* Software by:                SimplePortal Team (http://www.simpleportal.net)     *
* Copyright 2008-2009 by:     SimplePortal Team (http://www.simpleportal.net)     *
* Support, News, Updates at:  http://www.simpleportal.net                         *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

/*

	This file here, unbelievably, has your portal within.

	In order to use SimplePortal in standalone mode:
		+ Go to "SPortal Admin" >> "Configuration" >> "General Settings"
		+ Select "Standalone" mode as "Portal Mode"
		+ Set "Standalone URL" as the full url of this file.
		+ Edit path to the forum ($forum_dir) in this file.

	See? It's just magic!

*/

global $sp_standalone;

// Should be the full path!
$forum_dir = 'full/path/to/forum';

// Let them know the mode.
$sp_standalone = true;

// Hmm, wrong forum dir?
if (!file_exists($forum_dir . '/index.php'))
	die('Wrong $forum_dir value. Please make sure that the $forum_value variable points to your forum\'s directory.');

// Get out the forum's SMF version number.
$data = substr(file_get_contents($forum_dir . '/index.php'), 0, 4096);
if (preg_match('~\*\s*Software\s+Version:\s+(SMF\s+.+?)[\s]{2}~i', $data, $match))
	$forum_version = $match[1];
elseif (preg_match('~\*\s@version\s+(.+)[\s]{2}~i', $data, $match))
	$forum_version = 'SMF ' . $match[1];

// Call the SSI magic.
require_once($forum_dir . '/SSI.php');

// Wireless? We don't support you, yet.
if (WIRELESS)
	redirectexit();

// Get our main file.
require_once($sourcedir . '/PortalMain.php');

// Re-initialize SP.
sportal_init(true);

// Get the page ready.
sportal_main();

// Here we go!
obExit(true);

?>