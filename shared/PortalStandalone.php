<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause 
 *
 * @version 2.4
 */

global $sp_standalone;

$forum_dir = 'full/path/to/forum';
$sp_standalone = true;

if (!file_exists($forum_dir . '/index.php'))
	exit('Wrong $forum_dir value. Please make sure that the $forum_dir variable points to your forum\'s directory.');

$data = substr(file_get_contents($forum_dir . '/index.php'), 0, 4096);
if (preg_match('~\*\s@version\s+(.+)[\s]{2}~i', $data, $match))
	$forum_version = 'SMF ' . $match[1];

require_once($forum_dir . '/SSI.php');
require_once($sourcedir . '/PortalMain.php');

if (WIRELESS)
	redirectexit();

sportal_init(true);
sportal_main();

obExit(true);

?>