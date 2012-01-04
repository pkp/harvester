<?php

/**
 * @file classes/admin/form/LayoutForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LayoutForm
 * @ingroup admin_form_layout
 *
 * @brief Form for layout setup.
 */

//$Id$


import('lib.pkp.classes.form.Form');

class LayoutForm extends Form {
	/**
	 * Constructor.
	 */
	function LayoutForm() {
		parent::form('admin/layout.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize data from current settings.
	 */
	function initData() {
		$site =& Request::getSite();
		$this->data = array(
			'theme' => $site->getSetting('theme')
		);
	}

	/**
	 * Read user input.
	 */
	function readInputData() {
		$this->readUserVars(array('theme'));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$site =& Request::getSite();

		$allThemes =& PluginRegistry::loadCategory('themes');
		$siteThemes = array();
		foreach ($allThemes as $key => $junk) {
			$plugin =& $allThemes[$key]; // by ref
			$siteThemes[basename($plugin->getPluginPath())] =& $plugin;
			unset($plugin);
		}

		// Ensure upload file settings are reloaded when the form is displayed.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
		$templateMgr->assign(array(
			'siteThemes' => $siteThemes
		));

		// Make lists of the sidebar blocks available.
		$templateMgr->initialize();
		$leftBlockPlugins = $disabledBlockPlugins = $rightBlockPlugins = array();
		$plugins =& PluginRegistry::getPlugins('blocks');
		foreach ($plugins as $key => $junk) {
			if (!$plugins[$key]->getEnabled() || $plugins[$key]->getBlockContext() == '') {
				if (count(array_intersect($plugins[$key]->getSupportedContexts(), array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR))) > 0) $disabledBlockPlugins[] =& $plugins[$key];
			} else switch ($plugins[$key]->getBlockContext()) {
				case BLOCK_CONTEXT_LEFT_SIDEBAR:
					$leftBlockPlugins[] =& $plugins[$key];
					break;
				case BLOCK_CONTEXT_RIGHT_SIDEBAR:
					$rightBlockPlugins[] =& $plugins[$key];
					break;
			}
		}
		$templateMgr->assign(array(
			'disabledBlockPlugins' => &$disabledBlockPlugins,
			'leftBlockPlugins' => &$leftBlockPlugins,
			'rightBlockPlugins' => &$rightBlockPlugins
		));

		parent::display();
	}

	/**
	 * Save the page of settings.
	 */
	function execute() {
		// Save the block plugin layout settings.
		$blockVars = array('blockSelectLeft', 'blockUnselected', 'blockSelectRight');
		foreach ($blockVars as $varName) {
			$$varName = split(' ', Request::getUserVar($varName));
		}

		$plugins =& PluginRegistry::loadCategory('blocks');
		foreach ($plugins as $key => $junk) {
			$plugin =& $plugins[$key]; // Ref hack
			$plugin->setEnabled(!in_array($plugin->getName(), $blockUnselected));
			if (in_array($plugin->getName(), $blockSelectLeft)) {
				$plugin->setBlockContext(BLOCK_CONTEXT_LEFT_SIDEBAR);
				$plugin->setSeq(array_search($key, $blockSelectLeft));
			} else if (in_array($plugin->getName(), $blockSelectRight)) {
				$plugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
				$plugin->setSeq(array_search($key, $blockSelectRight));
			}
			unset($plugin);
		}

		$site =& Request::getSite();
		$siteSettingsDao =& DAORegistry::getDAO('SiteSettingsDAO');

		$settings = array('theme');

		foreach ($this->_data as $name => $value) {
			if (isset($settings[$name])) {
				$isLocalized = in_array($name, $this->getLocaleFieldNames());
				$siteSettingsDao->updateSetting(
					$name,
					$value,
					$this->settings[$name],
					$isLocalized
				);
			}
		}
	}
}

?>
