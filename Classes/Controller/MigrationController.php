<?php
namespace Enet\Migrate\Controller;

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

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MigrationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * migrationRepository
	 *
	 * @var \Enet\Migrate\Domain\Repository\MigrationRepository
	 * @inject
	 */
	protected $migrationRepository;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		
		$migrations = $this->migrationRepository->findAll();
		$this->view->assign('migrations', $migrations);
	}

	/**
	 * action show
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $migration
	 * @return void
	 */
	public function showAction(\Enet\Migrate\Domain\Model\Migration $migration) {
		
		$this->view->assign('migration', $migration);
	}

	/**
	 * action new
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $newMigration
	 * @dontvalidate $newMigration
	 * @return void
	 */
	public function newAction(\Enet\Migrate\Domain\Model\Migration $newMigration = NULL) {
		
		$this->view->assign('newMigration', $newMigration);
	}

	/**
	 * action create
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $newMigration
	 * @return void
	 */
	public function createAction(\Enet\Migrate\Domain\Model\Migration $newMigration) {
		
		$this->migrationRepository->add($newMigration);
		$this->flashMessageContainer->add('Your new Migration was created.');
		$this->redirect('list');
	}

	/**
	 * action edit
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $migration
	 * @return void
	 */
	public function editAction(\Enet\Migrate\Domain\Model\Migration $migration) {
		
		$this->view->assign('migration', $migration);
	}

	/**
	 * action update
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $migration
	 * @return void
	 */
	public function updateAction(\Enet\Migrate\Domain\Model\Migration $migration) {
		
		$this->migrationRepository->update($migration);
		$this->flashMessageContainer->add('Your Migration was updated.');
		$this->redirect('list');
	}

	/**
	 * action delete
	 *
	 * @param \Enet\Migrate\Domain\Model\Migration $migration
	 * @return void
	 */
	public function deleteAction(\Enet\Migrate\Domain\Model\Migration $migration) {
		
		$this->migrationRepository->remove($migration);
		$this->flashMessageContainer->add('Your Migration was removed.');
		$this->redirect('list');
	}

}
?>