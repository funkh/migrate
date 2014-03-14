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


class DatabaseMigrationDriver extends AbstractMigrationDriver {

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
		$databaseMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(MigrationDriverInterface::TYPE_DATABASE);
		if (is_dir($databaseMigrationsPath) && isset($this->configuration['Database'])) {
			foreach ($this->configuration['Database'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $databaseMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'sql'
					|| $this->hasMigration(MigrationDriverInterface::TYPE_DATABASE, $this->getPackageVersion(), $migrationFileName)
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
						MigrationDriverInterface::TYPE_DATABASE,
						$this->getPackageVersion(),
						$migrationFileName,
						implode(CRLF, $sqlStatements)
					);
				}

			}
		}
	}

	public function hasNotAppliedMigrations() {
		$databaseMigrationsPath = $this->getAbsoluteMigrationScriptPathByType(MigrationDriverInterface::TYPE_DATABASE);
		$hasNotAppliedDatabaseMigrations = FALSE;
		if (is_dir($databaseMigrationsPath) && isset($this->configuration['Database'])) {
			foreach ($this->configuration['Database'] as $migrationFileName => $configuration) {
				$migrationPathAndFileName = $databaseMigrationsPath . DIRECTORY_SEPARATOR . $migrationFileName;
				if (
					!is_file($migrationPathAndFileName)
					|| pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION) !== 'sql'
					|| $this->hasMigration(MigrationDriverInterface::TYPE_DATABASE, $this->getPackageVersion(), $migrationFileName)
				) {
					continue;
				}
				$hasNotAppliedDatabaseMigrations = TRUE;
			}
		}
		return $hasNotAppliedDatabaseMigrations;
	}

	/**
	 * @param $sql
	 * @return array
	 */
	protected function getSqlStatements($sql) {
		$sqlSchemaMigrationService = new \TYPO3\CMS\Install\Service\SqlSchemaMigrationService();
		return $sqlSchemaMigrationService->getStatementArray($sql, TRUE);
	}
}