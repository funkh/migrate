<?php
namespace Enet\Migrate\Core\Driver;

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

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\Flow\Utility\Files;


/**
 * Class AbstractMigrationDriver
 *
 * @package Enet\Migrate\Core\Driver
 */
abstract class AbstractMigrationDriver implements MigrationDriverInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \Symfony\Component\Console\Output\ConsoleOutput
	 * @inject
	 */
	protected $output;

	/**
	 * @var array
	 */
	protected $argumentDefinitions = array();

	/**
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Initialize object
	 *
	 * @throws Exception\DriverInitializationException
	 * @return void
	 */
	public function initializeObject() {
		// @todo: make verbosity configureable
		$this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
	}

	/**
	 *
	 */
	public function initializeArguments() {
		$this->validateArguments($this->arguments);
		foreach ($this->argumentDefinitions as $argumentName => $argumentDefinition) {
			/** @var ArgumentDefinition $argumentDefinition */
			if (!isset($this->arguments[$argumentName])) {
				$this->arguments[$argumentName] = $argumentDefinition->getDefaultValue();
			}
		}
	}

	/**
	 * @param array $arguments
	 * @return void
	 */
	public function setArguments(array $arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * Validate arguments, and throw exception if arguments do not validate.
	 *
	 * @param array $arguments
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function validateArguments(array $arguments) {
		foreach ($this->argumentDefinitions as $argumentName => $argumentDefinition) {
			/** @var ArgumentDefinition $argumentDefinition */
			if ($argumentDefinition->isRequired() && !isset($this->arguments[$argumentName])) {
				throw new \InvalidArgumentException(
					'The argument "' . $argumentName . '" is required and was not set in migration driver "' . get_class($this) . '"',
					1398702705
				);
			}
		}

		if (!count($arguments)) {
			return;
		}

		foreach ($arguments as $argumentName => $argumentValue) {
			if (!($this->argumentDefinitions[$argumentName] instanceof ArgumentDefinition)) {
				throw new \InvalidArgumentException(
					'The argument "' . $argumentName . '" was not registered in migration driver "' . get_class($this) . '"',
					1398702705
				);
			}

			/** @var ArgumentDefinition $registeredArgumentDefinition */
			$registeredArgumentDefinition = $this->argumentDefinitions[$argumentName];

			if ($argumentValue === $registeredArgumentDefinition->getDefaultValue()) {
				continue;
			}

			// @todo: validate different types, with specific methods like empty(string), count(array), ???(integer) etc
			switch($registeredArgumentDefinition->getType()) {
				case 'array':
					if (!is_array($argumentValue) && !$argumentValue instanceof \ArrayAccess && !$argumentValue instanceof \Traversable) {
						throw new \InvalidArgumentException(
							'The argument "' . $argumentName . '" was registered with type "array", but is of type "' . gettype($argumentValue) . '" in migration driver "' . get_class($this) . '"',
							1398702705
						);
					}
					break;
				case 'boolean':
				case 'bool':
					if (!is_bool($argumentValue)) {
						throw new \InvalidArgumentException(
							'The argument "' . $argumentName . '" was registered with type "boolean", but is of type "' . gettype($argumentValue) . '" in migration driver "' . get_class($this) . '".',
							1398702715
						);
					}

					break;
				case 'integer':
				case 'int':
					if (!is_integer($argumentValue)) {
						throw new \InvalidArgumentException(
							'The argument "' . $argumentName . '" was registered with type "integer", but is of type "' . gettype($argumentValue) . '" in migration driver "' . get_class($this) . '".',
							1398702722
						);
					}
					break;
				case 'string':
					if (!is_string($argumentValue)) {
						throw new \InvalidArgumentException(
							'The argument "' . $argumentName . '" was registered with type "string", but is of type "' . gettype($argumentValue) . '" in migration driver "' . get_class($this) . '".',
							1400501284
						);
					}
					break;
			}
		}
	}

	/**
	 * Register a configuration argument.
	 *
	 * @param string $name
	 * @param string $type
	 * @param boolean $required
	 * @param string $defaultValue
	 */
	public function registerArgument($name, $type, $required, $defaultValue) {
		$this->argumentDefinitions[$name] = new ArgumentDefinition($name, $type, $required, $defaultValue);
	}

	/**
	 * @return integer
	 */
	public function getPriority() {
		return 100;
	}

}