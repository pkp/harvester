<?php

/**
 * @file pages/rt/RTHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rt
 * @class RTHandler
 *
 * Handle Reading Tools requests. 
 *
 */


// $Id$

import('classes.rt.harvester2.RTDAO');
import('classes.handler.Handler');

class RTHandler extends Handler {
	/** archive associated with this request **/
	var $archive;
	
	/** record associated with this request **/
	var $record; 
	
	function context($args) {
		$recordId = array_shift($args);
		$contextId = array_shift($args);

		$this->validate($recordId);
		$archive =& $this->archive;
		$record =& $this->record;
		$this->setupTemplate();

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$context =& $rtDao->getContext($contextId);

		if (!$context) {
			Request::redirect('index');
		}

		// Deal with the post and URL parameters for each search
		// so that the client browser can properly submit the forms
		// with a minimum of client-side processing.
		$searches = array();
		// Some searches use parameters other than the "default" for
		// the search (i.e. keywords, author name, etc). If additional
		// parameters are used, they should be displayed as part of the
		// form for ALL searches in that context.
		$searchParams = array();
		foreach ($context->getSearches() as $search) {
			$params = array();
			$searchParams += $this->getParameterNames($search->getSearchUrl());
			if ($search->getSearchPost()) {
				$searchParams += $this->getParameterNames($search->getSearchPost());
				$postParams = explode('&', $search->getSearchPost());
				foreach ($postParams as $param) {
					// Split name and value from each parameter
					$nameValue = explode('=', $param);
					if (!isset($nameValue[0])) break;

					$name = trim($nameValue[0]);
					$value = trim(isset($nameValue[1])?$nameValue[1]:'');
					if (!empty($name)) $params[] = array('name' => $name, 'value' => $value);
				}
			}

			$search->postParams = $params;
			$searches[] = $search;
		}

		// Remove duplicate extra form elements and get their values
		$searchParams = array_unique($searchParams);
		$searchValues = array();

		foreach ($searchParams as $key => $param) switch ($param) {
			case 'author':
				$searchValues[$param] = $record->getAuthorString();
				break;
			case 'coverageGeo':
				// We don't currently support geo coverage info
				$searchValues[$param] = '';
				break;
			case 'title':
				$searchValues[$param] = $record->getTitle();
				break;
			default:
				// UNKNOWN parameter! Remove it from the list.
				unset($searchParams[$key]);
				break;
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('context', $context);
		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->assign_by_ref('searches', $searches);
		$templateMgr->assign('searchParams', $searchParams);
		$templateMgr->assign('searchValues', $searchValues);
		$templateMgr->assign('defineTerm', Request::getUserVar('defineTerm'));
		$templateMgr->display('rt/context.tpl');
	}

	function getParameterNames($value) {
		$matches = null;
		String::regexp_match_all('/\{\$([a-zA-Z0-9]+)\}/', $value, $matches);
		// Remove the entire string from the matches list
		return $matches[1];
	}

	function validate($recordId) {
		$returner = array();

		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$record =& $recordDao->getRecord($recordId);

		if ($record) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$archive =& $archiveDao->getArchive($record->getArchiveId());

			if ($archive && $archive->getEnabled()) {
				$this->archive =& $archive;
				$this->record =& $record;
				return true;
			}
		}
		Request::redirect('index');
	}
}

?>
