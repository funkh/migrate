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

use TYPO3\CMS\Core\Utility\GeneralUtility;


class TemplateIncludeStaticMigrationDriver extends AbstractSystemTemplateMigrationDriver {

	/**
	 * @var \TYPO3\Flow\Package\PackageInterface
	 */
	protected $package;

	/**
	 * @var array
	 */
	protected $configuration = array();

	public function migrate($package) {
		$this->package = $package;
		$yamlConfigurationFile = $this->package->getPackagePath() . MigrationDriverInterface::BASE_PATH . '/Migrations.yaml';
		if (is_file($yamlConfigurationFile)) {
			$this->configuration = \Symfony\Component\Yaml\Yaml::parse($yamlConfigurationFile);
			$this->sortDatabaseMigrationsByPriority();
		}
		// @todo make migrate function more generic
		if (is_array($this->configuration['TypoScript']['Template']['IncludeStatic'])) {
			foreach ($this->configuration['TypoScript']['Template']['IncludeStatic'] as $includeStaticPath => $configuration) {

				if (
					!is_dir(GeneralUtility::getFileAbsFileName($includeStaticPath))
					|| $this->hasMigration(MigrationDriverInterface::TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC, $this->getPackageVersion(), $includeStaticPath)
				) {
					continue;
				}

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
						MigrationDriverInterface::TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC,
						$this->getPackageVersion(),
						$includeStaticPath,
						var_export($configuration, TRUE)
					);
				}
			}
		}
	}

	public function hasNotAppliedMigrations() {
		$hasNotAppliedTemplateIncludeStaticMigrations = FALSE;
		if (is_array($this->configuration['TypoScript']['Template']['IncludeStatic'])) {
			foreach ($this->configuration['TypoScript']['Template']['IncludeStatic'] as $includeStaticPath => $configuration) {
				if (
					!is_dir(GeneralUtility::getFileAbsFileName($includeStaticPath))
					|| $this->hasMigration(MigrationDriverInterface::TYPE_TYPOSCRIPT_TEMPLATE_INCLUDE_STATIC, $this->getPackageVersion(), $includeStaticPath)
				) {
					continue;
				}
				$hasNotAppliedTemplateIncludeStaticMigrations = TRUE;
			}
		}
		return $hasNotAppliedTemplateIncludeStaticMigrations;

}
}