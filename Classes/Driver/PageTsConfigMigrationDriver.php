<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Jens Petersen <jens.petersen@e-net.info>, e-net Consulting GmbH & Co. KG
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

namespace Enet\Migrate\Driver;


class PageTsConfigMigrationDriver extends AbstractSystemTemplateMigrationDriver {

	/**
	 * @var \TYPO3\Flow\Package\PackageInterface
	 */
	protected $package;

	/**
	 * @var array
	 */
	protected $configuration = array();

	public function migrate($package) {
		$this->package = $package;
		$yamlConfigurationFile = $this->package->getPackagePath() . MigrationDriverInterface::BASE_PATH . '/Migrations.yaml';
		if (is_file($yamlConfigurationFile)) {
			$this->configuration = \Symfony\Component\Yaml\Yaml::parse($yamlConfigurationFile);
			$this->sortDatabaseMigrationsByPriority();
		}
		// @todo make migrate function more generic
		$pageTConfigMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(MigrationDriverInterface::TYPE_TYPOSCRIPT_PAGE_TSCONFIG);
		if (is_dir($pageTConfigMigrationsPath) && isset($this->configuration['TypoScript']['PageTsConfig'])) {
			foreach ($this->configuration['TypoScript']['PageTsConfig'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $pageTConfigMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;

				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(MigrationDriverInterface::TYPE_TYPOSCRIPT_PAGE_TSCONFIG, $this->getPackageVersion(), $migrationFileName)
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
						MigrationDriverInterface::TYPE_TYPOSCRIPT_PAGE_TSCONFIG,
						$this->getPackageVersion(),
						$migrationFileName,
						$ts
					);
				}
			}
		}
	}

	public function hasNotAppliedMigrations() {
		$hasNotAppliedPageTsConfigMigrations = FALSE;
		$pageTConfigMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(MigrationDriverInterface::TYPE_TYPOSCRIPT_PAGE_TSCONFIG);
		if (is_dir($pageTConfigMigrationsPath) && isset($this->configuration['TypoScript']['PageTsConfig'])) {
			foreach ($this->configuration['TypoScript']['PageTsConfig'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $pageTConfigMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'ts'
					|| $this->hasMigration(MigrationDriverInterface::TYPE_TYPOSCRIPT_PAGE_TSCONFIG, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}
				$hasNotAppliedPageTsConfigMigrations = TRUE;
			}
		}
		return $hasNotAppliedPageTsConfigMigrations;
	}
}