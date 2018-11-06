<?php

/*
	<id>chenzhen:shoutAudio</id>
	<name>SP-Shoutbox Audio</name>
	<version>2.1</version>
	<type>modification</type>
*/

// This file is here solely to protect your Sources directory.

// Look for Settings.php....
if (file_exists(dirname(dirname(__FILE__)) . '/Settings.php'))
{
	// Found it!
	require(dirname(dirname(__FILE__)) . '/Settings.php');
	header('Location: ' . $boardurl);
}
// Can't find it... just forget it.
else
	exit;

?>