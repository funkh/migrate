<?php
namespace Enet\Migrate\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helge Funk <helge.funk@e-net.info>, e-net Consulting GmbH & Co. KG
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

use Enet\Migrate\Core\Driver\MigrationDriverInterface;
use Enet\Migrate\Service\MigrationService\Exception\InvalidPackageConfigurationException;
use Enet\Migrate\Utility\PackageUtility;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Enet\Migrate\Utility\FilesUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use Enet\Migrate\Utility\DebuggerUtility;


/**
 *
 */
class MigrationService {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \Symfony\Component\Console\Output\ConsoleOutput
	 * @inject
	 */
	protected $output;

	/**
	 * @var \Enet\Migrate\Domain\Repository\MigrationRepository
	 * @inject
	 */
	protected $migrationRepository;

	/**
	 * @var \Enet\Migrate\Core\Driver\MigrationDriverRegistry
	 * @inject
	 */
	protected $driverRegistry;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 *
	 */
	public function __construct() {
		$this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
	}

	/**
	 * Initialize object
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->initialize();
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initialize() {
		foreach ($this->getActivePackages() as $package) {
			/** @var $package \TYPO3\CMS\Core\Package\PackageInterface */
			$packageBlacklist = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['packageBlacklist'];
			if (is_array($packageBlacklist) && in_array($package->getPackageKey(), $packageBlacklist)) {
				$this->output->writeln('<comment>  Package: ' . $package->getPackageKey() . ' has been excluded.</comment>');
				continue;
			}
			$this->initializePackageMigrations($package);
		}
	}

	/**
	 * @return array<\TYPO3\CMS\Core\Package\PackageInterface>
	 */
	protected function getActivePackages() {
		$packages = array();
		/** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
		$packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance('TYPO3\CMS\Core\Package\PackageManager');
		foreach ($packageManager->getActivePackages() as $package) {
			/** @var $package \TYPO3\CMS\Core\Package\PackageInterface */
			if (
				strpos($package->getPackagePath(), PATH_typo3 . 'sysext') !== FALSE
				|| strpos($package->getPackagePath(), PATH_site . 'Packages') !== FALSE
			) {
				// Skip system extensions and composer libraries
				continue;
			}

			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('migrate_samples')) {
				/** @var \TYPO3\CMS\Core\Configuration\ConfigurationManager $configurationManager */
				$configurationManager = $this->objectManager->get('TYPO3\CMS\Core\Configuration\ConfigurationManager');
				try {
					$migrateSamplesExtensionConfiguration = unserialize($configurationManager->getConfigurationValueByPath('EXT/extConf/migrate_samples'));
					if (
						(boolean) $migrateSamplesExtensionConfiguration['developmentMode'] === TRUE
						&& $package->getPackageKey() !== 'migrate_samples'
					) {
						continue;
					}
				} catch (\RuntimeException $e) {
				}
			}
			$packages[$package->getPackageKey()] = $package;
		}
		return $packages;
	}

	/**
	 *
	 */
	public function migrate() {
		if (!$this->migrationRepository->hasNotAppliedMigrations()) {
			return;
		}
		$this->output->writeln('Start package migrations... ');

		foreach ($this->getActivePackages() as $package) {
			/** @var $package \TYPO3\CMS\Core\Package\PackageInterface */

			if (!$this->migrationRepository->hasNotAppliedMigrations($package->getPackageKey())) {
				continue;
			}

			$packageMigrations = $this->migrationRepository->getNotAppliedPackageMigrations($package->getPackageKey());

			$this->output->writeln('');
			$this->output->writeln('<info>Migrating ' . $package->getPackageKey() . '...</info>');

			foreach ($packageMigrations as $migrationVersion => $driverConfigurations) {
				foreach ($driverConfigurations as $driverShortName => $driverConfiguration) {
					foreach ($driverConfiguration as $uuid => $migration) {
						$this->applyMigration($migration);
					}
				}
			}
		}
	}

	/**
	 * @param \Enet\Migrate\Domain\Model\Migration $migration
	 */
	public function applyMigration(\Enet\Migrate\Domain\Model\Migration $migration) {
		if ($this->migrationRepository->isApplied($migration)) {
			return;
		}
		$driverClassName = $this->driverRegistry->getDriverClass($migration->getDriver());

		/** @var \Enet\Migrate\Core\Driver\AbstractMigrationDriver $driver */
		$driver = $this->objectManager->get($driverClassName);

		if (is_array($migration->getConfiguration())) {
			$driver->setArguments($migration->getConfiguration());
		}
		if ($this->driverRegistry->driverImplementsInterface($migration->getDriver(), 'Enet\Migrate\Core\Driver\MigrationDriverDataInterface')) {
			$driver->setData($migration->getData());
		} elseif ($this->driverRegistry->driverImplementsInterface($migration->getDriver(), 'Enet\Migrate\Core\Driver\MigrationDriverDataFileInterface')) {
			$driver->setDataFile($migration->getDataFile());
		}

		$result = $driver->migrate();
		if (count($result->getErrors()) === 0) {
			$migration->setApplied(TRUE);
			$this->migrationRepository->update($migration);
			$this->persistenceManager->persistAll();
		} else {
			// @todo: write errors to migration
			// @todo: roll back migration if possible
		}
	}

	/**
	 * @param \TYPO3\CMS\Core\Package\PackageInterface $package
	 * @return array
	 */
	protected function getMigrationVersions(\TYPO3\CMS\Core\Package\PackageInterface $package) {
		$migrationVersions = array();
		if (!is_dir($package->getPackagePath() . MigrationDriverInterface::BASE_PATH)) {
			return $migrationVersions;
		}
		$migrationDirectory = array_diff(
			scandir($package->getPackagePath() . MigrationDriverInterface::BASE_PATH),
			array('..', '.')
		);
		foreach ($migrationDirectory as $entry) {
			if(preg_match(MigrationDriverInterface::MIGRATION_VERSION_SCHEME_PATTERN, $entry)) {
				$migrationVersions[] = (int) $entry;
			}
		}
		return $migrationVersions;
	}

	/**
	 * @param \TYPO3\CMS\Core\Package\PackageInterface $package
	 */
	protected function initializePackageMigrations(\TYPO3\CMS\Core\Package\PackageInterface $package) {
		try {
			foreach ($this->getMigrationVersions($package) as $migrationVersion) {
				$yamlConfigurationPathAndFileName = $this->getYamlConfigurationPathAndFileName($package, $migrationVersion);
				if (!is_file($yamlConfigurationPathAndFileName)) {
					continue;
				}

				$configuration = \Symfony\Component\Yaml\Yaml::parse($yamlConfigurationPathAndFileName);
				$this->validateConfiguration($package, $migrationVersion, $configuration);

				foreach($configuration as $driverShortName => $driverConfiguration) {

					foreach ($driverConfiguration as $uuid => $migrationData) {
						$migration = $this->migrationRepository->findOneByUuid($uuid);
						if (!($migration instanceof \Enet\Migrate\Domain\Model\Migration)) {
							/** @var \Enet\Migrate\Domain\Model\Migration $migration */
							$migration = $this->objectManager->get('Enet\Migrate\Domain\Model\Migration');
							$migration->setPid(0);
							$migration->setUuid($uuid);
							$migration->setDriver($driverShortName);
							$migration->setVersion($migrationVersion);
							$migration->setExtensionKey($package->getPackageKey());
							$migration->setExtensionVersion(PackageUtility::getPackageVersion($package));
							$migration->setApplied(FALSE);
							$migration->setHidden($this->isBlacklisted($package->getPackageKey(), $migrationVersion));

							if (is_array($migrationData['configuration'])) {
								$migration->setConfiguration($migrationData['configuration']);
							}

							if ($this->driverRegistry->driverImplementsInterface($driverShortName, 'Enet\Migrate\Core\Driver\MigrationDriverDataInterface')) {
								$migration->setData($migrationData['data']);
							} elseif ($this->driverRegistry->driverImplementsInterface($driverShortName, 'Enet\Migrate\Core\Driver\MigrationDriverDataFileInterface')) {
								$dataFilePathAndName = $this->getDataFilePathAndName(
									$package,
									$migrationVersion,
									$driverShortName,
									$migrationData['dataFile']
								);
								$migration->setDataFile($dataFilePathAndName);
							}
							$this->migrationRepository->add($migration);
							$this->persistenceManager->persistAll();
						}
					}
				}
			}
		} catch (InvalidPackageConfigurationException $e) {
			DebuggerUtility::var_dump($package->getPackageKey() . ' | Invalid package configuration: ' . $e->getMessage(), __FILE__, __LINE__);
		}
	}

	/**
	 * @param \TYPO3\CMS\Core\Package\PackageInterface $package
	 * @param integer $migrationVersion
	 * @param array $configuration
	 * @throws MigrationService\Exception\InvalidPackageConfigurationException
	 */
	protected function validateConfiguration(\TYPO3\CMS\Core\Package\PackageInterface $package, $migrationVersion, array $configuration) {

		foreach($configuration as $driverShortName => $driverConfiguration) {

			if (!$this->driverRegistry->driverExists($driverShortName)) {
				throw new InvalidPackageConfigurationException(
					'Driver: ' . $driverShortName . ' in ' . $package->getPackageKey() . ' is not registered.',
					1397080391
				);
			}

			foreach ($driverConfiguration as $uuid => $migration) {
				if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid)) {
					throw new InvalidPackageConfigurationException(
						'Migration id is not a valid identifier. Has to be an UUID.',
						1397080525
					);
				}

				if ($this->driverRegistry->driverImplementsInterface($driverShortName, 'Enet\Migrate\Core\Driver\MigrationDriverDataInterface')) {
					if (empty($migration['data']) && !is_array($migration['data'])) {
						throw new InvalidPackageConfigurationException(
							'Data has to be defined in data property for uuid = ' . $uuid,
							1400584600
						);
					}
				}

				if ($this->driverRegistry->driverImplementsInterface($driverShortName, 'Enet\Migrate\Core\Driver\MigrationDriverDataFileInterface')) {
					if (empty($migration['dataFile'])) {
						throw new InvalidPackageConfigurationException(
							'Data file has to be defined in dataFile property for uuid = ' . $uuid,
							1397509641
						);
					}

					$migrationDataFileExtension = pathinfo($migration['dataFile'], PATHINFO_EXTENSION);
					if (!in_array($migrationDataFileExtension, $this->driverRegistry->getDriverDataFileExtensions($driverShortName))) {
						throw new InvalidPackageConfigurationException(
							'Not allowed data file extension for driver: ' . $this->driverRegistry->getDriverClass($driverShortName),
							1394985565
						);
					}

					$expectedDataFilePathAndName = $this->getDataFilePathAndName($package, $migrationVersion, $driverShortName, $migration['dataFile']);
					if (!is_file($expectedDataFilePathAndName)) {
						throw new InvalidPackageConfigurationException(
							'Data file does not exist. Expected location: ' . $expectedDataFilePathAndName,
							1394985565
						);
					}
				}
			}
		}
	}

	/**
	 * @param \TYPO3\CMS\Core\Package\PackageInterface $package
	 * @param $migrationVersion
	 * @return string
	 */
	public function getYamlConfigurationPathAndFileName(\TYPO3\CMS\Core\Package\PackageInterface $package, $migrationVersion) {
		return FilesUtility::concatenatePaths(array(
			$package->getPackagePath(),
			MigrationDriverInterface::BASE_PATH,
			$migrationVersion,
			'Migrations.yaml'
		));
	}

	/**
	 * @param \TYPO3\CMS\Core\Package\PackageInterface $package
	 * @param integer $migrationVersion
	 * @param string $driverShortName
	 * @param string $dataFileName
	 * @return string
	 */
	public function getDataFilePathAndName(\TYPO3\CMS\Core\Package\PackageInterface $package, $migrationVersion, $driverShortName, $dataFileName) {
		$dataFilePathAndName = FilesUtility::concatenatePaths(array(
			$package->getPackagePath(),
			MigrationDriverInterface::BASE_PATH,
			$migrationVersion,
			$driverShortName,
			$dataFileName
		));
		return $dataFilePathAndName;
	}

	protected function isBlacklisted($packageKey, $migrationVersion) {
		$result = FALSE;
		$packageBlacklist = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['packageBlacklist'];
		if (isset($packageBlacklist[$packageKey])) {
			$versionList = $packageBlacklist[$packageKey];
			if (empty($versionList)) {
				$result = TRUE; // applies to all versions
			} else {
				$versions = explode(',', $packageBlacklist[$packageKey]);
				if (in_array($migrationVersion, $versions)) {
					$result = TRUE;
				}
			}
		}
		return $result;
	}
}
