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
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SysTemplateMigrationDriver
 *
 * @package Enet\Migrate\Driver\SysTemplate
 */
class SysTemplateMigrationDriver extends AbstractDataMigrationDriver {

	/**
	 * @var \Enet\Migrate\Domain\Model\MigrationResult
	 */
	protected $result;

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerArgument('templateUid', 'integer', TRUE, 0);
		$this->registerArgument('mode', 'string', FALSE, 'append');
		parent::initializeArguments();
	}

	/**
	 * @return bool
	 * @throws \RuntimeException
	 */
	public function migrate() {
		$this->initializeArguments();
		$this->result = $this->objectManager->get('Enet\Migrate\Domain\Model\MigrationResult');
		$this->migrateConstants();
		$this->migrateSetup();
		$this->migrateIncludeStatic();
		return $this->result;
	}

	/**
	 *
	 */
	protected function migrateConstants() {
		if (!isset($this->data['constants'])) {
			return;
		}
		$this->migrateTypoScriptField('constants', $this->data['constants']);
	}

	/**
	 *
	 */
	protected function migrateSetup() {
		if (!isset($this->data['setup'])) {
			return;
		}
		$this->migrateTypoScriptField('config', $this->data['setup']);
	}

	/**
	 * @param string $field
	 * @param $data
	 * @return bool|\mysqli_result|object
	 */
	protected function migrateTypoScriptField($field, $data) {
		if (!in_array($field, array('constants', 'config'))) {
			return;
		}
		$res = FALSE;
		if ($this->arguments['mode'] === 'overwrite') {
			$res = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
				'sys_template',
				'uid = ' . (int) $this->arguments['templateUid'],
				array(
					$field => $data,
					'tstamp' => time()
				)
			);
		} else {
			$row = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
				$field,
				'sys_template',
				'uid = ' . (int) $this->arguments['templateUid']
			);
			if (is_array($row)) {
				$res = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
					'sys_template',
					'uid = ' . (int) $this->arguments['templateUid'],
					array(
						$field => $row[$field] . PHP_EOL .  $data,
						'tstamp' => time()
					)
				);
			}
		}
		if ($res === FALSE || DatabaseUtility::getDatabaseConnection()->sql_errno() !== 0) {
			$this->result->addError('MySQLi error number: ' . DatabaseUtility::getDatabaseConnection()->sql_errno() . ' Field: ' . $field);
		}
	}

	/**
	 *
	 */
	protected function migrateIncludeStatic() {
		if (!isset($this->data['includeStatic'])) {
			return;
		}
		if (is_string($this->data['includeStatic'])) {
			$includeStaticFilesToMerge = array($this->data['includeStatic']);
		} else {
			$includeStaticFilesToMerge = (array) $this->data['includeStatic'];
		}

		foreach ($includeStaticFilesToMerge as $key => $includeStaticPath) {
			if (!is_dir(GeneralUtility::getFileAbsFileName($includeStaticPath))) {
				unset($includeStaticFilesToMerge[$key]);
			}
		}
		if (count($includeStaticFilesToMerge) < 1) {
			return;
		}

		$row = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
			'include_static_file',
			'sys_template',
			'uid = ' . (int) $this->arguments['templateUid']
		);
		if (is_null($row)) {
			return;
		}

		// @todo: integrate ordering!?
		$includeStaticFiles = GeneralUtility::trimExplode(',', $row['include_static_file'], TRUE);
		ArrayUtility::mergeRecursiveWithOverrule($includeStaticFiles, $includeStaticFilesToMerge);

		$res = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
			'sys_template',
			'uid = ' . (int) $this->arguments['templateUid'],
			array(
				'include_static_file' => implode(',', $includeStaticFiles),
				'tstamp' => time()
			)
		);

		if ($res === FALSE || DatabaseUtility::getDatabaseConnection()->sql_errno() !== 0) {
			$this->result->addError('MySQLi error number: ' . DatabaseUtility::getDatabaseConnection()->sql_errno() . ' Query: ' . $statement);
		}
	}

}