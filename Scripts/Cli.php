<?php

if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
	/** @var \Enet\Migrate\Cli\Dispatcher $dispatcher */
	$dispatcher = $objectManager->get('Enet\Migrate\Cli\Dispatcher');
	$dispatcher->run();
} else {
	die('This script must be included by the "CLI module dispatcher"');
}
