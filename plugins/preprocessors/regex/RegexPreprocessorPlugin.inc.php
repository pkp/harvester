<?php

/**
 *
 * @class RegexPreprocessorPlugin
 *
 * Based on OJS 2.0's LanguageMapPreprocessorPlugin.inc.php 
 *
 *
**/


import('classes.plugins.PreprocessorPlugin');

class RegexPreprocessorPlugin extends PreprocessorPlugin {
	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}


	/**
	 * Return the unique name of this plugin.
	 */
	function getName() {
		return 'RegexPreprocessorPlugin';
	}

	/**
	 * Get a display name for the plugin.
	 */
	function getDisplayName() {
		return __('plugins.preprocessors.regex.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.preprocessors.regex.description');
	}

	/**
	 * This callback implements the actual map and is called before an
	 * entry is inserted.
	 * @param $archive object
	 * @param $record object
	 * @param $field object
	 * @param $value string
	 * @param $attributes array
	 * @return boolean
	 */
	function preprocessEntry(&$archive, &$record, &$field, &$value, &$attributes) {
		/*
		 * Add your regular expressions, and any conditional logic, here. You can use
		 * methods like $archive->getArchiveId() and $field->getName() in your code.
		 * This example removes periods from the ends of subject elements:
		 *
		 * if ($field->getName() == 'subject') {
		 *    $value = preg_replace('/\.$/', '', $value);  
		 * }
		 */

		return false;
	}

	/**
	 * Return the set of management verbs supported by this plugin for the
	 * administration interface.
	 * @return array
	 */
	function getManagementVerbs() {
		if ($this->isEnabled()) return array(
			array('disable', __('common.disable'))
		);
		else return array(
			array('enable', __('common.enable'))
		);
	}

	/**
	 * Perform a management function on this plugin.
	 * @param $verb string
	 * @param $params array
	 */
	function manage($verb, $params) {
		switch ($verb) {
			case 'enable':
				$this->updateSetting('enabled', true);
				break;
			case 'disable':
				$this->updateSetting('enabled', false);
				break;
		}
		Request::redirect('admin', 'plugins');
	}

	/**
	 * Determine whether or not this plugin is currently enabled.
	 * @return boolean
	 */
	function isEnabled() {
		return $this->getSetting('enabled');
	}
}

?>
