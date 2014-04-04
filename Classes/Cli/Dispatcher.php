<?php
namespace Enet\Migrate\Cli;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Thomas Christiansen <thomas.christiansen@e-net.info>
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
 * Class Dispatcher
 *
 * @package Enet\Migrate\Cli
 *
 * @todo @tc: make this a command dispatcher, not a action command itself
 */
class Dispatcher extends \TYPO3\CMS\Core\Controller\CommandLineController {

	/**
	 * migrationService
	 *
	 * @var \Enet\Migrate\Service\MigrationService
	 * @inject
	 */
	protected $migrationService;

	/**
	 *
	 */
	public function run() {
		$this->migrationService->migrate();
	}

}
?>