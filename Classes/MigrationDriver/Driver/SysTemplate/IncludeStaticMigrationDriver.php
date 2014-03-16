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

namespace Enet\Migrate\MigrationDriver\Driver\SysTemplate;

use Enet\Migrate\MigrationDriver\AbstractConfigurationMigrationDriver;
use Enet\Migrate\MigrationDriver\Exception\InvalidDriverConfigurationException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IncludeStaticMigrationDriver
 *
 * @package Enet\Migrate\Driver\SysTemplate
 */
class IncludeStaticMigrationDriver extends AbstractConfigurationMigrationDriver {

	/**
	 * @return string
	 */
	public function getConfigurationPath() {
		return 'TypoScript/Template/IncludeStatic';
	}

	/**
	 * @param array $configuration
	 * @throws InvalidDriverConfigurationException
	 */
	protected function validateConfiguration(array $configuration) {
		foreach ($this->configuration as $includeStaticPath => $configuration) {
			if (!is_dir(GeneralUtility::getFileAbsFileName($includeStaticPath))) {
				throw new InvalidDriverConfigurationException(
					'Static file does not exist.',
					1395003755
				);
			}
		}
	}

	/**
	 * @return bool
	 */
	public function migrate() {
		if (!$this->hasNotAppliedMigrations()) {
			return TRUE;
		}

		foreach ($this->configuration as $includeStaticPath => $configuration) {
			$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
				'include_static_file',
				'sys_template',
				'uid = ' . (int) $configuration['templateUid']
			);
			if (is_null($row)) {
				continue;
			}

			// @todo: integrate ordering!?
			$includeStaticFiles = GeneralUtility::trimExplode(',', $row['include_static_file'], TRUE);
			if (!in_array($includeStaticPath, $includeStaticFiles)) {
				$includeStaticFiles[] = $includeStaticPath;
			}

			$res = $this->getDatabaseConnection()->exec_UPDATEquery(
				'sys_template',
				'uid = ' . (int) $configuration['templateUid'],
				array(
					'include_static_file' => implode(',', $includeStaticFiles),
					'tstamp' => time()
				)
			);

			if (
				$res !== FALSE
				&& $this->getDatabaseConnection()->sql_errno() === 0
				&& $this->getDatabaseConnection()->sql_affected_rows() === 1
			) {
				$this->addMigration(
					$this->getPackageVersion(),
					$includeStaticPath,
					var_export($configuration, TRUE)
				);
			}
		}

		return TRUE;
	}

}