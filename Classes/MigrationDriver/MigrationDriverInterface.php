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

namespace Enet\Migrate\MigrationDriver;


interface MigrationDriverInterface {

	const BASE_PATH = 'Migration';
	const VERSION_SCHEME_PATTERN = '/[0-9]{3}/';

	/**
	 * @param \TYPO3\Flow\Package\PackageInterface $package
	 * @param integer $migrationVersion
	 */
	public function __construct(\TYPO3\Flow\Package\PackageInterface $package, $migrationVersion);

	/**
	 * @return string
	 */
	public function getConfigurationPath();

	/**
	 * @return boolean
	 */
	public function migrate();

	/**
	 * @return boolean
	 */
	public function hasNotAppliedMigrations();
}



