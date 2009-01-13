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

	function luceneEscape($str) {
		return str_replace(array('+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '\\'), array('\\+', '\\-', '\\&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\"', '\\~', '\\*', '\\?', '\\:', '\\\\'), $str);
	}

	/**
	 * Display search results.
	 */
	function searchResults() {
		ZendSearchHandler::setupTemplate();
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
		$isUsingSolr = $plugin->isUsingSolr();

		if ($isUsingSolr) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $plugin->getSetting('solrUrl') . '/select');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			$query = '';
		} else {
			$index =& $plugin->getIndex();
			$query = new Zend_Search_Lucene_Search_Query_Boolean();
		}

		$q = Request::getUserVar('q');
		if (!empty($q)) {
			if ($isUsingSolr) {
				$query .= 'text:"' . ZendSearchHandler::luceneEscape($q) . '" ';
			} else {
				$query->addSubquery(Zend_Search_Lucene_Search_QueryParser::parse($q));
			}
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
					if (!empty($term)) {
						if ($isUsingSolr) {
							$query .= $symbolic . ':"' . ZendSearchHandler::luceneEscape($term) . '" ';
						} else {
							$query->addSubquery(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($term, $symbolic)), true);
						}
					}
					break;
				case SEARCH_FORM_ELEMENT_TYPE_DATE:
					$from = Request::getUserDateVar($symbolic . '-from');
					$to = Request::getUserDateVar($symbolic . '-to');
					if (!empty($from) && !empty($to)) {
						if ($isUsingSolr) {
							$query .= $symbolic . ':[' . strftime('%y%m%d', $from) . ' to ' . strftime('%y%m%d', $to) . '] ';
						} else {
							$fromTerm = new Zend_Search_Lucene_Index_Term($from, $symbolic);
							$toTerm = new Zend_Search_Lucene_Index_Term($to, $symbolic);
							$query->addSubquery(new Zend_Search_Lucene_Search_Query_Range($fromTerm, $toTerm, true), true);
						}
					}
					break;
				default:
					fatalError('Unknown element type!');
			}
			unset($searchFormElement);
		}

		$rangeInfo =& PKPHandler::getRangeInfo('results');

		if ($isUsingSolr) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'q=' . trim(urlencode($query)));
			$data = curl_exec($ch);
			$xmlParser = new XMLParser();
			$result = null;
			@$result =& $xmlParser->parseTextStruct($data, array("str"));
			$recordIds = array();
			if ($result) foreach ($result as $nodeSet) foreach ($nodeSet as $node) {
				if (isset($node['attributes']['name']) && $node['attributes']['name'] == 'id') {
					$recordIds[] = $node['value'];
				}
			}
			$plugin->import('SolrResultIterator');
			$resultsIterator =& SolrResultIterator::fromRangeInfo($recordIds, $rangeInfo);
			unset($recordIds);
		} else {
			$resultsArray = $index->find($query);
			$plugin->import('ZendSearchResultIterator');
			$resultsIterator =& ZendSearchResultIterator::fromRangeInfo($resultsArray, $rangeInfo);
			unset($resultsArray);
		}

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
		parent::setupTemplate();
		parent::validate();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierachy', array(
			array(Request::url('search'), 'navigation.search')
		));
	}
}

?>
