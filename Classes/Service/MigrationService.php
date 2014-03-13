<?php
namespace Enet\Migrate\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helge Funk <helge.funk@e-net.info>, e-net Consulting GmbH & Co. KG
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Enet\Migrate\Domain\Model\Migration;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo: rename to PackageMigrationService or so...
 * @todo: refactor this whole class
 */
class MigrationService {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\Flow\Package\PackageInterface
	 */
	protected $package;

	/**
	 * @var \Symfony\Component\Console\Output\ConsoleOutput
	 */
	protected $output;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 */
	public function __construct(\TYPO3\Flow\Package\PackageInterface $package) {
		\Enet\Migrate\Utility\ComposerUtility::initializeAutoloading();
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->configurationManager = $this->objectManager->get('TYPO3\CMS\Core\Configuration\ConfigurationManager');
		$this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$this->package = $package;

		try {
			$yamlConfigurationFile = $this->package->getPackagePath() . Migration::BASE_PATH . '/Migrations.yaml';
			if (is_file($yamlConfigurationFile)) {
				$this->configuration = \Symfony\Component\Yaml\Yaml::parse($yamlConfigurationFile);
				$this->sortDatabaseMigrationsByPriority();
			}
		} catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
			$this->output->write('<error>Could not parse migrations yaml file: ' . $e->getParsedFile() . '</error>', TRUE);
		}
	}

	/**
	 * @return bool
	 */
	public function migratePackage() {
		$migrationErrors = 0;

		$this->output->write('Migrating ' . $this->package->getPackageKey() . '... ');
		$this->migrateExtensionConfiguration();
		$this->migrateDatabase();
		$this->migratePageTsConfig();
		$this->migrateTemplateConstantsTs();
		$this->migrateTemplateSetupTs();
		$this->migrateTemplateIncludeStatic();

		if ($migrationErrors > 0) {
			$this->output->write('<error>Failed</error>', TRUE);
		} else {
			$this->output->write('<info>OK</info>', TRUE);
		}
		return TRUE;
	}

	/**
	 *
	 */
	protected function migratePageTsConfig() {
		// @todo make migrate function more generic
		$pageTConfigMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT_PAGE_TSCONFIG);
		if (is_dir($pageTConfigMigrationsPath) && isset($this->configuration['TypoScript']['PageTsConfig'])) {
			foreach ($this->configuration['TypoScript']['PageTsConfig'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $pageTConfigMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;

				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_PAGE_TSCONFIG, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}

				$ts = file_get_contents($migrationPathAndFileName);
				if (strlen($ts) === 0) {
					continue;
				}

				if($configuration['mode'] === 'overwrite') {
					$res = $this->getDatabaseConnection()->exec_UPDATEquery(
						'pages',
						'uid = ' . (int) $configuration['pageUid'],
						array(
							'TSconfig' => $ts,
							'tstamp' => time()
						)
					);
				} else {
					$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
						'TSconfig',
						'pages',
						'uid = ' . (int) $configuration['pageUid']
					);
					if (is_null($row)) {
						continue;
					}
					$res = $this->getDatabaseConnection()->exec_UPDATEquery(
						'pages',
						'uid = ' . (int) $configuration['pageUid'],
						array(
							'TSconfig' => $row['TSconfig'] . PHP_EOL .  $ts,
							'tstamp' => time()
						)
					);
				}

				if (
					$res !== FALSE
					&& $this->getDatabaseConnection()->sql_errno() === 0
					&& $this->getDatabaseConnection()->sql_affected_rows() === 1
				) {
					$this->addMigration(
						Migration::TYPE_TYPOSCRIPT_PAGE_TSCONFIG,
						$this->getPackageVersion(),
						$migrationFileName,
						$ts
					);
				}
			}
		}
	}

	/**
	 *
	 */
	protected function migrateTemplateConstantsTs() {
		// @todo make migrate function more generic
		$templateConstantsTsMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT_TEMPLATE_CONSTANTS);
		if (is_dir($templateConstantsTsMigrationsPath) && isset($this->configuration['TypoScript']['Template']['Constants'])) {
			foreach ($this->configuration['TypoScript']['Template']['Constants'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $templateConstantsTsMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;

				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_TEMPLATE_CONSTANTS, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}

				$ts = file_get_contents($migrationPathAndFileName);
				if (strlen($ts) === 0) {
					continue;
				}

				if ($configuration['mode'] === 'overwrite') {
					$res = $this->getDatabaseConnection()->exec_UPDATEquery(
						'sys_template',
						'uid = ' . (int) $configuration['templateUid'],
						array(
							'constants' => $ts,
							'tstamp' => time()
						)
					);
				} else {
					$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
						'constants',
						'sys_template',
						'uid = ' . (int) $configuration['templateUid']
					);
					if (is_null($row)) {
						continue;
					}
					$res = $this->getDatabaseConnection()->exec_UPDATEquery(
						'sys_template',
						'uid = ' . (int) $configuration['templateUid'],
						array(
							'constants' => $row['constants'] . PHP_EOL .  $ts,
							'tstamp' => time()
						)
					);
				}


				if (
					$res !== FALSE
					&& $this->getDatabaseConnection()->sql_errno() === 0
					&& $this->getDatabaseConnection()->sql_affected_rows() === 1
				) {
					$this->addMigration(
						Migration::TYPE_TYPOSCRIPT_TEMPLATE_CONSTANTS,
						$this->getPackageVersion(),
						$migrationFileName,
						$ts
					);
				}
			}
		}
	}

	/**
	 *
	 */
	protected function migrateTemplateSetupTs() {
		// @todo make migrate function more generic
		$migrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT_TEMPLATE_SETUP);
		if (is_dir($migrationsPath) && isset($this->configuration['TypoScript']['Template']['Setup'])) {
			foreach ($this->configuration['TypoScript']['Template']['Setup'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $migrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_TEMPLATE_SETUP, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}

				$ts = file_get_contents($migrationPathAndFileName);
				if (strlen($ts) === 0) {
					continue;
				}

				if ($configuration['mode'] === 'overwrite') {
					$res = $this->getDatabaseConnection()->exec_UPDATEquery(
						'sys_template',
						'uid = ' . (int) $configuration['templateUid'],
						array(
							'config' => $ts,
							'tstamp' => time()
						)
					);
				} else {
					$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
						'config',
						'sys_template',
						'uid = ' . (int) $configuration['templateUid']
					);
					if (is_null($row)) {
						continue;
					}
					$res = $this->getDatabaseConnection()->exec_UPDATEquery(
						'sys_template',
						'uid = ' . (int) $configuration['templateUid'],
						array(
							'config' => $row['config'] . PHP_EOL .  $ts,
							'tstamp' => time()
						)
					);
				}

				if (
					$res !== FALSE
					&& $this->getDatabaseConnection()->sql_errno() === 0
					&& $this->getDatabaseConnection()->sql_affected_rows() === 1
				) {
					$this->addMigration(
						Migration::TYPE_TYPOSCRIPT_TEMPLATE_SETUP,
						$this->getPackageVersion(),
						$migrationFileName,
						$ts
					);
				}
			}
		}
	}

	/**
	 *
	 */
	protected function migrateTemplateIncludeStatic() {
		// @todo make migrate function more generic
		if (is_array($this->configuration['TypoScript']['Template']['IncludeStatic'])) {
			foreach ($this->configuration['TypoScript']['Template']['IncludeStatic'] as $includeStaticPath => $configuration) {

				if (
					!is_dir(GeneralUtility::getFileAbsFileName($includeStaticPath))
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC, $this->getPackageVersion(), $includeStaticPath)
				) {
					continue;
				}

				$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
					'include_static_file',
					'sys_template',
					'uid = ' . (int) $configuration['templateUid']
				);
				if (is_null($row)) {
					continue;
				}

				// @todo: integrate ordering!?
				$includeStaticFiles = GeneralUtility::trimExplode(',', $row['include_static_file'], TRUE);
				if (!in_array($includeStaticPath, $includeStaticFiles)) {
					$includeStaticFiles[] = $includeStaticPath;
				}

				$res = $this->getDatabaseConnection()->exec_UPDATEquery(
					'sys_template',
					'uid = ' . (int) $configuration['templateUid'],
					array(
						'include_static_file' => implode(',', $includeStaticFiles),
						'tstamp' => time()
					)
				);
				if (
					$res !== FALSE
					&& $this->getDatabaseConnection()->sql_errno() === 0
					&& $this->getDatabaseConnection()->sql_affected_rows() === 1
				) {
					$this->addMigration(
						Migration::TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC,
						$this->getPackageVersion(),
						$includeStaticPath,
						var_export($configuration, TRUE)
					);
				}
			}
		}
	}

	/**
	 *
	 */
	protected function migrateDatabase() {
		// @todo make migrate function more generic
		$databaseMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_DATABASE);
		if (is_dir($databaseMigrationsPath) && isset($this->configuration['Database'])) {
			foreach ($this->configuration['Database'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $databaseMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'sql'
					|| $this->hasMigration(Migration::TYPE_DATABASE, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}

				// @todo: validate sql
				$sqlStatements = $this->getSqlStatements(file_get_contents($migrationPathAndFileName));
				if (count($sqlStatements) === 0) {
					continue;
				}

				$sqlErrors = 0;
				foreach ($sqlStatements as $statement) {
					$res = $this->getDatabaseConnection()->sql_query($statement);
					if ($res === FALSE || $this->getDatabaseConnection()->sql_errno() !== 0) {
						$sqlErrors++;
					}
				}

				if ($sqlErrors === 0) {
					$this->addMigration(
						Migration::TYPE_DATABASE,
						$this->getPackageVersion(),
						$migrationFileName,
						implode(CRLF, $sqlStatements)
					);
				}

			}
		}
	}

	/**
	 * @param $sql
	 * @return array
	 */
	protected function getSqlStatements($sql) {
		$sqlSchemaMigrationService = new \TYPO3\CMS\Install\Service\SqlSchemaMigrationService();
		return $sqlSchemaMigrationService->getStatementArray($sql, TRUE);
	}

	/**
	 *
	 */
	protected function migrateExtensionConfiguration() {
		if (!$this->hasNotAppliedExtensionConfigurationMigration()) {
			return;
		}
		$extensionConfiguration = include $this->getAbsoluteExtensionConfigurationFilePathAndName();
		if (is_array($extensionConfiguration) && !$this->dryRun) {
			$this->configurationManager->setLocalConfigurationValueByPath(
				'EXT/extConf/' . $this->package->getPackageKey(),
				serialize($extensionConfiguration)
			);
			$this->addMigration(
				Migration::TYPE_EXTCONF,
				$this->getPackageVersion(),
				$this->getExtensionConfigurationFileName(),
				serialize($extensionConfiguration)
			);
		}
	}

	/**
	 * @param integer $type
	 * @param string $version
	 * @param string $script
	 * @return mixed
	 */
	protected function hasMigration($type, $version, $script = '') {
		$where = 'type = ' . $this->getDatabaseConnection()->fullQuoteStr($type, 'tx_migrate_domain_model_migration');
		$where .= ' AND version = ' . $this->getDatabaseConnection()->fullQuoteStr($version, 'tx_migrate_domain_model_migration');
		$where .= ' AND script_path = ' . $this->getDatabaseConnection()->fullQuoteStr($script, 'tx_migrate_domain_model_migration');
		$where .= ' AND extension_key = ' . $this->getDatabaseConnection()->fullQuoteStr($this->package->getPackageKey(), 'tx_migrate_domain_model_migration');
		$where .= ' AND applied = 1';
		$where .= ' AND deleted = 0';
		$res = $this->getDatabaseConnection()->exec_SELECTcountRows(
			'*',
			'tx_migrate_domain_model_migration',
			$where
		);
		return ($res === FALSE || $res < 1) ? FALSE : TRUE;
	}

	/**
	 * @param integer $type
	 * @param string $version
	 * @param string $script
	 * @param string $rawData
	 * @return bool|\mysqli_result|object
	 */
	protected function addMigration($type, $version, $script = '', $rawData = '') {
		$res = $this->getDatabaseConnection()->exec_INSERTquery(
			'tx_migrate_domain_model_migration',
			array(
				//'pid' => 0,
				'crdate' => time(),
				'tstamp' => time(),
				'type' => $type,
				'version' => $version,
				'extension_key' => $this->package->getPackageKey(),
				'script_path' => $script,
				'applied' => TRUE,
				'query' => $rawData,
			)
		);
		return $res;
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		if (!$databaseConnection instanceof \TYPO3\CMS\Core\Database\DatabaseConnection) {

		}
		return $databaseConnection;
	}

	/**
	 *
	 */
	protected function sortDatabaseMigrationsByPriority() {
		if (!is_array($this->configuration['Database'])) {
			return;
		}
		$priority = array();
		foreach ($this->configuration['Database'] as $cogetAbsoluteMigrationScriptPathByTypenfiguration) {
			$priority[] = (int) $configuration['priority'];
		}
		array_multisort($priority, SORT_ASC, $this->configuration['Database']);
	}

	/**
	 * @param integer $type
	 * @return string
	 */
	protected function getAbsoluteMigrationScriptPathByType($type) {
		return $this->package->getPackagePath() . Migration::getMigrationScriptPathByType($type);
	}

	/**
	 * @return boolean
	 */
	public function hasPackageMigrations() {
		$hasPackageMigrations =
			$this->hasNotAppliedPageTsConfigMigrations()
			|| $this->hasNotAppliedTemplateConstantsTsMigrations()
			|| $this->hasNotAppliedTemplateSetupTsMigrations()
			|| $this->hasNotAppliedTemplateIncludeStaticMigrations()
			|| $this->hasNotAppliedDatabaseMigrations()
			|| $this->hasNotAppliedExtensionConfigurationMigration();
		return $hasPackageMigrations;
	}

	/**
	 *
	 */
	protected function getPackageVersion() {
		$packageKey = $this->package->getPackageKey();
		/** @var \Enet\Composer\Typo3\Cms\Package\ComposerAdaptedPackageManager $packageManager */
		$packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance('TYPO3\\Flow\\Package\\PackageManager');
		$composerName = $packageManager->getComposerNameFromPackageKey($packageKey);

		if ($composerName != '') {
			$canonicalPackages = $packageManager->getComposer()->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
			foreach ($canonicalPackages as $package) {
				if ($package->getName() === $composerName) {
					return $package->getVersion();
				}
			}
		}

		return $this->package->getPackageMetaData()->getVersion();
	}

	/**
	 * @return bool
	 * @todo: refactor, same conditions are used in migrate function
	 */
	protected function hasNotAppliedPageTsConfigMigrations() {
		$hasNotAppliedPageTsConfigMigrations = FALSE;
		$pageTConfigMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT_PAGE_TSCONFIG);
		if (is_dir($pageTConfigMigrationsPath) && isset($this->configuration['TypoScript']['PageTsConfig'])) {
			foreach ($this->configuration['TypoScript']['PageTsConfig'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $pageTConfigMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_PAGE_TSCONFIG, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}
				$hasNotAppliedPageTsConfigMigrations = TRUE;
			}
		}
		return $hasNotAppliedPageTsConfigMigrations;
	}

	/**
	 * @return bool
	 * @todo: refactor, same conditions are used in migrate function
	 */
	protected function hasNotAppliedTemplateConstantsTsMigrations() {
		$hasNotAppliedTemplateConstantsTsMigrations = FALSE;
		$templateConstantsTsMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT_TEMPLATE_CONSTANTS);
		if (is_dir($templateConstantsTsMigrationsPath) && isset($this->configuration['TypoScript']['Template']['Constants'])) {
			foreach ($this->configuration['TypoScript']['Template']['Constants'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $templateConstantsTsMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_TEMPLATE_CONSTANTS, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}
				$hasNotAppliedTemplateConstantsTsMigrations = TRUE;
			}
		}
		return $hasNotAppliedTemplateConstantsTsMigrations;
	}

	/**
	 * @return bool
	 * @todo: refactor, same conditions are used in migrate function
	 */
	protected function hasNotAppliedTemplateSetupTsMigrations() {
		$hasNotAppliedTemplateSetupTsMigrations = FALSE;
		$migrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT_TEMPLATE_SETUP);
		if (is_dir($migrationsPath) && isset($this->configuration['TypoScript']['Template']['Setup'])) {
			foreach ($this->configuration['TypoScript']['Template']['Setup'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $migrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_TEMPLATE_SETUP, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}
				$hasNotAppliedTemplateSetupTsMigrations = TRUE;
			}
		}
		return $hasNotAppliedTemplateSetupTsMigrations;
	}

	/**
	 * @return bool
	 * @todo: refactor, same conditions are used in migrate function
	 */
	protected function hasNotAppliedTemplateIncludeStaticMigrations() {
		$hasNotAppliedTemplateIncludeStaticMigrations = FALSE;
		if (is_array($this->configuration['TypoScript']['Template']['IncludeStatic'])) {
			foreach ($this->configuration['TypoScript']['Template']['IncludeStatic'] as $includeStaticPath => $configuration) {
				if (
					!is_dir(GeneralUtility::getFileAbsFileName($includeStaticPath))
					|| $this->hasMigration(Migration::TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC, $this->getPackageVersion(), $includeStaticPath)
				) {
					continue;
				}
				$hasNotAppliedTemplateIncludeStaticMigrations = TRUE;
			}
		}
		return $hasNotAppliedTemplateIncludeStaticMigrations;
	}

	/**
	 * @return bool
	 * @todo: refactor, same conditions are used in migrate function
	 */
	protected function hasNotAppliedDatabaseMigrations() {
		$databaseMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_DATABASE);
		$hasNotAppliedDatabaseMigrations = FALSE;
		if (is_dir($databaseMigrationsPath) && isset($this->configuration['Database'])) {
			foreach ($this->configuration['Database'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $databaseMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'sql'
					|| $this->hasMigration(Migration::TYPE_DATABASE, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}
				$hasNotAppliedDatabaseMigrations = TRUE;
			}
		}
		return $hasNotAppliedDatabaseMigrations;
	}

	/**
	 * @return bool
	 */
	protected function hasNotAppliedExtensionConfigurationMigration() {
		if (
			is_file($this->getAbsoluteExtensionConfigurationFilePathAndName())
			&& !$this->hasMigration(Migration::TYPE_EXTCONF, $this->getPackageVersion(), $this->getExtensionConfigurationFileName())
		) {
			$hasNotAppliedMigration = TRUE;
		} else {
			$hasNotAppliedMigration = FALSE;
		}
		return $hasNotAppliedMigration;
	}

	/**
	 * @return string
	 */
	protected function getExtensionConfigurationFileName() {
		return 'ExtensionConfiguration.php';
	}

	/**
	 * @return string
	 */
	protected function getAbsoluteExtensionConfigurationFilePathAndName() {
		return $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_EXTCONF) . DIRECTORY_SEPARATOR . $this->getExtensionConfigurationFileName();
	}
}
