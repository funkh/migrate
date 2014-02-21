<?php
namespace Enet\Migrate\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helge Funk <helge.funk@e-net.info>, e-net Consulting GmbH & Co. KG
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
 */
class ComposerUtility {

	/**
	 * @var string
	 */
	protected static $composerAutoloadFile = 'Packages/autoload.php';

	/**
	 * @var string
	 */
	protected static $composerAutoloadRealFile = 'Packages/composer/autoload_real.php';

	/**
	 *
	 */
	public static function initializeAutoloading() {
		if (file_exists(self::$composerAutoloadFile) && file_exists(self::$composerAutoloadRealFile)) {
			require_once self::$composerAutoloadRealFile;
			require_once self::$composerAutoloadFile;
		} else {
			// @todo: handle this case
		}
	}
}
