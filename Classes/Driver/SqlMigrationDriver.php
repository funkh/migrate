<?php
namespace Enet\Migrate\Driver;

/***************************************************************
*  Copyright notice
*
*  (c) 2014 Helge Funk <helge.funk@e-net.info>, e-net Consulting GmbH & Co. KG
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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

use Enet\Migrate\Core\Driver\AbstractDataFileMigrationDriver;
use Enet\Migrate\Utility\DatabaseUtility;

/**
 * Class SqlMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class SqlMigrationDriver extends AbstractDataFileMigrationDriver {

	/**
	 * @return \Enet\Migrate\Domain\Model\MigrationResult
	 */
	public function migrate() {
		$this->initializeArguments();

		/** @var \Enet\Migrate\Domain\Model\MigrationResult $result */
		$result = $this->objectManager->get('Enet\Migrate\Domain\Model\MigrationResult');

		$sqlStatements = $this->getSqlStatements($this->getDataFileContent());
		if (count($sqlStatements) === 0) {
			$this->output->writeln('<notice>File: ' . $this->getDataFile() . ' contains no sql statements.</notice>');
		}

		foreach ($sqlStatements as $statement) {
			$res = DatabaseUtility::getDatabaseConnection()->sql_query($statement);
			if ($res === FALSE || DatabaseUtility::getDatabaseConnection()->sql_errno() !== 0) {
				$result->addError('MySQLi error number: ' . DatabaseUtility::getDatabaseConnection()->sql_errno() . ' Query: ' . $statement);
			}
		}

		return $result;
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