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

namespace Enet\Migrate\Driver;


interface MigrationDriverInterface {

	const BASE_PATH = 'Migration';
	const TYPE_UNDEFINED = 0;
	const TYPE_EXTCONF = 1;
	const TYPE_DATABASE = 2;
	#const TYPE_TYPOSCRIPT = 3;
	const TYPE_TYPOSCRIPT_PAGE_TSCONFIG = 4;
	const TYPE_TYPOSCRIPT_TEMPLATE_CONSTANTS = 5;
	const TYPE_TYPOSCRIPT_TEMPLATE_SETUP = 6;
	const TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC = 7;

	/**
	 * @return boolean
	 */
	public function migrate($package);

	/**
	 * @return boolean
	 */
	public function hasNotAppliedMigrations();
}



