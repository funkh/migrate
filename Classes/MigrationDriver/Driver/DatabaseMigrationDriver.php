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

namespace Enet\Migrate\MigrationDriver\Driver;

use Enet\Migrate\MigrationDriver\AbstractFileMigrationDriver;

class DatabaseMigrationDriver extends AbstractFileMigrationDriver {

	/**
	 * @return string
	 */
	public function getConfigurationPath() {
		return 'Database';
	}

	/**
	 * @return array
	 */
	public function getConfigurationFileExtensions() {
		return array('sql');
	}

	/**
	 *
	 */
	protected function setConfiguration() {
		parent::setConfiguration();
		$this->sortDatabaseMigrationsByPriority();
	}

	/**
	 * @return bool|void
	 */
	public function migrate() {
		if (!$this->hasNotAppliedMigrations()) {
			return TRUE;
		}

		foreach ($this->configuration as $migrationFileName => $configuration) {
			$migrationPathAndFileName = $this->getAbsoluteConfigurationPath() . $migrationFileName;

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
					$this->getPackageVersion(),
					$this->getRelativeConfigurationPath() . $migrationFileName,
					implode(CRLF, $sqlStatements)
				);
			}
		}

		return TRUE;
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
	protected function sortDatabaseMigrationsByPriority() {
		if (!is_array($this->configuration['Database'])) {
			return;
		}
		$priority = array();
		foreach ($this->configuration['Database'] as $configuration) {
			$priority[] = (int) $configuration['priority'];
		}
		array_multisort($priority, SORT_ASC, $this->configuration['Database']);
	}
}