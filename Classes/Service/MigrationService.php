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

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
class MigrationService {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \Symfony\Component\Console\Output\ConsoleOutput
	 */
	protected $output;

	/**
	 *
	 */
	public function __construct() {
		\Enet\Migrate\Utility\ComposerUtility::initializeAutoloading();
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->configurationManager = $this->objectManager->get('TYPO3\CMS\Core\Configuration\ConfigurationManager');
		$this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
	}

	/**
	 *
	 */
	public function migrate() {
		/** @var \Enet\Composer\Typo3\Cms\Package\ComposerAdaptedPackageManager $packageManager */
		$packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance('TYPO3\\Flow\\Package\\PackageManager');
		foreach ($packageManager->getActivePackages() as $activePackage) {
			/** @var $activePackage \TYPO3\Flow\Package\PackageInterface */
			if (
				strpos($activePackage->getPackagePath(), PATH_typo3 . 'sysext') !== FALSE
				|| strpos($activePackage->getPackagePath(), PATH_site . 'Packages') !== FALSE
			) {
				// Skip system extensions and composer libraries
				continue;
			}
			$this->migratePackage($activePackage);
		}
	}

	/**
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 */
	protected function migratePackage(\TYPO3\Flow\Package\PackageInterface $package) {
		/** @var $driverRegistry \Enet\Migrate\Driver\MigrationDriverRegistry */
		$driverRegistry = $this->objectManager->get('Enet\Migrate\Driver\MigrationDriverRegistry');
		foreach ($driverRegistry->getDriverClassNames() as $driverClassName) {
			/** @var \Enet\Migrate\Driver\MigrationDriverInterface $driver */
			$driver = $this->objectManager->get($driverClassName, $package);
			if ($driver->hasNotAppliedMigrations()) {
				$this->output->write('Migrating ' . $package->getPackageKey() . '... ');
				if ($driver->migrate()) {
					$this->output->write('<info>OK</info>', TRUE);
				} else {
					$this->output->write('<error>Failed</error>', TRUE);
				}
			}
		}
	}

}
