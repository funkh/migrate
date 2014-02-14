<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_migrate_domain_model_migration'] = array(
	'ctrl' => $TCA['tx_migrate_domain_model_migration']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, type, version, extension_key, script_path, query, applied',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, type, version, extension_key, script_path, query, applied,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'),
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
		'type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type.undefined', \Enet\Migrate\Domain\Model\Migration::TYPE_UNDEFINED),
					array('LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type.extconf', \Enet\Migrate\Domain\Model\Migration::TYPE_EXTCONF),
					array('LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type.database', \Enet\Migrate\Domain\Model\Migration::TYPE_DATABASE),
					array('LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type.typoscript', \Enet\Migrate\Domain\Model\Migration::TYPE_TYPOSCRIPT),
					array('LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type.typoscript.page', \Enet\Migrate\Domain\Model\Migration::TYPE_TYPOSCRIPT_PAGE),
					array('LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.type.typoscript.template', \Enet\Migrate\Domain\Model\Migration::TYPE_TYPOSCRIPT_TEMPLATE),
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
				'size' => 5,
				'max' => 5,
				'eval' => 'trim,num'
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
		'script_path' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.script_path',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'query' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:migrate/Resources/Private/Language/locallang_db.xlf:tx_migrate_domain_model_migration.query',
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
