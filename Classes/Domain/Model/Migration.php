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

	const BASE_PATH = 'Migration';
	const TYPE_UNDEFINED = 0;
	const TYPE_EXTCONF = 1;
	const TYPE_DATABASE = 2;
	const TYPE_TYPOSCRIPT = 3;
	const TYPE_TYPOSCRIPT_PAGE = 4;
	const TYPE_TYPOSCRIPT_TEMPLATE = 5;

	/**
	 * @var array
	 */
	protected static $migrationPaths = array(
		self::TYPE_EXTCONF => 'Migration',
		self::TYPE_DATABASE => 'Migration/Database',
		self::TYPE_TYPOSCRIPT => 'Migration/TypoScript',
		self::TYPE_TYPOSCRIPT_PAGE => 'Migration/TypoScript/Page',
		self::TYPE_TYPOSCRIPT_TEMPLATE => 'Migration/TypoScript/Template',
	);

	/**
	 * @param $type
	 * @return mixed
	 */
	public static function getMigrationScriptPathByType($type) {
		if (isset(self::$migrationPaths[$type])) {
			return self::$migrationPaths[$type];
		} else {
			// @todo: throw exception
		}
	}

	/**
	 * type
	 *
	 * @var integer
	 */
	protected $type = 0;

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
	 * scriptPath
	 *
	 * @var string
	 */
	protected $scriptPath = '';

	/**
	 * query
	 *
	 * @var string
	 */
	protected $query = '';

	/**
	 * applied
	 *
	 * @var boolean
	 */
	protected $applied = FALSE;

	/**
	 * Returns the type
	 *
	 * @return integer $type
	 */
	public function getType() {

		return $this->type;
	}

	/**
	 * Sets the type
	 *
	 * @param integer $type
	 * @return void
	 */
	public function setType($type) {

		$this->type = $type;
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
	 * Returns the query
	 *
	 * @return string $query
	 */
	public function getQuery() {

		return $this->query;
	}

	/**
	 * Sets the query
	 *
	 * @param string $query
	 * @return void
	 */
	public function setQuery($query) {

		$this->query = $query;
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