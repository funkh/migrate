<?php
namespace Enet\Migrate\Core\Driver;

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

use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Registry for driver classes.
 *
 * @package Enet\Migrate\Core\Driver
 */
class MigrationDriverRegistry implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array
	 */
	protected $drivers = array();

	/**
	 * @var array
	 */
	protected $driverConfigurations = array();

	/**
	 * Creates this object.
	 */
	public function __construct() {
		$driverConfigurations = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['registeredDrivers'];
		foreach ($driverConfigurations as $shortName => $driverConfig) {
			$shortName = $shortName ?: $driverConfig['shortName'];
			$this->registerDriverClass($driverConfig['class'], $shortName, $driverConfig);
		}
	}

	/**
	 * Registers a driver class with an optional short name.
	 *
	 * @param string $className
	 * @param string $shortName
	 * @param array $driverConfig
	 * @return boolean TRUE if registering succeeded
	 * @throws \InvalidArgumentException
	 */
	public function registerDriverClass($className, $shortName = NULL, array $driverConfig) {
		// check if the class is available for TYPO3 before registering the driver
		if (!class_exists($className)) {
			throw new \InvalidArgumentException('Class ' . $className . ' does not exist.', 1394973615);
		}

		if (!in_array('Enet\Migrate\Core\Driver\MigrationDriverInterface', class_implements($className), TRUE)) {
			throw new \InvalidArgumentException('Driver ' . $className . ' needs to implement the MigrationDriverInterface.', 1394973621);
		}
		if ($shortName === '') {
			$shortName = $className;
		}
		if (array_key_exists($shortName, $this->drivers)) {
				// Return immediately without changing configuration
			if ($this->drivers[$shortName] === $className) {
				return TRUE;
			} else {
				throw new \InvalidArgumentException('Driver ' . $shortName . ' is already registered.', 1394973654);
			}
		}
		$this->drivers[$shortName] = $className;
		$this->driverConfigurations[$shortName] = array(
			'class' => $className,
			'shortName' => $shortName,
			'label' => $driverConfig['label'],
		);

		if ($this->driverImplementsInterface($shortName, 'Enet\Migrate\Core\Driver\MigrationDriverDataFileInterface')) {
			if (!isset($driverConfig['dataFileExtensions']) || count($driverConfig['dataFileExtensions']) < 1) {
				throw new \InvalidArgumentException(
					$className . ' implements \Enet\Migrate\Core\Driver\MigrationDriverDataFileInterface and has to define at least one file extension.',
					1400584635
				);
			}
			$this->driverConfigurations[$shortName]['dataFileExtensions'] = $driverConfig['dataFileExtensions'];
		}

		return TRUE;
	}

	/**
	 * @return void
	 */
	public function addDriversToTCA() {
		// Add driver to TCA of sys_file_storage
		if (TYPO3_MODE !== 'BE') {
			return;
		}
		$driverFieldConfig = &$GLOBALS['TCA']['tx_migrate_domain_model_migration']['columns']['driver']['config'];
		foreach ($this->driverConfigurations as $driver) {
			$label = $driver['label'] ?: $driver['class'];
			$driverFieldConfig['items'][] = array($label, $driver['shortName']);
		}
	}

	/**
	 * Returns a class name for a given class name or short name.
	 *
	 * @param string $shortName
	 * @return string The class name
	 * @throws \InvalidArgumentException
	 */
	public function getDriverClass($shortName) {
		if (in_array($shortName, $this->drivers) && class_exists($shortName)) {
			return $shortName;
		}
		if (!array_key_exists($shortName, $this->drivers)) {
			throw new \InvalidArgumentException('Desired storage is not in the list of available storages.', 1314085990);
		}
		return $this->drivers[$shortName];
	}

	/**
	 * Returns the driver class names
	 *
	 * @return array The class names
	 */
	public function getDriverClassNames() {
		return $this->drivers;
	}

	/**
	 * Checks if the given driver exists
	 *
	 * @param string $shortName Name of the driver
	 * @return boolean TRUE if the driver exists, FALSE otherwise
	 */
	public function driverExists($shortName) {
		return array_key_exists($shortName, $this->drivers);
	}

	/**
	 * @param string $shortName
	 * @return array|null
	 */
	public function getDriverDataFileExtensions($shortName) {
		if ($this->driverImplementsInterface($shortName, 'Enet\Migrate\Core\Driver\MigrationDriverDataFileInterface')) {
			return $this->driverConfigurations[$shortName]['dataFileExtensions'];
		} else {
			return NULL;
		}
	}

	/**
	 * @param string $shortName
	 * @param string $interfaceName
	 * @return boolean
	 */
	public function driverImplementsInterface($shortName, $interfaceName) {
		$driverClassReflection = new \ReflectionClass($this->getDriverClass($shortName));
		return $driverClassReflection->implementsInterface($interfaceName);
	}
}
