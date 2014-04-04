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
 * Class PageTsConfigMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class PageTsConfigMigrationDriver extends AbstractDataFileMigrationDriver {

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerArgument('pageUid', 'integer', TRUE, 0);
		$this->registerArgument('mode', 'string', FALSE, 'append');
		parent::initializeArguments();
	}

	/**
	 * @return bool|void
	 */
	public function migrate() {
		$this->initializeArguments();

		/** @var \Enet\Migrate\Domain\Model\MigrationResult $result */
		$result = $this->objectManager->get('Enet\Migrate\Domain\Model\MigrationResult');

		$typoScript = $this->getDataFileContent();
		if (strlen($typoScript) === 0) {
			return $result;
		}

		if($this->arguments['mode'] === 'overwrite') {
			$res = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
				'pages',
				'uid = ' . (int) $this->arguments['pageUid'],
				array(
					'TSconfig' => $typoScript,
					'tstamp' => time()
				)
			);
		} else {
			$row = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
				'TSconfig',
				'pages',
				'uid = ' . (int) $this->arguments['pageUid']
			);
			if (is_array($row)) {
				$res = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
					'pages',
					'uid = ' . (int) $this->arguments['pageUid'],
					array(
						'TSconfig' => $row['TSconfig'] . PHP_EOL .  $typoScript,
						'tstamp' => time()
					)
				);
			}
		}

		if ($res === FALSE || DatabaseUtility::getDatabaseConnection()->sql_errno() !== 0) {
			$result->addError('MySQLi error number: ' . DatabaseUtility::getDatabaseConnection()->sql_errno() . ' Query: ' . $statement);
		}
		return $result;
	}

}