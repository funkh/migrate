<?php

namespace Enet\Migrate\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helge Funk <helge.funk@e-net.info>
 *
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
 * Test case for class \Enet\Migrate\Domain\Model\Migration.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Helge Funk <helge.funk@e-net.info>
 */
class MigrationTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Enet\Migrate\Domain\Model\Migration
	 */
	protected $subject;

	public function setUp() {
		$this->subject = new \Enet\Migrate\Domain\Model\Migration();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getVersionReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getVersion()
		);
	}

	/**
	 * @test
	 */
	public function setVersionForStringSetsVersion() {
		$this->subject->setVersion('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'version',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getExtensionKeyReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getExtensionKey()
		);
	}

	/**
	 * @test
	 */
	public function setExtensionKeyForStringSetsExtensionKey() {
		$this->subject->setExtensionKey('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'extensionKey',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getScriptPathReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getScriptPath()
		);
	}

	/**
	 * @test
	 */
	public function setScriptPathForStringSetsScriptPath() {
		$this->subject->setScriptPath('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'scriptPath',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getQueryReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getQuery()
		);
	}

	/**
	 * @test
	 */
	public function setQueryForStringSetsQuery() {
		$this->subject->setQuery('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'query',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAppliedReturnsInitialValueForBoolean() {
		$this->assertSame(
			FALSE,
			$this->subject->getApplied()
		);
	}

	/**
	 * @test
	 */
	public function setAppliedForBooleanSetsApplied() {
		$this->subject->setApplied(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'applied',
			$this->subject
		);
	}
}
