<?php
namespace Enet\Migrate\Core\Driver;

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

/**
 * Interface MigrationDriverInterface
 *
 * @package Enet\Migrate\Core\Driver
 */
interface MigrationDriverInterface {

	const BASE_PATH = 'Migration';
	const MIGRATION_VERSION_SCHEME_PATTERN = '/[0-9]{3}/';

	/**
	 * @return \Enet\Migrate\Domain\Model\MigrationResult
	 */
	public function migrate();

	/**
	 * @return integer
	 */
	public function getPriority();

}



