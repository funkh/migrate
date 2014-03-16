<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Enet.' . $_EXTKEY,
		'tools',	 // Make module a submodule of 'tools'
		'migrationlist',	// Submodule key
		'',						// Position
		array(
			'Migration' => 'list',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_migrationlist.xlf',
		)
	);

}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Database migrations');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_migrate_domain_model_migration', 'EXT:migrate/Resources/Private/Language/locallang_csh_tx_migrate_domain_model_migration.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_migrate_domain_model_migration');

/** @var \Enet\Migrate\MigrationDriver\MigrationDriverRegistry $registry */
$registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Enet\Migrate\MigrationDriver\MigrationDriverRegistry');
$registry->addDriversToTCA();

?>