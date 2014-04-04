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
class MigrationResult extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * uuid
	 * @var string
	 */
	protected $uuid;

	/**
	 * applied
	 * @var boolean
	 */
	protected $applied = FALSE;

	/**
	 * errors
	 * @var array<string>
	 */
	protected $errors = array();

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
	 * Getter for applied
	 *
	 * @return boolean applied
	 */
	public function getApplied() {
		return $this->applied;
	}

	/**
	 * Setter for applied
	 *
	 * @param boolean $applied
	 * @return void
	 */
	public function setApplied($applied) {
		$this->applied = (boolean) $applied;
	}

	/**
	 * Getter for errors
	 *
	 * @return array<string> errors
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Adds a Error
	 *
	 * @param string $error the Error to be added
	 * @return void
	 */
	public function addError($error) {
		$this->errors[] = $error;
	}
}
?>