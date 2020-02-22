<?php

/**
 * @file SearchForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package rt.ohs.form
 * @class SearchForm
 *
 * Form to change metadata information for an RT search.
 *
 * $Id$
 */

import('lib.pkp.classes.form.Form');

class SearchForm extends Form {

	/** @var int the ID of the search */
	var $searchId;

	/** @var Context current search */
	var $search;

	/** @var int ID of the context */
	var $contextId;

	/** @var int ID of the version */
	var $versionId;

	/** @var int ID of the archive */
	var $archiveId;

	/**
	 * Constructor.
	 */
	function SearchForm($searchId, $contextId, $versionId, $archiveId) {
		parent::Form('rtadmin/search.tpl');

		$this->addCheck(new FormValidatorPost($this));

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$this->search =& $rtDao->getSearch($searchId);

		$this->contextId = $contextId;
		$this->versionId = $versionId;
		$this->archiveId = $archiveId;

		if (isset($this->search)) {
			$this->searchId = $searchId;
		}
	}

	/**
	 * Initialize form data from current search.
	 */
	function initData() {
		if (isset($this->search)) {
			$search =& $this->search;
			$this->_data = array(
				'url' => $search->getUrl(),
				'title' => $search->getTitle(),
				'searchUrl' => $search->getSearchUrl(),
				'description' => $search->getDescription(),
				'searchPost' => $search->getSearchPost(),
				'order' => $search->getOrder()
			);
		} else {
			$this->_data = array();
		}
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('contextId', $this->contextId);
		$templateMgr->assign('versionId', $this->versionId);
		$templateMgr->assign('archiveId', $this->archiveId);

		if (isset($this->search)) {
			$templateMgr->assign_by_ref('search', $this->search);
			$templateMgr->assign('searchId', $this->searchId);
		}

		parent::display();
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'url',
				'title',
				'order',
				'description',
				'searchUrl',
				'searchPost'
			)
		);
	}

	/**
	 * Save changes to search.
	 * @return int the search ID
	 */
	function execute() {
		$rtDao =& DAORegistry::getDAO('RTDAO');

		$search = $this->search;
		if (!isset($search)) {
			$search = new RTSearch();
			$search->setContextId($this->contextId);
		}

		$search->setTitle($this->getData('title'));
		$search->setUrl($this->getData('url'));
		$search->setSearchUrl($this->getData('searchUrl'));
		$search->setSearchPost($this->getData('searchPost'));
		$search->setDescription($this->getData('description'));
		if (!isset($this->search)) $search->setOrder(0);

		if (isset($this->search)) {
			$rtDao->updateSearch($search);
		} else {
			$rtDao->insertSearch($search);
			$this->searchId = $search->getSearchId();
		}

		return $this->searchId;
	}

}

?>
