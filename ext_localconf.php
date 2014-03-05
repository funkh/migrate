<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
	'EXT:' . $_EXTKEY . '/Scripts/Cli.php', '_CLI_lowlevel'
);

?>