<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration',
		'label' => 'driver',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',

		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'type' => 'type',
		#'readOnly' => 1,// Prevents the table from being altered
		'adminOnly' => 1, // Only admin, if any
		'rootLevel' => 1,
		'searchFields' => 'type,version,extension_key,script_path,query,applied,',
		'iconfile' => 'EXT:migrate/Resources/Public/Icons/tx_migrate_domain_model_migration.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, driver, version, extension_key, extension_version, script_path, identifier, configuration, raw_data, applied',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, driver, version, extension_key, extension_version, script_path, identifier, configuration, raw_data, applied,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_migrate_domain_model_migration',
				'foreign_table_where' => 'AND tx_migrate_domain_model_migration.pid=###CURRENT_PID### AND tx_migrate_domain_model_migration.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'driver' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.driver',
			'config' => array(
				'type' => 'select',
				'items' => array(
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => ''
			),
		),
		'version' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.version',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'trim'
			),
		),
		'extension_key' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.extension_key',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'extension_version' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.extension_version',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'script_path' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.script_path',
			'config' => array(
				'type' => 'input',
				'size' => 100,
				'eval' => 'trim'
			),
		),
		'identifier' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.identifier',
			'config' => array(
				'type' => 'input',
				'size' => 100,
				'eval' => 'trim'
			),
		),
		'configuration' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.configuration',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			),
		),
		'raw_data' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.raw_data',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			),
		),
		'applied' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.applied',
			'config' => array(
				'type' => 'check',
				'default' => 0
			),
		),
	),
);

?>
