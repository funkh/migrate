<?php
namespace Enet\Migrate\Driver;

/***************************************************************
*  Copyright notice
*
*  (c) 2014 Helge Funk <helge.funk@e-net.info>, e-net Consulting GmbH & Co. KG
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

use Enet\Migrate\Core\Driver\AbstractDataFileMigrationDriver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionConfigurationMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class ExtensionConfigurationMigrationDriver extends AbstractDataFileMigrationDriver {

	/**
	 * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerArgument('extensionKey', 'string', TRUE, '');
		parent::initializeArguments();
	}

	/**
	 * @return bool
	 * @throws \RuntimeException
	 * @return \Enet\Migrate\Domain\Model\MigrationResult
	 */
	public function migrate() {
		$this->initializeArguments();

		/** @var \Enet\Migrate\Domain\Model\MigrationResult $result */
		$result = $this->objectManager->get('Enet\Migrate\Domain\Model\MigrationResult');

		$extensionConfiguration = include $this->getDataFile();
		if (!is_array($extensionConfiguration)) {
			throw new \RuntimeException('Extension configuration must be a serializable array.', 1394982162);
		}
		$serializedExtensionConfiguration = serialize($extensionConfiguration);
		$result->setApplied($this->configurationManager->setLocalConfigurationValueByPath(
			'EXT/extConf/' . $this->arguments['extensionKey'],
			$serializedExtensionConfiguration
		));

		return $result;
	}
}