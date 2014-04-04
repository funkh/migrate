<?php
namespace Enet\Migrate\Core\Driver;

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

use Enet\Migrate\Core\Driver\Exception\InvalidDriverConfigurationException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\Flow\Utility\Files;

/**
 * Class AbstractMigrationDriver
 *
 * @package Enet\Migrate\Core\Driver
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
	 * @param integer
	 */
	protected $migrationVersion;

	/**
	 * @var \Symfony\Component\Console\Output\ConsoleOutput
	 * @inject
	 */
	protected $output;

	/**
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 * @param integer $migrationVersion
	 */
	public function __construct(\TYPO3\Flow\Package\PackageInterface $package, $migrationVersion) {
		$this->package = $package;
		$this->migrationVersion = (int) $migrationVersion;
	}

	/**
	 *
	 */
	public function initializeObject() {
		$this->setConfiguration();
		// @todo: make verbosity configureable
		$this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
	}

	/**
	 * @throws \Exception
	 */
	protected function setConfiguration() {
		if (is_file($this->getYamlConfigurationPathAndFileName())) {
			$configuration = \Symfony\Component\Yaml\Yaml::parse($this->getYamlConfigurationPathAndFileName());
			try {
				if (!ArrayUtility::isValidPath($configuration, $this->getConfigurationPath())) {
					throw new InvalidDriverConfigurationException('Configuration path ' . $this->getConfigurationPath() . ' does not exist.', 1394985553);
				}
				$this->configuration = ArrayUtility::getValueByPath($configuration, $this->getConfigurationPath());
				$this->validateConfiguration($this->configuration);
			} catch (InvalidDriverConfigurationException $e) {
				$this->configuration = array();
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getYamlConfigurationPathAndFileName() {
		return $this->package->getPackagePath() . MigrationDriverInterface::BASE_PATH . DIRECTORY_SEPARATOR . $this->migrationVersion . DIRECTORY_SEPARATOR . 'Migrations.yaml';
	}

	/**
	 * @param array $configuration
	 * @throws Exception\InvalidDriverConfigurationException
	 */
	abstract protected function validateConfiguration(array $configuration);

	/**
	 *
	 */
	protected function getMigrationIdentifier() {
		$hashValues = array(
			get_class($this),
			$this->package->getPackageKey(),
			$this->migrationVersion,
		);
		return md5(implode('', $hashValues));
	}

	/**
	 * @param string $script
	 * @param string $rawData
	 * @return bool|\mysqli_result|object
	 */
	public function addMigration($script = '', $rawData = '') {

		if ($this->output->isDebug()) {
			$this->output->write('<info>Driver:</info>  ' . get_class($this), TRUE);
			$this->output->write('<info>Script:</info>  ' . $script, TRUE);
		}

		$res = $this->getDatabaseConnection()->exec_INSERTquery(
			'tx_migrate_domain_model_migration',
			array(
				//'pid' => 0,
				'crdate' => time(),
				'tstamp' => time(),
				'driver' => get_class($this),
				'version' => (int) $this->migrationVersion,
				'extension_key' => $this->package->getPackageKey(),
				'extension_version' => $this->getPackageVersion(),
				'identifier' => $this->getMigrationIdentifier(),
				'script_path' => $this->getConfigurationPath() . DIRECTORY_SEPARATOR . $script,
				'applied' => TRUE,
				'configuration' => Yaml::dump($this->configuration),
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
		$where .= ' AND version = ' . (int) $this->migrationVersion;
		$where .= ' AND script_path = ' . $this->getDatabaseConnection()->fullQuoteStr($this->getConfigurationPath() . DIRECTORY_SEPARATOR . $script, 'tx_migrate_domain_model_migration');
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
	 * @return null|string
	 * @throws \Exception
	 */
	protected function getPackageVersion() {
		/** @var \Enet\Composer\Typo3\Cms\Package\ComposerAdaptedPackageManager $packageManager */
		$packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance('TYPO3\\Flow\\Package\\PackageManager');
		$packageKey = $this->package->getPackageKey();
		$packageVersion = NULL;

		if ($packageManager instanceof \Enet\Composer\Typo3\Cms\Package\ComposerAdaptedPackageManager) {
			$composerName = $packageManager->getComposerNameFromPackageKey($packageKey);
			if ($composerName != '') {
				$canonicalPackages = $packageManager->getComposer()->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
				foreach ($canonicalPackages as $package) {
					if ($package->getName() === $composerName) {
						$packageVersion = $package->getVersion();
						break;
					}
				}
			}
		}

		if (is_null($packageVersion) || strlen($packageVersion) === 0) {
			if (is_null($this->package->getPackageMetaData()->getVersion())) {
				$_EXTKEY = $this->package->getPackageKey();
				$extensionManagerConfigurationFilePath = $this->package->getPackagePath() . 'ext_emconf.php';
				$EM_CONF = NULL;
				if (@file_exists($extensionManagerConfigurationFilePath)) {
					include $extensionManagerConfigurationFilePath;
					if (is_array($EM_CONF[$_EXTKEY]) && isset($EM_CONF[$_EXTKEY]['version'])) {
						$packageVersion = $EM_CONF[$_EXTKEY]['version'];
					}
				}
			} else {
				$packageVersion = $this->package->getPackageMetaData()->getVersion();
			}
		}

		if (is_null($packageVersion)) {
			throw new \Exception(
				'Could not determine package version of package: ' . $this->package->getPackageKey(),
				1395908221
			);
		}

		return $packageVersion;
	}

	/**
	 * @return string
	 */
	public function getAbsoluteConfigurationPath() {
		$absoluteConfigurationBasePath = Files::concatenatePaths(array(
			$this->package->getPackagePath(),
			MigrationDriverInterface::BASE_PATH,
			$this->migrationVersion,
			$this->getConfigurationPath()
		));
		return PathUtility::sanitizeTrailingSeparator($absoluteConfigurationBasePath);
	}

}