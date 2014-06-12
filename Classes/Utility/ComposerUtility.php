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
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 *
 */
class ComposerUtility {

	/**
	 * @var string
	 */
	protected static $composerAutoloadFile = 'autoload.php';

	/**
	 * @var string
	 */
	protected static $composerAutoloadRealFile = 'composer/autoload_real.php';

	/**
	 * @throws \RuntimeException
	 */
	public static function initializeAutoloading() {
		$absoluteComposerAutoloadFilePath = self::getAbsoluteComposerVendorDir() . self::$composerAutoloadFile;
		$absoluteComposerAutoloadRealFilePath = self::getAbsoluteComposerVendorDir() . self::$composerAutoloadRealFile;
		if (file_exists($absoluteComposerAutoloadFilePath) && file_exists($absoluteComposerAutoloadRealFilePath)) {
			require_once $absoluteComposerAutoloadRealFilePath;
			require_once $absoluteComposerAutoloadFilePath;
		} else {
			throw new \RuntimeException(
				'Composer autoload not initialized, autoload files missing.',
				1402490526
			);
		}
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getAbsoluteComposerVendorDir() {
		$composerConfigurationPath = PATH_site . 'composer.json';
		if (!file_exists($composerConfigurationPath)) {
			throw new \RuntimeException('composer.json does not exist. This seems to be no composer project!!!', 1402493622);
		}
		$composerConfiguration = json_decode(file_get_contents($composerConfigurationPath));
		if (!isset($composerConfiguration->config->{'vendor-dir'})) {
			throw new \RuntimeException('vendor-dir in composer.json is not set.', 1402493665);
		}
		$vendorDir = PATH_site . $composerConfiguration->config->{'vendor-dir'};
		if (!is_dir($vendorDir)) {
			throw new \RuntimeException('vendor dir does not exist.', 1402493656);
		}
		return PathUtility::sanitizeTrailingSeparator($vendorDir);
	}
}
