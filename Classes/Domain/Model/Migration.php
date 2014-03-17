<?php
namespace Enet\Migrate\Domain\Model;

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

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Migration extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * driver
	 * @var string
	 */
	protected $driver;

	/**
	 * version
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * extensionKey
	 *
	 * @var string
	 */
	protected $extensionKey = '';

	/**
	 * extensionVersion
	 * @var string
	 */
	protected $extensionVersion;

	/**
	 * scriptPath
	 *
	 * @var string
	 */
	protected $scriptPath = '';

	/**
	 * identifier
	 * @var string
	 */
	protected $identifier;

	/**
	 * rawData
	 *
	 * @var string
	 */
	protected $rawData = '';

	/**
	 * applied
	 *
	 * @var boolean
	 */
	protected $applied = FALSE;

	/**
	 * Getter for driver
	 *
	 * @return string driver
	 */
	public function getDriver() {
		return $this->driver;
	}

	/**
	 * Setter for driver
	 *
	 * @param string $driver
	 * @return void
	 */
	public function setDriver($driver) {
		$this->driver = $driver;
	}

	/**
	 * Returns the version
	 *
	 * @return string $version
	 */
	public function getVersion() {

		return $this->version;
	}

	/**
	 * Sets the version
	 *
	 * @param string $version
	 * @return void
	 */
	public function setVersion($version) {

		$this->version = $version;
	}

	/**
	 * Returns the extensionKey
	 *
	 * @return string $extensionKey
	 */
	public function getExtensionKey() {

		return $this->extensionKey;
	}

	/**
	 * Sets the extensionKey
	 *
	 * @param string $extensionKey
	 * @return void
	 */
	public function setExtensionKey($extensionKey) {

		$this->extensionKey = $extensionKey;
	}

	/**
	 * Getter for extensionVersion
	 *
	 * @return string extensionVersion
	 */
	public function getExtensionVersion() {
		return $this->extensionVersion;
	}

	/**
	 * Setter for extensionVersion
	 *
	 * @param string $extensionVersion
	 * @return void
	 */
	public function setExtensionVersion($extensionVersion) {
		$this->extensionVersion = $extensionVersion;
	}

	/**
	 * Returns the scriptPath
	 *
	 * @return string $scriptPath
	 */
	public function getScriptPath() {

		return $this->scriptPath;
	}

	/**
	 * Sets the scriptPath
	 *
	 * @param string $scriptPath
	 * @return void
	 */
	public function setScriptPath($scriptPath) {

		$this->scriptPath = $scriptPath;
	}

	/**
	 * Getter for identifier
	 *
	 * @return string identifier
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Setter for identifier
	 *
	 * @param string $identifier
	 * @return void
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * Getter for rawData
	 *
	 * @return string rawData
	 */
	public function getRawData() {
		return $this->rawData;
	}

	/**
	 * Setter for rawData
	 *
	 * @param string $rawData
	 * @return void
	 */
	public function setRawData($rawData) {
		$this->rawData = $rawData;
	}

	/**
	 * Returns the applied
	 *
	 * @return boolean $applied
	 */
	public function getApplied() {

		return $this->applied;
	}

	/**
	 * Sets the applied
	 *
	 * @param boolean $applied
	 * @return void
	 */
	public function setApplied($applied) {

		$this->applied = $applied;
	}

	/**
	 * Returns the boolean state of applied
	 *
	 * @return boolean
	 */
	public function isApplied() {

		return $this->getApplied();
	}

}
?>