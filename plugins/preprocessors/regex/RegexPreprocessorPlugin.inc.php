<?php

/**
 * @class RegexPreprocessorPlugin
 *
 */


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
	 * @param $record object
	 * @param $archive object
	 * @param $schema object
	 * @return boolean
	 */
	function preprocessRecord(&$record, &$archive, &$schema) {
		/**
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
}

?>
