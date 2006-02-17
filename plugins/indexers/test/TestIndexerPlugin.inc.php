<?php

/**
 * TestIndexerPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Test indexer plugin
 *
 * $Id$
 */

import('plugins.IndexerPlugin');

class TestIndexerPlugin extends IndexerPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'TestIndexerPlugin';
	}

	function getDisplayName() {
		return Locale::translate('plugins.indexers.test.name');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.indexers.test.description');
	}

	function displayAdminForm(&$indexer) {
		$indexerId = $indexer->getIndexerId();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('indexerId', $indexerId);

		foreach ($this->getAdminFormFields($indexer) as $field) switch ($field) {
			case "indexer-$indexerId-text":
				$templateMgr->assign('testIndexerText', $templateMgr->get_template_vars($field));
				break;
			default:
				fatalError("Unknown parameter \"$field\"!");
		}
		$templateMgr->display($this->getTemplatePath() . 'adminForm.tpl', null);
	}

	function getAdminFormFields(&$indexer) {
		$indexerId = $indexer->getIndexerId();
		return array(
			"indexer-$indexerId-text"
		);
	}

	function initAdminFormData(&$indexer, &$form) {
		$indexerId = $indexer->getIndexerId();
		foreach ($this->getAdminFormFields($indexer) as $field) switch ($field) {
			case "indexer-$indexerId-text":
				$form->setData($field, $val = $indexer->getSetting('text'));
				break;
			default:
				fatalError("Unknown parameter \"$field\"!");
		}
	}

	function saveAdminForm(&$indexer, &$form) {
		$indexerId = $indexer->getIndexerId();
		foreach ($this->getAdminFormFields($indexer) as $field) switch ($field) {
			case "indexer-$indexerId-text":
				$indexer->updateSetting('text', $form->getData($field));
				break;
			default:
				fatalError("Unknown parameter \"$field\"!");
		}
	}
}

?>
