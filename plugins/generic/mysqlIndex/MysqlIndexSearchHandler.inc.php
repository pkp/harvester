<?php

/**
 * @file MysqlIndexSearchHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.search
 * @class MysqlIndexSearchHandler
 *
 * Handle requests for search functions. 
 *
 */

// $Id$


import('classes.handler.Handler');

class MysqlIndexSearchHandler extends Handler {
	/**
	 * Constructor
	 */
	function MysqlIndexSearchHandler() {
		parent::Handler();
	}	
	
	/**
	 * Get the Zend Search Plugin object.
	 */
	function &getPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', MYSQL_PLUGIN_NAME);
		return $plugin;
	}


	/**
	 * Display site search page.
	 */
	function index() {
		$this->validate();
		list($crosswalks, $fields, $archives) = $this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$templateMgr->assign('archives', $archiveDao->getArchives());

		// Populate the select options for fields and crosswalks
		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$archiveIds = Request::getUserVar('archiveIds');
		if (empty($archiveIds)) $archiveIds = null;
		elseif (!is_array($archiveIds)) $archiveIds = array($archiveIds);

		$plugin =& $this->getPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'search.tpl');
	}

	/**
	 * Display search results.
	 */
	function results($args) {
		$this->validate();
		list($crosswalks, $fields, $archives) = $this->setupTemplate();

		$plugin =& $this->getPlugin();
		$plugin->import('Search');
		$rangeInfo = PKPHandler::getRangeInfo('search');

		$query = Request::getUserVar('query');
		$forwardParams = array();

		$keywords = array(
			'all' => Search::parseQuery($query),
			'crosswalk' => array(),
			'field' => array(),
			'date-from' => array(),
			'date-to' => array()
		);
		$dates = array(
			'field' => array(),
			'crosswalk' => array()
		);

		import('classes.field.Field');

		if (is_array($fields)) foreach ($fields as $field) switch ($field->getType()) {
			case FIELD_TYPE_DATE:
				$varName = 'field-' . $field->getFieldId();
				$dateFromName = "$varName-from";
				$dateToName = "$varName-to";

				$dateFrom = Request::getUserDateVar($dateFromName, 1, 1);
				$dateTo = Request::getUserDateVar($dateToName, 32, 12, null, 23, 59, 59);
				$dates['field'][$field->getFieldId()] = array($dateFrom, $dateTo);
				foreach (array('Month', 'Day', 'Year') as $datePart) {
					$forwardParams[$dateFromName . $datePart] = Request::getUserVar($dateFromName . $datePart);
					$forwardParams[$dateToName . $datePart] = Request::getUserVar($dateToName . $datePart);
				}
				break;
			case FIELD_TYPE_STRING:
			case FIELD_TYPE_SELECT:
				$varName = 'field-' . $field->getFieldId();
				$value = Request::getUserVar($varName);
				if (!empty($value)) {
					$forwardParams[$varName] = $value;
					if (is_array($value)) $value = '"' . implode('" OR "', $value) . '"';
					$keywords['field'][$field->getFieldId()] = Search::parseQuery($value);
				}
				break;
		}

		if (is_array($crosswalks)) foreach ($crosswalks as $crosswalk) switch ($crosswalk->getType()) {
			case FIELD_TYPE_DATE:
				$varName = 'crosswalk-' . $crosswalk->getCrosswalkId();
				$dateFromName = "$varName-from";
				$dateToName = "$varName-to";

				$dateFrom = Request::getUserDateVar($dateFromName, 1, 1);
				$dateTo = Request::getUserDateVar($dateToName, 32, 12, null, 23, 59, 59);
				$dates['crosswalk'][$crosswalk->getCrosswalkId()] = array($dateFrom, $dateTo);
				foreach (array('Month', 'Day', 'Year') as $datePart) {
					$forwardParams[$dateFromName . $datePart] = Request::getUserVar($dateFromName . $datePart);
					$forwardParams[$dateToName . $datePart] = Request::getUserVar($dateToName . $datePart);
				}
				break;
			case FIELD_TYPE_SELECT:
			case FIELD_TYPE_STRING:
			default:
				$varName = 'crosswalk-' . $crosswalk->getCrosswalkId();
				$value = Request::getUserVar($varName);
				if (!empty($value)) {
					$forwardParams[$varName] = $value;
					if (is_array($value)) $value = '"' . implode('" OR "', $value) . '"';
					$keywords['crosswalk'][$crosswalk->getCrosswalkId()] = Search::parseQuery($value);
				}
		}

		$archiveIds = array();
		if (empty($archiveIds)) $archiveIds = null;
		foreach ($archives as $archive) {
			if (is_object($archive)) $archiveIds[] = $archive->getArchiveId();
		}
		$results =& Search::retrieveResults($keywords, $dates, $archiveIds, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();

		// Give the results page access to the search parameters
		$templateMgr->assign('isAdvanced', Request::getUserVar('isAdvanced'));
		$templateMgr->assign('importance', Request::getUserVar('importance')); // Field importance

		foreach ($forwardParams as $key => $value) if ($value == '') unset($forwardParams[$key]);
		$templateMgr->assign('forwardParams', $forwardParams); // Field importance

		$templateMgr->assign_by_ref('results', $results);
		$plugin =& $this->getPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'results.tpl');
	}

	/**
	 * Display search results from a URL-based search.
	 */
	function byUrl($args) {
		$this->validate();
		list($crosswalks, $fields, $archives) = $this->setupTemplate();
		$plugin =& $this->getPlugin();
		$plugin->import('Search');
		$rangeInfo = PKPHandler::getRangeInfo('search');

		$query = Request::getUserVar('query');
		$forwardParams = array();

		$keywords = array(
			'all' => Search::parseQuery($query),
			'crosswalk' => array(),
			'field' => array(),
			'date-from' => array(),
			'date-to' => array()
		);
		$dates = array(
			'field' => array(),
			'crosswalk' => array()
		);

		import('classes.field.Field');

		if (is_array($fields)) foreach ($fields as $field) switch ($field->getType()) {
			case FIELD_TYPE_DATE:
				$varName = $field->getName();
				$dateFromName = "$varName-from";
				$dateToName = "$varName-to";

				$dateFrom = Request::getUserVar($dateFromName);
				$dateTo = Request::getUserVar($dateToName);
				$dates['field'][$field->getFieldId()] = array($dateFrom, $dateTo);
				foreach (array('Month', 'Day', 'Year') as $datePart) {
					$forwardParams[$dateFromName . $datePart] = Request::getUserVar($dateFromName . $datePart);
					$forwardParams[$dateToName . $datePart] = Request::getUserVar($dateToName . $datePart);
				}
				break;
			case FIELD_TYPE_STRING:
			case FIELD_TYPE_SELECT:
				$value = Request::getUserVar($field->getName());
				if (!empty($value)) {
					$forwardParams[$field->getName()] = $value;
					if (is_array($value)) $value = '"' . implode('" OR "', $value) . '"';
					$keywords['field'][$field->getFieldId()] = Search::parseQuery($value);
				}
				break;
		}

		if (is_array($crosswalks)) foreach ($crosswalks as $crosswalk) switch ($crosswalk->getType()) {
			case FIELD_TYPE_DATE:
				$varName = $crosswalk->getPublicCrosswalkId();
				$dateFromName = "$varName-from";
				$dateToName = "$varName-to";

				$dateFrom = Request::getUserVar($dateFromName);
				$dateTo = Request::getUserVar($dateToName);
				$dates['crosswalk'][$crosswalk->getCrosswalkId()] = array($dateFrom, $dateTo);
				foreach (array('Month', 'Day', 'Year') as $datePart) {
					$forwardParams[$dateFromName . $datePart] = Request::getUserVar($dateFromName . $datePart);
					$forwardParams[$dateToName . $datePart] = Request::getUserVar($dateToName . $datePart);
				}
				break;
			case FIELD_TYPE_SELECT:
			case FIELD_TYPE_STRING:
				$value = Request::getUserVar($crosswalk->getPublicCrosswalkId());
				if (!empty($value)) {
					$forwardParams[$crosswalk->getPublicCrosswalkId()] = $value;
					if (is_array($value)) $value = '"' . implode('" OR "', $value) . '"';
					$keywords['crosswalk'][$crosswalk->getCrosswalkId()] = Search::parseQuery($value);
				}
		}

		$archiveIds = array();
		if (empty($archives)) $archiveIds = null;
		foreach ($archives as $archive) {
			if (is_object($archive)) $archiveIds[] = $archive->getArchiveId();
		}

		$results =& Search::retrieveResults($keywords, $dates, $archiveIds, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();

		// Give the results page access to the search parameters
		$templateMgr->assign('isAdvanced', Request::getUserVar('isAdvanced'));
		$templateMgr->assign('importance', Request::getUserVar('importance')); // Field importance

		foreach ($forwardParams as $key => $value) if ($value == '') unset($forwardParams[$key]);
		$templateMgr->assign('forwardParams', $forwardParams);

		$templateMgr->assign_by_ref('results', $results);
		$plugin =& $this->getPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'results.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('search'), 'navigation.search'))
			);
		}

		// Assign prior values, if supplied, to form fields
		$templateMgr->assign('query', Request::getUserVar('query'));

		// Determine the list of schemas that must be supported by the search form
		$publicArchiveIds = Request::getUserVar('archive');
		if (!is_array($publicArchiveIds) && !empty($publicArchiveIds)) $publicArchiveIds = array($publicArchiveIds);
		$archiveIds = Request::getUserVar('archiveIds');
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$schemaList = array();
		$archives = array();

		$isAllSelected = (!is_array($archiveIds) || empty($archiveIds) || in_array('all', $archiveIds)) && empty($publicArchiveIds);

		if (!$isAllSelected) {
			if (is_array($archiveIds)) foreach ($archiveIds as $archiveId) {
				$archive =& $archiveDao->getArchive((int) $archiveId);
				if ($archive && ($schemaPluginName = $archive->getSchemaPluginName()) != '') {
					array_push($schemaList, $schemaPluginName);
					$archives[] =& $archive;
					unset($archive);
				}
			}
			if (is_array($publicArchiveIds)) foreach ($publicArchiveIds as $publicArchiveId) {
				$archive =& $archiveDao->getArchiveByPublicArchiveId($publicArchiveId);
				if ($archive && ($schemaPluginName = $archive->getSchemaPluginName()) != '') {
					array_push($schemaList, $schemaPluginName);
				}
				$archives[] =& $archive;
				unset($archive);
			}
		} else {
			$archives =& $archiveDao->getArchives();
			$archives =& $archives->toArray();
			foreach ($archives as $archive) {
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

		import('classes.field.Field');

		if (count($schemaList) == 1) {
			// There is a single schema being searched; make use of all its
			// fields.
			$schema = array_shift($schemaList);
			$fieldDao =& DAORegistry::getDAO('FieldDAO');
			$fields =& $fieldDao->getFields($schema->getSchemaId());
			$fields =& $fields->toArray();
			foreach ($fields as $field) switch ($field->getType()) {
				case FIELD_TYPE_DATE:
					$varName = 'field-' . $field->getFieldId();
					$dateFromName = "$varName-from";
					$dateToName = "$varName-to";

					$dateFrom = Request::getUserDateVar($dateFromName, 1, 1);
					if (empty($dateFrom)) $dateFrom = Request::getUserVar($dateFromName);
					$templateMgr->assign($dateFromName, $dateFrom);

					$dateTo = Request::getUserDateVar($dateToName, 32, 12, null, 23, 59, 59);
					if (empty($dateTo)) $dateTo = Request::getUserVar($dateToName);
					$templateMgr->assign($dateToName, $dateTo);
					break;
				case FIELD_TYPE_SELECT:
				case FIELD_TYPE_STRING:
					$varName = 'field-' . $field->getFieldId();
					$templateMgr->assign($varName, Request::getUserVar($varName));
					break;
			}
			$templateMgr->assign_by_ref('fields', $fields);
			$crosswalks = null; // Won't be using crosswalks
		} elseif (count($schemaList)>1) {
			// Multiple schema are being searched; use crosswalks.
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$crosswalks =& $crosswalkDao->getCrosswalksForSchemas($schemaList);
			$crosswalks =& $crosswalks->toArray();

			foreach ($crosswalks as $crosswalk) switch ($crosswalk->getType()) {
				case FIELD_TYPE_DATE:
					$varName = 'crosswalk-' . $crosswalk->getCrosswalkId();
					$dateFromName = "$varName-from";
					$dateToName = "$varName-to";

					$dateFrom = Request::getUserDateVar($dateFromName, 1, 1);
					if (empty($dateFrom)) $dateFrom = Request::getUserVar($dateFromName);
					$templateMgr->assign($dateFromName, $dateFrom);

					$dateTo = Request::getUserDateVar($dateToName, 32, 12, null, 23, 59, 59);
					if (empty($dateTo)) $dateTo = Request::getUserVar($dateToName);
					$templateMgr->assign($dateToName, $dateTo);
					break;
				case FIELD_TYPE_SELECT:
				case FIELD_TYPE_STRING:
				default:
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

		return array($crosswalks, $fields, $archives);
	}
}

?>
