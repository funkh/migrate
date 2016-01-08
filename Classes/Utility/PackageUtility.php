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
class PackageUtility {

	/**
	 * @param \TYPO3\CMS\Core\Package\PackageInterface $package
	 * @return null|string
	 * @throws \Exception
	 */
	public static function getPackageVersion(\TYPO3\CMS\Core\Package\PackageInterface $package) {
		$packageVersion = NULL;
		if (is_null($package->getPackageMetaData()->getVersion())) {
			$_EXTKEY = $package->getPackageKey();
			$extensionManagerConfigurationFilePath = $package->getPackagePath() . 'ext_emconf.php';
			$EM_CONF = NULL;
			if (@file_exists($extensionManagerConfigurationFilePath)) {
				include $extensionManagerConfigurationFilePath;
				if (is_array($EM_CONF[$_EXTKEY]) && isset($EM_CONF[$_EXTKEY]['version'])) {
					$packageVersion = $EM_CONF[$_EXTKEY]['version'];
				}
			}
		} else {
			$packageVersion = $package->getPackageMetaData()->getVersion();
		}

		if (is_null($packageVersion)) {
			throw new \Exception(
				'Could not determine package version of package: ' . $package->getPackageKey(),
				1395908221
			);
		}

		return $packageVersion;
	}
}
