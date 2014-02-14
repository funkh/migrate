<?php
namespace Enet\Migrate\Tests\Unit\Controller;
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
 * Test case for class Enet\Migrate\Controller\MigrationController.
 *
 * @author Helge Funk <helge.funk@e-net.info>
 */
class MigrationControllerTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var Enet\Migrate\Controller\MigrationController
	 */
	protected $subject;

	public function setUp() {
		$this->subject = $this->getMock('Enet\\Migrate\\Controller\\MigrationController', array('redirect', 'forward'), array(), '', FALSE);
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function listActionFetchesAllMigrationsFromRepositoryAndAssignsThemToView() {

		$allMigrations = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('findAll'), array(), '', FALSE);
		$migrationRepository->expects($this->once())->method('findAll')->will($this->returnValue($allMigrations));
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('migrations', $allMigrations);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}

	/**
	 * @test
	 */
	public function showActionAssignsTheGivenMigrationToView() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('migration', $migration);

		$this->subject->showAction($migration);
	}

	/**
	 * @test
	 */
	public function newActionAssignsTheGivenMigrationToView() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('newMigration', $migration);
		$this->inject($this->subject, 'view', $view);

		$this->subject->newAction($migration);
	}

	/**
	 * @test
	 */
	public function createActionAddsTheGivenMigrationToMigrationRepository() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('add'), array(), '', FALSE);
		$migrationRepository->expects($this->once())->method('add')->with($migration);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->createAction($migration);
	}

	/**
	 * @test
	 */
	public function createActionAddsMessageToFlashMessageContainer() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$flashMessageContainer->expects($this->once())->method('add');
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->createAction($migration);
	}

	/**
	 * @test
	 */
	public function createActionRedirectsToListAction() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->expects($this->once())->method('redirect')->with('list');
		$this->subject->createAction($migration);
	}

	/**
	 * @test
	 */
	public function editActionAssignsTheGivenMigrationToView() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('migration', $migration);

		$this->subject->editAction($migration);
	}

	/**
	 * @test
	 */
	public function updateActionUpdatesTheGivenMigrationInMigrationRepository() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('update'), array(), '', FALSE);
		$migrationRepository->expects($this->once())->method('update')->with($migration);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->updateAction($migration);
	}

	/**
	 * @test
	 */
	public function updateActionAddsMessageToFlashMessageContainer() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('update'), array(), '', FALSE);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$flashMessageContainer->expects($this->once())->method('add');
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->updateAction($migration);
	}

	/**
	 * @test
	 */
	public function updateActionRedirectsToListAction() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('update'), array(), '', FALSE);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->expects($this->once())->method('redirect')->with('list');
		$this->subject->updateAction($migration);
	}

	/**
	 * @test
	 */
	public function deleteActionRemovesTheGivenMigrationFromMigrationRepository() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('remove'), array(), '', FALSE);
		$migrationRepository->expects($this->once())->method('remove')->with($migration);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->deleteAction($migration);
	}

	/**
	 * @test
	 */
	public function deleteActionAddsMessageToFlashMessageContainer() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('remove'), array(), '', FALSE);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$flashMessageContainer->expects($this->once())->method('add');
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->deleteAction($migration);
	}

	/**
	 * @test
	 */
	public function deleteActionRedirectsToListAction() {
		$migration = new \Enet\Migrate\Domain\Model\Migration();

		$migrationRepository = $this->getMock('Enet\\Migrate\\Domain\\Repository\\MigrationRepository', array('remove'), array(), '', FALSE);
		$this->inject($this->subject, 'migrationRepository', $migrationRepository);

		$flashMessageContainer = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\FlashMessageContainer', array('add'), array(), '', FALSE);
		$this->inject($this->subject, 'flashMessageContainer', $flashMessageContainer);

		$this->subject->expects($this->once())->method('redirect')->with('list');
		$this->subject->deleteAction($migration);
	}
}
