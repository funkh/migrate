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
	 * packageInstallPath
	 * @var string
	 */
	protected $packageInstallPath;

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
	protected $configuration;

	/**
	 * @var bool
	 */
	protected $dryRun = FALSE;

	/**
	 * Getter for packageInstallPath
	 *
	 * @return string packageInstallPath
	 */
	public function getPackageInstallPath() {
		return $this->packageInstallPath;
	}

	/**
	 * Setter for packageInstallPath
	 *
	 * @param string $packageInstallPath
	 * @return void
	 */
	public function setPackageInstallPath($packageInstallPath) {
		$this->packageInstallPath = $packageInstallPath;
	}

	/**
	 * @return string
	 */
	protected function getAbsolutePackageInstallPath() {
		return getcwd() . DIRECTORY_SEPARATOR . $this->getPackageInstallPath();
	}

	/**
	 * @return string
	 */
	protected function getExtensionKeyFromCurrentPackage() {
		return \Enet\Composer\Utility\PackageUtility::getExtensionKeyFromPackage($this->package);
	}

	/**
	 *
	 */
	public function __construct() {
		\Enet\Migrate\Utility\ComposerUtility::initializeAutoloading();
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->configurationManager = $this->objectManager->get('TYPO3\CMS\Core\Configuration\ConfigurationManager');
		$this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
	}

	/**
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 * @return bool
	 */
	public function migratePackage(\TYPO3\Flow\Package\PackageInterface $package) {
		$this->package = $package;
		$migrationErrors = 0;

		$yamlConfigurationFile = $this->package->getPackagePath() . Migration::BASE_PATH . '/Migrations.yaml';
		if (!is_file($yamlConfigurationFile)) {
			return;
		}

		$this->output->write('Migrating ' . $package->getPackageKey() . '... ');
		try {
			$this->configuration = \Symfony\Component\Yaml\Yaml::parse($yamlConfigurationFile);
			$this->sortDatabaseMigrationsByPriority();
		} catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
			$this->output->write('<error>Could not parse migrations yaml file: ' . $e->getParsedFile() . '</error>', TRUE);
			$migrationErrors++;
		}

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

				// @todo implement append / overwrite mode
				$res = $this->getDatabaseConnection()->exec_UPDATEquery(
					'pages',
					'uid = ' . (int) $configuration['pageUid'],
					array(
						'TSconfig' => $ts
					)
				);
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

				// @todo implement append / overwrite mode
				$res = $this->getDatabaseConnection()->exec_UPDATEquery(
					'sys_template',
					'uid = ' . (int) $configuration['templateUid'],
					array(
						'constants' => $ts
					)
				);
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

				// @todo implement append / overwrite mode
				$res = $this->getDatabaseConnection()->exec_UPDATEquery(
					'sys_template',
					'uid = ' . (int) $configuration['templateUid'],
					array(
						'config' => $ts
					)
				);
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
				$sql = file_get_contents($migrationPathAndFileName);
				if (strlen($sql) === 0) {
					continue;
				}

				$res = $this->getDatabaseConnection()->sql_query($sql);
				if ($res !== FALSE && $this->getDatabaseConnection()->sql_errno() === 0) {
					$this->addMigration(
						Migration::TYPE_DATABASE,
						$this->getPackageVersion(),
						$migrationFileName,
						$sql
					);
				}

			}
		}
	}

	/**
	 *
	 */
	protected function migrateExtensionConfiguration() {
		// @todo make migrate function more generic
		$extensionConfigurationFile = 'ExtensionConfiguration.php';
		$absoluteExtensionConfigurationFile = $this->getAbsoluteMigrationScriptPathByType(Migration::TYPE_EXTCONF) . DIRECTORY_SEPARATOR . $extensionConfigurationFile;
		if (
			is_file($absoluteExtensionConfigurationFile)
			&& !$this->hasMigration(Migration::TYPE_EXTCONF, $this->getPackageVersion(), $extensionConfigurationFile)
		) {
			$extensionConfiguration = include $absoluteExtensionConfigurationFile;
			if (is_array($extensionConfiguration) && !$this->dryRun) {
				$this->configurationManager->setLocalConfigurationValueByPath(
					'EXT/extConf/' . $this->package->getPackageKey(),
					serialize($extensionConfiguration)
				);
				$this->addMigration(
					Migration::TYPE_EXTCONF,
					$this->getPackageVersion(),
					$extensionConfigurationFile,
					serialize($extensionConfiguration)
				);
			}
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
		$priority = array();
		foreach ($this->configuration['Database'] as $configuration) {
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
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 * @return boolean
	 * @todo refactor according to Migrations.yaml file
	 */
	public function hasPackageMigrations(\TYPO3\Flow\Package\PackageInterface $package) {

		$absolutePackageInstallPath = rtrim($package->getPackagePath(), '/');
		$packageHasMigrations = FALSE;

		$migrationsBasePath = array(
			$absolutePackageInstallPath,
			Migration::getMigrationScriptPathByType(Migration::BASE_PATH),
		);
		if (is_dir(implode(DIRECTORY_SEPARATOR, $migrationsBasePath))) {

			$extensionConfigurationPathAndFileName = array(
				$package->getPackagePath(),
				Migration::getMigrationScriptPathByType(Migration::TYPE_EXTCONF),
				'ExtensionConfiguration.php'
			);
			if (is_file(implode(DIRECTORY_SEPARATOR, $extensionConfigurationPathAndFileName))) {
				$packageHasMigrations = TRUE;
			}

			$databaseMigrationsBasePath = array(
				$absolutePackageInstallPath,
				Migration::getMigrationScriptPathByType(Migration::TYPE_DATABASE),
			);
			if (is_dir(implode(DIRECTORY_SEPARATOR, $databaseMigrationsBasePath))) {
				// @todo: check more reliable
				$packageHasMigrations = TRUE;
			}

			/*$typoScriptMigrationsBasePath = array(
				$absolutePackageInstallPath,
				Migration::getMigrationScriptPathByType(Migration::TYPE_TYPOSCRIPT),
			);
			if (is_dir(implode(DIRECTORY_SEPARATOR, $typoScriptMigrationsBasePath))) {
				// @todo: check more reliable
				$packageHasMigrations = TRUE;
			}*/

		}
		return $packageHasMigrations;
	}

	/**
	 *
	 */
	protected function getPackageVersion() {
		/** @var \Enet\Composer\Typo3\Cms\Package\ComposerAdaptedPackageManager $packageManager */
		$packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance('TYPO3\\Flow\\Package\\PackageManager');
		$composerName = $packageManager->getComposerNameFromPackageKey($this->package->getPackageKey());
		$canonicalPackages = $packageManager->getComposer()->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
		foreach ($canonicalPackages as $package) {
			if ($package->getName() === $composerName) {
				return $package->getVersion();
			}
		}
	}
}
