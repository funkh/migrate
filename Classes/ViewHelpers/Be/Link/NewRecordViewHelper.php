<?php
namespace Enet\Migrate\ViewHelpers\Be\Link;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kemal Emre <kemal.emre@e-net.info>, e-net Consulting GmbH & Co. KG
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class NewRecordViewHelper
 *
 * @package Enet\Migrate\ViewHelpers\Be\Link
 *
 * Link to the backend editing of an existing or new record.
 *
 * = Examples =
 *
 * <code title="edit existing record">
 * <fx:be.link.editRecord table="pages" uid="7">Edit page 7</fx:be.link.editRecord>
 * </code>
 * <output>
 * <a href="" onclick="window.location.href='alt_doc.php?returnUrl=/typo3/mod.php...&edit[pages][7]=edit'; return false;">Edit page 7</a>
 * </output>
 *
 * <code title="edit new record">
 * <fx:be.link.editRecord table="pages" newRecordStoragePid="7">New page in page 7</fx:be.link.editRecord>
 * </code>
 * <output>
 * <a href="" onclick="window.location.href='alt_doc.php?returnUrl=/typo3/mod.php...&edit[pages][7]=new'; return false;">New page in page 7</a>
 * </output>
 *
 */
class NewRecordViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
		$this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document');
		$this->registerTagAttribute('rev', 'string', 'Specifies the relationship between the linked document and the current document');
		$this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
	}

	/**
	 * @param $className Name of the records table
	 * @param int $newRecordStoragePid Where a new record should be created, defaults to root
	 * @param string $backPath /typo3/ by default
	 * @param string $returnUrl By default the url the request came from
	 * @param boolean $showSpriteIcon Show the default document-open sprite icon instead of rendering the child content
	 * @return string Rendered a tag
	 */
	public function render($className, $newRecordStoragePid = 0, $backPath = '', $returnUrl = '', $showSpriteIcon = TRUE) {
		$parameters = array();
		$parameters[] = '&edit';
		$parameters[] = $this->wrapArgument($this->resolveTableName($className));
		$parameters[] = $this->wrapArgument(intval($newRecordStoragePid));
		$parameters[] = '=new';
		$editOnClick = BackendUtility::editOnClick(implode('', $parameters), $backPath, $returnUrl);

		$this->tag->addAttribute('href', '#');
		$this->tag->addAttribute('onclick', $editOnClick);

		if ($showSpriteIcon === TRUE) {
			$this->tag->setContent(IconUtility::getSpriteIcon('actions-document-new'));
			$this->tag->forceClosingTag(FALSE);
		} else {
			$this->tag->setContent($this->renderChildren());
			$this->tag->forceClosingTag(TRUE);
		}

		return $this->tag->render();
	}

	/**
	 * wraps argument [|]
	 *
	 * @param string $argument
	 * @return string
	 */
	protected function wrapArgument($argument) {
		return '[' . $argument . ']';
	}

	/**
	 * @param string $className
	 * @return string
	 */
	protected function resolveTableName($className) {
		/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory $dataMapFactory */
		$dataMapFactory = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory');
		$dataMap = $dataMapFactory->buildDataMap($className);
		return $dataMap->getTableName();
	}
}