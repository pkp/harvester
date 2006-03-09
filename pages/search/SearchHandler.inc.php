<?php

/**
 * SearchHandler.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.search
 *
 * Handle requests for search functions. 
 *
 * $Id$
 */

class SearchHandler extends Handler {

	/**
	 * Display site search page.
	 */
	function index() {
		SearchHandler::validate();
		SearchHandler::setupTemplate();
			
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.search');

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$templateMgr->assign('archives', $archiveDao->getArchives());

		$templateMgr->display('search/index.tpl');
	}

	/**
	 * Display search results.
	 */
	function results($args) {
		SearchHandler::validate();
		list($crosswalks, $fields) = SearchHandler::setupTemplate();
		import('search.Search');
		$rangeInfo = Handler::getRangeInfo('search');

		// Get the archives we're searching.
		$archiveIds = Request::getUserVar('archiveIds');
		if (!is_array($archiveIds)) {
			if (empty($archiveIds)) $archiveIds = null;
			else $archiveIds = array($archiveIds);
		}
		if ($archiveIds !== null && in_array('all', $archiveIds)) {
			$archiveIds = null;
		}

		$query = Request::getUserVar('query');

		$keywords = array(
			'all' => Search::parseQuery($query),
			'crosswalk' => array(),
			'field' => array()
		);

		if (is_array($fields)) foreach ($fields as $field) {
			$value = Request::getUserVar('field-' . $field->getFieldId());
			if (!empty($value)) {
				$keywords['field'][$field->getFieldId()] = Search::parseQuery($value);
			}
		}

		if (is_array($crosswalks)) foreach ($crosswalks as $crosswalk) {
			$value = Request::getUserVar('crosswalk-' . $crosswalk->getCrosswalkId());
			if (!empty($value)) {
				$keywords['crosswalk'][$crosswalk->getCrosswalkId()] = Search::parseQuery($value);
			}
		}

		$results = &Search::retrieveResults($keywords, $archiveIds, $rangeInfo);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.search');

		// Give the results page access to the search parameters
		$templateMgr->assign('isAdvanced', Request::getUserVar('isAdvanced'));
		$templateMgr->assign('query', $query);
		$templateMgr->assign('archiveIds', Request::getUserVar('archiveIds'));

		$templateMgr->assign_by_ref('results', $results);
		$templateMgr->display('search/results.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		$templateMgr = &TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('search'), 'navigation.search'))
			);
		}

		// Assign prior values, if supplied, to form fields
		$templateMgr->assign('query', Request::getUserVar('query'));

		// Determine the list of schemas that must be supported by the search form
		$archiveIds = Request::getUserVar('archiveIds');
		$isAllSelected = false;
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$schemaList = array();
		if (is_array($archiveIds)) foreach ($archiveIds as $archiveId) {
			if ($archiveId === 'all') {
				$isAllSelected = true;
				break;
			}
			$archive =& $archiveDao->getArchive((int) $archiveId);
			if ($archive && ($schemaPluginName = $archive->getSchemaPluginName()) != '') {
				array_push($schemaList, $schemaPluginName);
			}
			unset($archive);
		}
		if ($isAllSelected) {
			$archives =& $archiveDao->getArchives();
			while ($archive =& $archives->next()) {
				if ($archive->getRecordCount() > 0 && ($schemaPluginName = $archive->getSchemaPluginName()) != '') {
					array_push($schemaList, $schemaPluginName);
				}
				unset($archive);
			}
		}
		$schemaList = array_map(
			array(DAORegistry::getDAO('SchemaDAO'), 'buildSchema'),
			array_unique($schemaList)
		);

		if (count($schemaList) == 1) {
			// There is a single schema being searched; make use of all its
			// fields.
			$schema = array_shift($schemaList);
			$fieldDao =& DAORegistry::getDAO('FieldDAO');
			$fields =& $fieldDao->getFields($schema->getSchemaId());
			$fields =& $fields->toArray();
			foreach ($fields as $field) {
				$varName = 'field-' . $field->getFieldId();
				$templateMgr->assign($varName, Request::getUserVar($varName));
			}
			$templateMgr->assign_by_ref('fields', $fields);
			$crosswalks = null; // Won't be using crosswalks
		} elseif (count($schemaList)>1) {
			// Multiple schema are being searched; use crosswalks.
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$crosswalks =& $crosswalkDao->getCrosswalksForSchemas($schemaList);
			$crosswalks =& $crosswalks->toArray();
			foreach ($crosswalks as $crosswalk) {
				$varName = 'crosswalk-' . $crosswalk->getCrosswalkId();
				$templateMgr->assign($varName, Request::getUserVar($varName));
			}
			$templateMgr->assign_by_ref('crosswalks', $crosswalks);
			$fields = null; // Won't be using fields
		} else {
			$fields = null;
			$crosswalks = null;
		}

		$templateMgr->assign('archiveIds', Request::getUserVar('archiveIds'));

		return array($crosswalks, $fields);
	}
}

?>
