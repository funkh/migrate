<?php
namespace Enet\Migrate\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helge Funk <helge.funk@e-net.info>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MigrationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 *
	 */
	public function initializeObject() {
		/** @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $defaultQuerySettings */
		$defaultQuerySettings = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface');
		$defaultQuerySettings->setRespectStoragePage(FALSE);
		$defaultQuerySettings->setIgnoreEnableFields(TRUE);
		$this->defaultQuerySettings = $defaultQuerySettings;
	}

	/**
	 * @param \Enet\Migrate\Domain\Model\Migration $migration
	 * @return bool
	 */
	public function isApplied(\Enet\Migrate\Domain\Model\Migration $migration) {
		$query = $this->createQuery();
		$constraint = $query->logicalAnd(array(
			$query->equals('uuid', $migration->getUuid()),
			$query->equals('driver', $migration->getDriver()),
			$query->equals('extensionKey', $migration->getExtensionKey()),
			$query->equals('applied', TRUE),
		));
		$query->matching($constraint);
		return ($query->count() === 1 ? TRUE : FALSE);
	}

	/**
	 * @param string $packageKey
	 * @param integer $version
	 * @return bool
	 */
	public function hasNotAppliedMigrations($packageKey = NULL, $version = NULL) {
		$query = $this->createQuery();
		$constraints = array(
			$query->equals('applied', FALSE),
			$query->equals('hidden', FALSE),
		);
		if (!is_null($packageKey)) {
			$constraints[] = $query->equals('extensionKey', $packageKey);
		}
		if (!is_null($version)) {
			$constraints[] = $query->equals('version', $version);
		}
		$query->matching($query->logicalAnd($constraints));
		return $query->count() > 0;
	}


	/**
	 * @param string $packageKey
	 * @return array
	 */
	public function getNotAppliedPackageMigrations($packageKey) {
		$query = $this->createQuery();
		$constraints = array(
			$query->equals('applied', FALSE),
			$query->equals('hidden', FALSE),
			$query->equals('extensionKey', $packageKey),
		);
		$query->matching($query->logicalAnd($constraints));
		$migrations = array();
		foreach ($query->execute() as $migration) {
			/** @var \Enet\Migrate\Domain\Model\Migration $migration */
			$migrations[$migration->getVersion()][$migration->getDriver()][$migration->getUuid()] = $migration;
		}
		return $migrations;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
	 */
	public function findNotApplied() {
		$query = $this->createQuery();
		$constraints = array(
			$query->equals('applied', FALSE),
			$query->equals('hidden', FALSE),
		);
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}
}
?>