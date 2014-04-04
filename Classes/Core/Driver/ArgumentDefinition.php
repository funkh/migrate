<?php
namespace Enet\Migrate\Core\Driver;

/*                                                                        *
 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Argument definition of each migration driver argument
 */
class ArgumentDefinition {

	/**
	 * Name of argument
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Type of argument
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Is argument required?
	 *
	 * @var boolean
	 */
	protected $required = FALSE;

	/**
	 * Default value for argument
	 *
	 * @var mixed
	 */
	protected $defaultValue = NULL;

	/**
	 * Constructor for this argument definition.
	 *
	 * @param string $name Name of argument
	 * @param string $type Type of argument
	 * @param boolean $required TRUE if argument is required
	 * @param mixed $defaultValue Default value
	 */
	public function __construct($name, $type, $required, $defaultValue = NULL) {
		$this->name = $name;
		$this->type = $type;
		$this->required = $required;
		$this->defaultValue = $defaultValue;
	}

	/**
	 * Get the name of the argument
	 *
	 * @return string Name of argument
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the type of the argument
	 *
	 * @return string Type of argument
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get the optionality of the argument
	 *
	 * @return boolean TRUE if argument is optional
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * Get the default value, if set
	 *
	 * @return mixed Default value
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}
}
