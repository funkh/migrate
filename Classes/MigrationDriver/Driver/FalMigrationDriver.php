<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Helge Funk <helge.funk@e-net.info>
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

namespace Enet\Migrate\MigrationDriver\Driver;

use Enet\Migrate\MigrationDriver\AbstractConfigurationMigrationDriver;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\Flow\Utility\Files;

/**
 * Class FalMigrationDriver
 *
 * @package Enet\Migrate\MigrationDriver\Driver
 */
class FalMigrationDriver extends AbstractConfigurationMigrationDriver {

	/**
	 * @var \TYPO3\CMS\Core\Resource\StorageRepository
	 * @inject
	 */
	protected $storageRepository;

	/**
	 * @var array
	 */
	protected $allowedFileReferenceTables = array('pages', 'tt_content');

	/**
	 * @return string
	 */
	public function getConfigurationPath() {
		return 'Fal';
	}

	/**
	 * @param array $configuration
	 */
	protected function validateConfiguration(array $configuration) {

	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function migrate() {
		if (!$this->hasNotAppliedMigrations()) {
			return TRUE;
		}

		// @todo add _cli_migrate user with concrete uid
		$GLOBALS['BE_USER']->user['uid'] = 0;

		$path = $this->getAbsoluteConfigurationBasePath() . 'Files.yaml';
		if (is_file($path)) {

			$files = \Symfony\Component\Yaml\Yaml::parse($path);
			foreach ($files as $fileData) {

				if (empty($fileData['storageUid'])) {
					$resourceStorage = $this->storageRepository->findByUid(1);
				} else {
					$resourceStorage = $this->storageRepository->findByUid((int) $fileData['storageUid']);
				}
				if (!($resourceStorage instanceof ResourceStorage)) {
					// @todo: throw specific exception
					throw new \Exception('Requested resource storage is not available.', 1395162250);
				}
				if (!$resourceStorage->isOnline()) {
					// @todo: throw specific exception
					throw new \Exception('Requested resource storage is not online.', 1395162257);
				}

				if (empty($fileData['targetFolder'])) {
					// @todo: throw specific exception
					throw new \Exception('targetFolder not set', 1395157470);
				}
				$targetFolderPath = PathUtility::sanitizeTrailingSeparator(ltrim($fileData['targetFolder']), '/');

				if (!$resourceStorage->hasFolder($targetFolderPath)) {
					$targetFolder = $resourceStorage->createFolder($targetFolderPath);
				} else {
					$targetFolder = $resourceStorage->getFolder($targetFolderPath);
				}
				if (!($targetFolder instanceof Folder)) {
					// @todo: throw specific exception
					throw new \Exception($targetFolderPath . ' is not accessible', 1395157014);
				}

				$sourceFilePathAndName = $this->getAbsoluteConfigurationBasePath() . $fileData['file'];
				$targetFilePathAndName = $this->getAbsoluteResourceStorageBasePath($resourceStorage) . $targetFolderPath . $fileData['file'];

				if (is_file($sourceFilePathAndName) && !$targetFolder->hasFile($fileData['file'])) {
					if(!@copy($sourceFilePathAndName, $targetFilePathAndName)) {
						// @todo: throw specific exception
						throw new \Exception('File copy failed.', 1395162437);
					}
					$file = $resourceStorage->getFile($targetFolderPath . $fileData['file']);
					if (!($file instanceof \TYPO3\CMS\Core\Resource\File)) {
						// @todo: throw specific exception
						throw new \Exception('File does not exist.', 1395166934);
					}

					if (is_array($fileData['references'])) {
						foreach ($fileData['references'] as $table => $uidList) {
							if (!in_array($table, $this->allowedFileReferenceTables)) {
								continue;
							}
							foreach ($uidList as $uid) {
								$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('*', $table, 'uid = ' . (int) $uid);
								if (!is_array($row)) {
									// @todo: Handle empty result
									// @todo: throw specific exception
									throw new \Exception('Row does not exist.', 1395166973);
								}

								switch ($table) {
									case 'pages':
										$fieldname = 'media';
										break;
									case 'tt_content':
										$fieldname = 'image';
										break;
								}

								$fields = array(
									// TODO add sorting/sorting_foreign
									'fieldname' => $fieldname,
									'table_local' => 'sys_file',
									// the sys_file_reference record should always placed on the same page
									// as the record to link to, see issue #46497
									'pid' => ($table === 'pages' ? $row['uid'] : $row['pid']),
									'uid_foreign' => $row['uid'],
									'uid_local' => $file->getUid(),
									'tablenames' => $table,
									'crdate' => time(),
									'tstamp' => time()
								);
								$this->getDatabaseConnection()->exec_INSERTquery('sys_file_reference', $fields);
							}
						}
					}
					$this->addMigration($fileData['file'], var_export($fileData, TRUE));
				}
			}
		}

		return TRUE;
	}

	/**
	 * @param ResourceStorage $storage
	 * @return string
	 */
	public function getAbsoluteResourceStorageBasePath(ResourceStorage $storage) {
		$storageConfiguration = $storage->getConfiguration();
		$absoluteBasePath = Files::concatenatePaths(array(
			PATH_site,
			$storageConfiguration['basePath']
		));
		return PathUtility::sanitizeTrailingSeparator($absoluteBasePath);
	}

	/**
	 * @return string
	 */
	public function getAbsoluteConfigurationBasePath() {
		return parent::getAbsoluteConfigurationPath();
	}

}