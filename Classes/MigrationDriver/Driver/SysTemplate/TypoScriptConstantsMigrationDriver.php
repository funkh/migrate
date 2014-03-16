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

use Enet\Migrate\MigrationDriver\AbstractSysTemplateMigrationDriver;

/**
 * Class TypoScriptConstantsMigrationDriver
 *
 * @package Enet\Migrate\Driver\SysTemplate
 */
class TypoScriptConstantsMigrationDriver extends AbstractSysTemplateMigrationDriver {

	/**
	 * @return string
	 */
	public function getConfigurationPath() {
		return 'TypoScript/Template/Constants';
	}

	/**
	 * @return array
	 */
	public function getConfigurationFileExtensions() {
		return array('ts', 'txt');
	}

	/**
	 * @return bool|void
	 */
	public function migrate() {
		if (!$this->hasNotAppliedMigrations()) {
			return TRUE;
		}

		foreach ($this->configuration as $migrationFileName => $configuration) {
			$migrationPathAndFileName = $this->getAbsoluteConfigurationPath() . $migrationFileName;
			$typoScript = file_get_contents($migrationPathAndFileName);
			if (strlen($typoScript) === 0) {
				continue;
			}

			if ($configuration['mode'] === 'overwrite') {
				$res = $this->getDatabaseConnection()->exec_UPDATEquery(
					'sys_template',
					'uid = ' . (int) $configuration['templateUid'],
					array(
						'constants' => $typoScript,
						'tstamp' => time()
					)
				);
			} else {
				$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
					'constants',
					'sys_template',
					'uid = ' . (int) $configuration['templateUid']
				);
				if (is_null($row)) {
					continue;
				}
				$res = $this->getDatabaseConnection()->exec_UPDATEquery(
					'sys_template',
					'uid = ' . (int) $configuration['templateUid'],
					array(
						'constants' => $row['constants'] . PHP_EOL .  $typoScript,
						'tstamp' => time()
					)
				);
			}

			if (
				$res !== FALSE
				&& $this->getDatabaseConnection()->sql_errno() === 0
				&& $this->getDatabaseConnection()->sql_affected_rows() === 1
			) {
				$this->addMigration(
					$this->getPackageVersion(),
					$this->getRelativeConfigurationPath() . $migrationFileName,
					$typoScript
				);
			}
		}

		return TRUE;
	}

}