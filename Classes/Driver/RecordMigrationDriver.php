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

use Enet\Migrate\Core\Driver\AbstractDataMigrationDriver;
use Enet\Migrate\Utility\DatabaseUtility;

/**
 * Class DatabaseMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class RecordMigrationDriver extends AbstractDataMigrationDriver {

	/**
	 * @return bool|void
	 */
	public function migrate() {
		/** @var \Enet\Migrate\Domain\Model\MigrationResult $result */
		$result = $this->objectManager->get('Enet\Migrate\Domain\Model\MigrationResult');

		$tables = $this->getDatabaseTables();
		foreach ($this->data as $table => $records) {
			if (!in_array($table, $tables)) {
				$this->output->write('<error>Table: ' . $table . ' does not exist.</error>');
				continue;
			}
			if (!is_array($records)) {
				$this->output->write('<error>No records for table: ' . $table . ' defined.</error>');
				continue;
			}
			$fields = $this->getTableFields($table);

			foreach ($records as $record) {
				$fieldValuePairs = array();
				foreach($record as $field => $value) {
					if (in_array($field, $fields)) {
						// @todo: implement type conversion
						$fieldValuePairs[$field] = $value;
					} else {
						$this->output->writeln('<error>Field: ' . $field . ' does not exist in table: ' . $table . '</error>');
					}
				}

				if (count($fieldValuePairs) === 0) {
					continue;
				}

				$pid = (int) $fieldValuePairs['pid'];
				if (
					$pid > 0
					&& DatabaseUtility::getDatabaseConnection()->exec_SELECTcountRows('*', 'pages', 'uid = ' . $pid) === 0) {
					$error = array(
						'PID: ' . $fieldValuePairs['pid'] . ' does not exist in table pages.',
						'table: ' . $table,
						\Symfony\Component\Yaml\Yaml::dump($record)
					);
					$result->addError(implode(LF, $error));
					continue;
				}

				if (
					isset($fieldValuePairs['uid'])
					&& DatabaseUtility::getDatabaseConnection()->exec_SELECTcountRows('*', $table, 'uid = ' . (int) $fieldValuePairs['uid']) > 0
				) {
					$uid = (int) $fieldValuePairs['uid'];
					unset($fieldValuePairs['uid']);

					$statement = DatabaseUtility::getDatabaseConnection()->UPDATEquery(
						$table,
						'uid = ' . $uid,
						$fieldValuePairs
					);
					$res = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
						$table,
						'uid = ' . $uid,
						$fieldValuePairs
					);
				} else {
					$statement = DatabaseUtility::getDatabaseConnection()->INSERTquery(
						$table,
						$fieldValuePairs
					);
					$res = DatabaseUtility::getDatabaseConnection()->exec_INSERTquery(
						$table,
						$fieldValuePairs
					);
				}

				if ($res === FALSE || DatabaseUtility::getDatabaseConnection()->sql_errno() > 0) {
					$result->addError(DatabaseUtility::getDatabaseConnection()->sql_errno() . ' Query: ' . $statement);
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getDatabaseTables() {
		$tables = array();
		$res = DatabaseUtility::getDatabaseConnection()->sql_query('SHOW TABLES');
		while($row = DatabaseUtility::getDatabaseConnection()->sql_fetch_row($res)) {
			$tables[] = $row[0];
		};
		return $tables;
	}

	/**
	 * @param $table
	 * @return array
	 */
	protected function getTableFields($table) {
		$fields = array();
		$res = DatabaseUtility::getDatabaseConnection()->sql_query('EXPLAIN ' . $table);
		while($row = DatabaseUtility::getDatabaseConnection()->sql_fetch_row($res)) {
			$fields[] = $row[0];
		};
		return $fields;
	}
}