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

use Symfony\Component\Yaml\Yaml;

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Migration extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * uuid
	 * @var string
	 */
	protected $uuid;

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
	 * @deprecated
	 */
	protected $scriptPath = '';

	/**
	 * identifier
	 * @var string
	 * @deprecated
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
	 * configuration
	 * @var string
	 */
	protected $configuration;

	/**
	 * configurationHash
	 * @var string
	 */
	protected $configurationHash;

	/**
	 * data
	 * @var string
	 */
	protected $data;

	/**
	 * dataHash
	 * @var string
	 */
	protected $dataHash;

	/**
	 * dataFile
	 * @var string
	 */
	protected $dataFile;

	/**
	 * @var boolean
	 */
	protected $hidden;

	/**
	 * Getter for uuid
	 *
	 * @return string uuid
	 */
	public function getUuid() {
		return $this->uuid;
	}

	/**
	 * Setter for uuid
	 *
	 * @param string $uuid
	 * @return void
	 */
	public function setUuid($uuid) {
		$this->uuid = $uuid;
	}

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

	/**
	 * Getter for configuration
	 *
	 * @return array configuration
	 */
	public function getConfiguration() {
		return Yaml::parse($this->configuration);
	}

	/**
	 * Setter for configuration
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function setConfiguration(array $configuration) {
		$this->configuration = Yaml::dump($configuration);
		$this->setConfigurationHash(sha1($this->configuration));
	}

	/**
	 * Getter for configurationHash
	 *
	 * @return string configurationHash
	 */
	public function getConfigurationHash() {
		return $this->configurationHash;
	}

	/**
	 * Setter for configurationHash
	 *
	 * @param string $configurationHash
	 * @return void
	 */
	protected function setConfigurationHash($configurationHash) {
		$this->configurationHash = $configurationHash;
	}

	/**
	 * Getter for data
	 *
	 * @return array data
	 */
	public function getData() {
		return Yaml::parse($this->data);
	}

	/**
	 * Setter for data
	 *
	 * @param array $data
	 * @return void
	 */
	public function setData(array $data) {
		$this->data = Yaml::dump($data);
		$this->setDataHash(sha1($this->data));
	}

	/**
	 * Getter for dataHash
	 *
	 * @return string dataHash
	 */
	public function getDataHash() {
		return $this->dataHash;
	}

	/**
	 * Setter for dataHash
	 *
	 * @param string $dataHash
	 * @return void
	 */
	protected function setDataHash($dataHash) {
		$this->dataHash = $dataHash;
	}

	/**
	 * Getter for dataFile
	 *
	 * @return string dataFile
	 */
	public function getDataFile() {
		return $this->dataFile;
	}

	/**
	 * Setter for dataFile
	 *
	 * @param string $dataFile
	 * @return void
	 */
	public function setDataFile($dataFile) {
		$this->dataFile = $dataFile;
		$this->setDataHash(sha1_file($dataFile));
	}

	/**
	 * @return boolean
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * @param boolean $hidden
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

}
?>