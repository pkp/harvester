<?php

/**
 * @file ZendSearchHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SendSearchHandler
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Handle requests for search functions
 */

// $Id$


import('core.PKPHandler');

class ZendSearchHandler extends PKPHandler {
	/**
	 * Display search form
	 */
	function index() {
		ZendSearchHandler::setupTemplate();
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');

		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$searchFormElements =& $searchFormElementDao->getSearchFormElements();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('searchFormElements', $searchFormElements);
		$templateMgr->display($plugin->getTemplatePath() . 'search.tpl');
	}

	/**
	 * Display search results.
	 */
	function searchResults() {
		ZendSearchHandler::setupTemplate();
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
		$index =& $plugin->getIndex();

		$query = new Zend_Search_Lucene_Search_Query_Boolean();

		$q = Request::getUserVar('q');
		if (!empty($q)) {
			$query->addSubquery(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($q)), true);
		}

		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$searchFormElements =& $searchFormElementDao->getSearchFormElements();
		while ($searchFormElement =& $searchFormElements->next()) {
			$searchFormElementId = $searchFormElement->getSearchFormElementId();
			$symbolic = $searchFormElement->getSymbolic();
			switch ($searchFormElement->getType()) {
				case SEARCH_FORM_ELEMENT_TYPE_SELECT:
				case SEARCH_FORM_ELEMENT_TYPE_STRING:
					$term = Request::getUserVar($symbolic);
					if (!empty($term)) $query->addSubquery(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($term, $symbolic)), true);
					break;
				case SEARCH_FORM_ELEMENT_TYPE_DATE:
					$from = Request::getUserDateVar($symbolic . '-from');
					$to = Request::getUserDateVar($symbolic . '-to');
					if (!empty($from) && !empty($to)) {
						$fromTerm = new Zend_Search_Lucene_Index_Term($from, $symbolic);
						$toTerm = new Zend_Search_Lucene_Index_Term($to, $symbolic);
						$query->addSubquery(new Zend_Search_Lucene_Search_Query_Range($fromTerm, $toTerm, true), true);
					}
					break;
				default:
					fatalError('Unknown element type!');
			}
			unset($searchFormElement);
		}

		$resultsArray = $index->find($query);
		$rangeInfo =& PKPHandler::getRangeInfo('results');

		import('core.ArrayItemIterator');
		$resultsIterator =& ArrayItemIterator::fromRangeInfo($resultsArray, $rangeInfo);
		unset($resultsArray);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('recordDao', DAORegistry::getDAO('RecordDAO'));
		$templateMgr->assign_by_ref('results', $resultsIterator);
		$templateMgr->assign_by_ref('q', $q);
		$templateMgr->display($plugin->getTemplatePath() . 'results.tpl');
	}

	/**
	 * Setup common template variables.
	 */
	function setupTemplate() {
		parent::validate();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierachy', array(
			array(Request::url('search'), 'navigation.search')
		));
	}
}

?>
