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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MigrationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * migrationRepository
     *
     * @var \Enet\Migrate\Domain\Repository\MigrationRepository
     * @inject
     */
    protected $migrationRepository;

    /**
     * migrationService
     *
     * @var \Enet\Migrate\Service\MigrationService
     * @inject
     */
    protected $migrationService;

    /**
     *
     */
    public function initializeAction()
    {
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = $this->objectManager->get('TYPO3\CMS\Core\Page\PageRenderer');

        $fullPublicPath = 'EXT:migrate/Resources/Public/';
        $fullPublicPath = GeneralUtility::getFileAbsFileName($fullPublicPath);
        $fullPublicPath = \TYPO3\CMS\Core\Utility\PathUtility::getRelativePath(PATH_typo3, $fullPublicPath);

        // Add jquery.dataTables module with it's vendor default module name,
        // so the dependency in dataTables.bootstrap could be solved
        $pageRenderer->addRequireJsConfiguration(array(
            'paths' => array(
                'datatables.net' => $pageRenderer->backPath . $fullPublicPath . 'Vendor/DataTables/DataTables/media/js/jquery.dataTables',
                'datatables.bootstrap' => $pageRenderer->backPath . $fullPublicPath . 'Vendor/DataTables/DataTables/media/js/dataTables.bootstrap',
            )
        ));

        $this->prepareDocHeaderMenu();

        $this->view->assign('migrations', $this->migrationRepository->findAll());
        $this->view->assign('notAppliedMigrations', $this->migrationRepository->findNotApplied());
    }

    /**
     * action applyAllPackageMigrations
     *
     * @return void
     */
    public function applyAllPackageMigrationsAction()
    {
        $this->addFlashMessage(
            'foo',
            'bar',
            FlashMessage::INFO
        );
        $this->migrationService->migrate();
        $this->redirect('list');
    }

    /**
     * DocHeaderMenu
     */
    protected function prepareDocHeaderMenu()
    {
        $this->view->getModuleTemplate()->setModuleName('typo3-module-migrate');
        $this->view->getModuleTemplate()->setModuleId('typo3-module-migrate');
        $this->view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
    }

}

?>