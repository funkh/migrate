<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
	'EXT:' . $_EXTKEY . '/Scripts/Cli.php', '_CLI_lowlevel'
);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers'] = array();
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['ExtensionConfiguration'] = array(
	'class' => 'Enet\Migrate\Driver\ExtensionConfigurationMigrationDriver',
	'shortName' => 'ExtensionConfiguration',
	'label' => 'ExtensionConfiguration',
	'dataFileExtensions' => array('php'),
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['PageTsConfig'] = array(
	'class' => 'Enet\Migrate\Driver\PageTsConfigMigrationDriver',
	'shortName' => 'PageTsConfig',
	'label' => 'PageTsConfig',
	'dataFileExtensions' => array('txt', 'ts'),
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['SysTemplate'] = array(
	'class' => 'Enet\Migrate\Driver\SysTemplateMigrationDriver',
	'shortName' => 'SysTemplate',
	'label' => 'SysTemplate'
);
//$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['Fal'] = array(
//	'class' => 'Enet\Migrate\Driver\FalMigrationDriver',
//	'shortName' => 'Fal',
//	'label' => 'Fal'
//);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['T3d'] = array(
	'class' => 'Enet\Migrate\Driver\T3dMigrationDriver',
	'shortName' => 'T3d',
	'label' => 'T3d',
	'dataFileExtensions' => array('t3d')
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['Record'] = array(
	'class' => 'Enet\Migrate\Driver\RecordMigrationDriver',
	'shortName' => 'Record',
	'label' => 'Record'
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['Sql'] = array(
	'class' => 'Enet\Migrate\Driver\SqlMigrationDriver',
	'shortName' => 'Sql',
	'label' => 'Sql',
	'dataFileExtensions' => array('sql'),
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers']['Php'] = array(
	'class' => 'Enet\Migrate\Driver\PhpMigrationDriver',
	'shortName' => 'Php',
	'label' => 'Php',
	'dataFileExtensions' => array('php'),
);

?>
