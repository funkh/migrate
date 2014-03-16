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
use Enet\Migrate\Driver\Exception\InvalidDriverConfigurationException;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class AbstractMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
abstract class AbstractMigrationDriver implements MigrationDriverInterface {

	/**
	 * @var \TYPO3\Flow\Package\PackageInterface
	 */
	protected $package;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 */
	public function __construct(\TYPO3\Flow\Package\PackageInterface $package) {
		$this->package = $package;
	}

	/**
	 *
	 */
	public function initializeObject() {
		$this->setConfiguration();
	}

	/**
	 * @throws \Exception
	 */
	protected function setConfiguration() {
		$yamlConfigurationFile = $this->package->getPackagePath() . MigrationDriverInterface::BASE_PATH . '/Migrations.yaml';
		if (is_file($yamlConfigurationFile)) {
			$configuration = \Symfony\Component\Yaml\Yaml::parse($yamlConfigurationFile);
			try {
				if (!ArrayUtility::isValidPath($configuration, $this->getConfigurationPath())) {
					throw new InvalidDriverConfigurationException('Configuration path ' . $this->getConfigurationPath() . ' does not exist.', 1394985553);
				}
				$this->configuration = ArrayUtility::getValueByPath($configuration, $this->getConfigurationPath());
				foreach ($this->configuration as $migrationFileName => $configuration) {
					$migrationPathAndFileName = $this->getAbsoluteConfigurationPath() . $migrationFileName;
					if (!is_file($migrationPathAndFileName)) {
						throw new InvalidDriverConfigurationException('Configuration file ' . $migrationPathAndFileName . ' does not exist.', 1394985553);
					}
					$migrationFileExtension = pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION);
					if (!in_array($migrationFileExtension, $this->getConfigurationFileExtensions())) {
						throw new InvalidDriverConfigurationException('Not allowed configuration file extension for this driver.', 1394985565);
					}
				}
			} catch (InvalidDriverConfigurationException $e) {
				$this->configuration = array();
			}
		}
	}

	/**
	 * @param string $version
	 * @param string $script
	 * @param string $rawData
	 * @return bool|\mysqli_result|object
	 */
	public function addMigration($version = '', $script = '', $rawData = '') {
		$res = $this->getDatabaseConnection()->exec_INSERTquery(
			'tx_migrate_domain_model_migration',
			array(
				//'pid' => 0,
				'crdate' => time(),
				'tstamp' => time(),
				'driver' => get_class($this),
				'version' => '000',
				'extension_key' => $this->package->getPackageKey(),
				'script_path' => $script,
				'applied' => TRUE,
				'raw_data' => $rawData,
			)
		);
		return $res;
	}

	/**
	 * @param string $script
	 * @return bool
	 */
	protected function hasMigration($script = '') {
		if (is_null($this->configuration)) {
			return FALSE;
		}
		$where = 'driver = ' . $this->getDatabaseConnection()->fullQuoteStr(get_class($this), 'tx_migrate_domain_model_migration');
		$where .= ' AND version = 000';
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
	 * @return bool
	 */
	public function hasNotAppliedMigrations() {
		$notAppliedMigrationsCount = 0;
		foreach ($this->configuration as $migrationFileName => $configuration) {
			if (!$this->hasMigration($this->getRelativeConfigurationPath() . $migrationFileName)) {
				$notAppliedMigrationsCount++;
			}
		}
		return $notAppliedMigrationsCount > 0;
	}

	/**
	 * @return string
	 */
	public function getAbsoluteConfigurationPath() {
		return $this->package->getPackagePath() . MigrationDriverInterface::BASE_PATH . DIRECTORY_SEPARATOR . $this->getConfigurationPath() . DIRECTORY_SEPARATOR;
	}

	/**
	 * @return string
	 */
	public function getRelativeConfigurationPath() {
		return MigrationDriverInterface::BASE_PATH . DIRECTORY_SEPARATOR . $this->getConfigurationPath() . DIRECTORY_SEPARATOR;
	}
}