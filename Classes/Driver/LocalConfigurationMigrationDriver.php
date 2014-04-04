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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Enet\Migrate\Core\Driver\AbstractFileMigrationDriver;

/**
 * Class ExtensionConfigurationMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class LocalConfigurationMigrationDriver extends AbstractFileMigrationDriver {

	/**
	 * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * @return string
	 */
	public function getConfigurationPath() {
		return 'LocalConfiguration';
	}

	/**
	 * @return array
	 */
	public function getConfigurationFileExtensions() {
		return array('php');
	}

	/**
	 * @return bool
	 * @throws \RuntimeException
	 */
	public function migrate() {
		if (!$this->hasNotAppliedMigrations()) {
			return TRUE;
		}

		foreach ($this->configuration as $migrationFileName => $configuration) {
			$extensionConfiguration = include $this->getAbsoluteConfigurationPath() . $migrationFileName;
			if (!is_array($extensionConfiguration)) {
				throw new \RuntimeException('Extension configuration must be a serializable array.', 1394982162);
			}
			$serializedExtensionConfiguration = serialize($extensionConfiguration);
			$result = $this->configurationManager->setLocalConfigurationValueByPath(
				'EXT/extConf/' . $configuration['extensionKey'],
				$serializedExtensionConfiguration
			);
			if ($result === TRUE) {
				$this->addMigration(
					$migrationFileName,
					$serializedExtensionConfiguration
				);
			} else {
				// @todo: implement some type of logging
			}
		}
		return TRUE;
	}
}