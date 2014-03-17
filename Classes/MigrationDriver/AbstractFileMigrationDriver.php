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

namespace Enet\Migrate\MigrationDriver;

use Enet\Migrate\MigrationDriver\Exception\InvalidDriverConfigurationException;

/**
 * Class AbstractSysTemplateMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
abstract class AbstractFileMigrationDriver extends AbstractMigrationDriver {

	/**
	 * @return array
	 */
	abstract public function getConfigurationFileExtensions();

	/**
	 * @param array $configuration
	 * @throws Exception\InvalidDriverConfigurationException
	 */
	protected function validateConfiguration(array $configuration) {
		foreach ($configuration as $migrationFileName => $migrationConfiguration) {
			$migrationPathAndFileName = $this->getAbsoluteConfigurationPath() . $migrationFileName;
			if (!is_file($migrationPathAndFileName)) {
				throw new InvalidDriverConfigurationException('Configuration file ' . $migrationPathAndFileName . ' does not exist.', 1394985553);
			}
			$migrationFileExtension = pathinfo($migrationPathAndFileName, PATHINFO_EXTENSION);
			if (!in_array($migrationFileExtension, $this->getConfigurationFileExtensions())) {
				throw new InvalidDriverConfigurationException('Not allowed configuration file extension for this driver.', 1394985565);
			}
		}
	}

	/**
	 * @return bool
	 */
	public function hasNotAppliedMigrations() {
		$notAppliedMigrationsCount = 0;
		foreach ($this->configuration as $migrationFileName => $configuration) {
			if (!$this->hasMigration($migrationFileName)) {
				$notAppliedMigrationsCount++;
			}
		}
		return $notAppliedMigrationsCount > 0;
	}

	/**
	 * @return string
	 */
	public function getAbsoluteConfigurationPath() {
		return $this->package->getPackagePath() . MigrationDriverInterface::BASE_PATH . DIRECTORY_SEPARATOR . $this->migrationVersion . DIRECTORY_SEPARATOR . $this->getConfigurationPath() . DIRECTORY_SEPARATOR;
	}

	/**
	 * @return string
	 */
	public function getRelativeConfigurationPath() {
		return MigrationDriverInterface::BASE_PATH . DIRECTORY_SEPARATOR . $this->migrationVersion . DIRECTORY_SEPARATOR . $this->getConfigurationPath() . DIRECTORY_SEPARATOR;
	}
}