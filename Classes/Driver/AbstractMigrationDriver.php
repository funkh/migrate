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
use Enet\Migrate\Domain\Model\Migration;

abstract class AbstractMigrationDriver implements MigrationDriverInterface {




	/**
	 * @param integer $type
	 * @param string $version
	 * @param string $script
	 * @param string $rawData
	 * @return bool|\mysqli_result|object
	 */
	public function addMigration($type, $version, $script = '', $rawData = '') {
		$res = $this->getDatabaseConnection()->exec_INSERTquery(
			'tx_migrate_domain_model_migration',
			array(
				//'pid' => 0,
				'crdate' => time(),
				'tstamp' => time(),
				'type' => $type,
				'version' => $version,
				'extension_key' => $this->package->getPackageKey(),
				'script_path' => $script,
				'applied' => TRUE,
				'query' => $rawData,
			)
		);
		return $res;
	}

	/**
	 * @param integer $type
	 * @param string $version
	 * @param string $script
	 * @return mixed
	 */
	protected function hasMigration($type, $version, $script = '') {
		$where = 'type = ' . $this->getDatabaseConnection()->fullQuoteStr($type, 'tx_migrate_domain_model_migration');
		$where .= ' AND version = ' . $this->getDatabaseConnection()->fullQuoteStr($version, 'tx_migrate_domain_model_migration');
		$where .= ' AND script_path = ' . $this->getDatabaseConnection()->fullQuoteStr($script, 'tx_migrate_domain_model_migration');
		$where .= ' AND extension_key = ' . $this->getDatabaseConnection()->fullQuoteStr($this->package->getPackageKey(), 'tx_migrate_domain_model_migration');
		$where .= ' AND applied = 1';
		$where .= ' AND deleted = 0';
		$res = $this->getDatabaseConnection()->exec_SELECTcountRows(
			'*',
			'tx_migrate_domain_model_migration',
			$where
		);
		return ($res === FALSE || $res < 1) ? FALSE : TRUE;
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		if (!$databaseConnection instanceof \TYPO3\CMS\Core\Database\DatabaseConnection) {

		}
		return $databaseConnection;
	}

	/**
	 * @param integer $type
	 * @return string
	 */
	protected function getAbsoluteMigrationScriptPathByType($type) {
		return $this->package->getPackagePath() . Migration::getMigrationScriptPathByType($type);
	}


	protected function getPackageVersion() {
		$packageKey = $this->package->getPackageKey();
		/** @var \Enet\Composer\Typo3\Cms\Package\ComposerAdaptedPackageManager $packageManager */
		$packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance('TYPO3\\Flow\\Package\\PackageManager');
		$composerName = $packageManager->getComposerNameFromPackageKey($packageKey);

		if ($composerName != '') {
			$canonicalPackages = $packageManager->getComposer()->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
			foreach ($canonicalPackages as $package) {
				if ($package->getName() === $composerName) {
					return $package->getVersion();
				}
			}
		}

		return $this->package->getPackageMetaData()->getVersion();
	}

	/**
	 *
	 */
	protected function sortDatabaseMigrationsByPriority() {
		if (!is_array($this->configuration['Database'])) {
			return;
		}
		$priority = array();
		foreach ($this->configuration['Database'] as $cogetAbsoluteMigrationScriptPathByTypenfiguration) {
			$priority[] = (int) $configuration['priority'];
		}
		array_multisort($priority, SORT_ASC, $this->configuration['Database']);
	}

}