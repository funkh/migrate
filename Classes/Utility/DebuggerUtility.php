<?php
namespace Enet\Migrate\Utility;

/*                                                                        *
 * This script belongs to the Extbase framework                           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
/**
 * This class is a backport of the corresponding class of TYPO3 Flow.
 * All credits go to the TYPO3 Flow team.
 */
/**
 * A debugging utility class
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class DebuggerUtility extends \TYPO3\CMS\Extbase\Utility\DebuggerUtility {

	/**
	 * @param mixed $variable
	 * @param string $file
	 * @param string $line
	 * @param null $blacklistedClassNames
	 * @param null $blacklistedPropertyNames
	 * @return string|void
	 */
	public static function var_dump($variable, $file = '', $line = '', $blacklistedClassNames = NULL, $blacklistedPropertyNames = NULL) {
		$title = (strlen($file) && strlen($line)) ? $file . ':' . $line : '';
		$cliMode = defined('TYPO3_cliMode') && TYPO3_cliMode === TRUE;
		parent::var_dump($variable, $title, 8, $cliMode);
	}
}