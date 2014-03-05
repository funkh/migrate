<?php

if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	$dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Enet\\Migrate\\Cli\\Dispatcher');
	$dispatcher->run();
} else {
	die('This script must be included by the "CLI module dispatcher"');
}

?>
