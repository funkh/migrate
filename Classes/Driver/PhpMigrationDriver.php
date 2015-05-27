<?php
namespace Enet\Migrate\Driver;

/***************************************************************
*  Copyright notice
*
*  (c) 2015 Thomas Christiansen <t.christiansen@reply.de>
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

/**
 * Class ExtensionConfigurationMigrationDriver
 *
 * @package Enet\Migrate\Driver
 */
class PhpMigrationDriver extends AbstractDataFileMigrationDriver {

	/**
	 * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerArgument('className', 'string', TRUE, '');
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

		require_once $this->getDataFile();
		$className = $this->arguments['className'];

		$classReflection = new \ReflectionClass($className);
		if(!$classReflection->implementsInterface('Enet\Migrate\Core\Migration\PhpMigrationInterface')) {
			throw new \Enet\Migrate\Core\Migration\Exception\InvalidPhpMigrationException($classReflection->getName() . ' must implement Enet\Migrate\Core\Migration\PhpMigrationInterface');
		};

		$migration = $classReflection->newInstance();
		$result->setApplied($migration->migrate());

		return $result;
	}
}