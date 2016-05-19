<?php
namespace Enet\Migrate\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2015 Helge Funk <h.funk@reply.de>, Portaltech Reply GmbH
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
 * Class FilesUtility
 *
 * @package Enet\Migrate\Utility
 */
class FilesUtility {

    /**
     * Replacing backslashes and double slashes to slashes.
     * It's needed to compare paths (especially on windows).
     *
     * @param string $path Path which should transformed to the Unix Style.
     * @return string
     * @api
     */
    public static function getUnixStylePath($path)
    {
        if (strpos($path, ':') === false) {
            return str_replace(array('//', '\\'), '/', $path);
        } else {
            return preg_replace('/^([a-z]{2,}):\//', '$1://', str_replace(array('//', '\\'), '/', $path));
        }
    }

    /**
     * Properly glues together filepaths / filenames by replacing
     * backslashes and double slashes of the specified paths.
     * Note: trailing slashes will be removed, leading slashes won't.
     * Usage: concatenatePaths(array('dir1/dir2', 'dir3', 'file'))
     *
     * @param array $paths the file paths to be combined. Last array element may include the filename.
     * @return string concatenated path without trailing slash.
     * @see getUnixStylePath()
     * @api
     */
    public static function concatenatePaths(array $paths)
    {
        $resultingPath = '';
        foreach ($paths as $index => $path) {
            $path = self::getUnixStylePath($path);
            if ($index === 0) {
                $path = rtrim($path, '/');
            } else {
                $path = trim($path, '/');
            }
            if ($path !== '') {
                $resultingPath .= $path . '/';
            }
        }
        return rtrim($resultingPath, '/');
    }
}
