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
use Enet\Migrate\Utility\UserUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Impexp\Utility\ImportExportUtility;

/**
 * Class T3dMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class T3dMigrationDriver extends AbstractDataFileMigrationDriver {

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerArgument('update', 'boolean', FALSE, TRUE);
		$this->registerArgument('forceAllUids', 'boolean', FALSE, FALSE);
		$this->registerArgument('targetPid', 'integer', FALSE, 0);
		parent::initializeArguments();
	}

	/**
	 * @return \Enet\Migrate\Domain\Model\MigrationResult
	 */
	public function migrate() {
		$this->initializeArguments();

		/** @var \Enet\Migrate\Domain\Model\MigrationResult $result */
		$result = $this->objectManager->get('Enet\Migrate\Domain\Model\MigrationResult');

		if (is_null($GLOBALS['BE_USER'])) {
			UserUtility::createFakeBackendUser();
		}

		if (is_null($GLOBALS['LANG'])) {
			Bootstrap::getInstance()->initializeLanguageObject();
		}

		// @todo: validate target pid
		$response = $this->importT3DFile(array(
			'targetPid' => $this->arguments['targetPid'],
			'forceAllUids' => $this->arguments['forceAllUids'],
			'update' => $this->arguments['update'],
			'file' => $this->getDataFile()
		));
		if (count($response) === 0) {
			$result->addError('T3D Import failed: ' . $this->getDataFile());
		}

		return $result;
	}

	/**
	 * Import a T3D file directly
	 *
	 * @param array $configuration
	 * @throws \ErrorException
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function importT3DFile(array $configuration) {
		$importResponse = array();
		if (!is_string($configuration['file'])) {
			throw new \InvalidArgumentException('Input parameter $file has to be of type string', 1377625645);
		}
		if (!is_int($configuration['targetPid'])) {
			throw new \InvalidArgumentException('Input parameter $int has to be of type integer', 1377625646);
		}
		/** @var $import \TYPO3\CMS\Impexp\ImportExport */
		$import = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Impexp\ImportExport');
		$import->init(0, 'import');

		if ($import->loadFile($configuration['file'], 1)) {
			$import->importData($configuration['targetPid']);
			$import->force_all_UIDS = $configuration['forceAllUids'];
			$import->update = $configuration['update'];

			// Get id of container page:
			$newPages = $import->import_mapId['pages'];
			reset($newPages);
			$importResponse = current($newPages);

			$this->t3dData = $import->dat;
		}

		// Check for errors during the import process:
		if (empty($importResponse) && count($import->errorLog) > 0) {
			foreach ($import->errorLog as $log) {
				$this->output->writeln($log);
			}
			return array();
		} else {
			return $importResponse;
		}
	}

}