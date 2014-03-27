<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
	'EXT:' . $_EXTKEY . '/Scripts/Cli.php', '_CLI_lowlevel'
);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers'] = array();
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['ExtensionConfiguration'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\ExtensionConfigurationMigrationDriver',
	'shortName' => 'ExtensionConfiguration',
	'label' => 'ExtensionConfiguration'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['Database'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\DatabaseMigrationDriver',
	'shortName' => 'Database',
	'label' => 'Database'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['PageTsConfig'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\PageTsConfigMigrationDriver',
	'shortName' => 'PageTsConfig',
	'label' => 'PageTsConfig'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['TypoScriptConstants'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\SysTemplate\TypoScriptConstantsMigrationDriver',
	'shortName' => 'TypoScriptConstants',
	'label' => 'SysTemplate\TypoScriptConstants'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['TypoScriptSetup'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\SysTemplate\TypoScriptSetupMigrationDriver',
	'shortName' => 'TypoScriptSetup',
	'label' => 'SysTemplate\TypoScriptSetup'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['IncludeStatic'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\SysTemplate\IncludeStaticMigrationDriver',
	'shortName' => 'IncludeStatic',
	'label' => 'SysTemplate\IncludeStatic'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['Fal'] = array(
	'class' => 'Enet\Migrate\MigrationDriver\Driver\FalMigrationDriver',
	'shortName' => 'Fal',
	'label' => 'Fal'
);

?>