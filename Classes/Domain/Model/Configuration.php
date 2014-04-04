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
class Configuration extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * package
	 * @var \TYPO3\Flow\Package\PackageInterface
	 */
	protected $package;

	/**
	 * localMigration
	 * @var \Enet\Migrate\Domain\Model\Migration
	 */
	protected $localMigration;

	/**
	 * Getter for package
	 *
	 * @return \TYPO3\Flow\Package\PackageInterface package
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * Setter for package
	 *
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 * @return void
	 */
	public function setPackage(\TYPO3\Flow\Package\PackageInterface $package) {
		$this->package = $package;
	}

	/**
	 * Getter for localMigration
	 *
	 * @return \Enet\Migrate\Domain\Model\Migration localMigration
	 */
	public function getLocalMigration() {
		return $this->localMigration;
	}

	/**
	 * Setter for localMigration
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $localMigration
	 * @return void
	 */
	public function setLocalMigration(\Enet\Migrate\Domain\Model\Migration $localMigration) {
		$this->localMigration = $localMigration;
	}
}
?>