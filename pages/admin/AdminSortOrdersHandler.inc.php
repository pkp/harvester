<?php

/**
 * @file pages/admin/AdminSortOrdersHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminSortOrdersHandler
 *
 * Handle requests for sorting management in site administration. 
 *
 */


import('pages.admin.AdminHandler');

class AdminSortOrdersHandler extends AdminHandler {
	/**
	 * Display a list of the sort orders configured on the site.
	 */
	function sortOrders($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);

		$rangeInfo = $this->getRangeInfo($request, 'sortOrders');

		$sortOrderDao = DAORegistry::getDAO('SortOrderDAO');
		$sortOrders = $sortOrderDao->getSortOrders($rangeInfo);

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign_by_ref('sortOrders', $sortOrders);
		if ($rangeInfo) $templateMgr->assign('sortOrderPage', $rangeInfo->getPage());
		$templateMgr->display('admin/sortOrders.tpl');
	}

	/**
	 * Display form to create a new sort order.
	 */
	function createSortOrder($args, &$request) {
		$this->editSortOrder($args, $request);
	}

	/**
	 * Display form to create/edit a sort order.
	 * @param $args array optional, if set the first parameter is the ID of the sort order to edit
	 */
	function editSortOrder($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		import('classes.admin.form.SortOrderForm');

		$sortOrderForm = new SortOrderForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		$sortOrderForm->initData();
		$sortOrderForm->display();
	}

	/**
	 * Save changes to a sort order's settings.
	 */
	function updateSortOrder($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);
		
		import('classes.admin.form.SortOrderForm');

		$sortOrderId = (int) $request->getUserVar('sortOrderId');

		$sortOrderForm = new SortOrderForm($sortOrderId);
		$sortOrderForm->initData();
		$sortOrderForm->readInputData();

		if ($sortOrderForm->validate()) {
			$sortOrderForm->execute();
			$request->redirect('admin', 'sortOrders');
		} else {
			$this->setupTemplate($request, true);
			$sortOrderForm->display();
		}
	}

	/**
	 * Delete a sort order.
	 * @param $args array first parameter is the ID of the sort order to delete
	 */
	function deleteSortOrder($args, &$request) {
		$this->validate();

		$sortOrderDao =& DAORegistry::getDAO('SortOrderDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		if (isset($args) && isset($args[0])) {
			$sortOrderId = $args[0];
			$sortOrderDao->deleteSortOrderById($sortOrderId);
		}

		$request->redirect('admin', 'sortOrders', null, array('sortOrderPage' => $request->getUserVar('sortOrderPage')));
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager($request);
		$pageHierarchy = array(
			array($request->url('admin'), 'admin.siteAdmin')
		);
		if ($subclass) {
			$pageHierarchy[] = array($request->url('admin', 'sortOrders'), 'admin.sortOrders');
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
