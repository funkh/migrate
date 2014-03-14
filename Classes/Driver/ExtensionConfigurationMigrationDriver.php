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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionConfigurationMigrationDriver extends AbstractMigrationDriver{

	/**
	 * @var \TYPO3\Flow\Package\PackageInterface
	 */
	protected $package;

	/**
	 * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	public function migrate($package) {
		$this->package = $package;
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->configurationManager = $this->objectManager->get('TYPO3\CMS\Core\Configuration\ConfigurationManager');
		if (!$this->hasNotAppliedMigrations()) {
			return;
		}
		$extensionConfiguration = include $this->getAbsoluteExtensionConfigurationFilePathAndName();
		if (is_array($extensionConfiguration) && !$this->dryRun) {
			$this->configurationManager->setLocalConfigurationValueByPath(
				'EXT/extConf/' . $this->package->getPackageKey(),
				serialize($extensionConfiguration)
			);
			$this->addMigration(
				Migration::TYPE_EXTCONF,
				$this->getPackageVersion(),
				$this->getExtensionConfigurationFileName(),
				serialize($extensionConfiguration)
			);
		}
	}

	public function hasNotAppliedMigrations() {
		if (
			is_file($this->getAbsoluteExtensionConfigurationFilePathAndName())
			&& !$this->hasMigration(Migration::TYPE_EXTCONF, $this->getPackageVersion(), $this->getExtensionConfigurationFileName())
		) {
			$hasNotAppliedMigration = TRUE;
		} else {
			$hasNotAppliedMigration = FALSE;
		}
		return $hasNotAppliedMigration;
	}

	protected function getAbsoluteExtensionConfigurationFilePathAndName() {
		return $this->getAbsoluteMigrationScriptPathByType(MigrationDriverInterface::TYPE_EXTCONF) . DIRECTORY_SEPARATOR . $this->getExtensionConfigurationFileName();
	}

	/**
	 * @return string
	 */
	protected function getExtensionConfigurationFileName() {
		return 'ExtensionConfiguration.php';
	}

}