<?php

/**
 * @file classes/plugins/PreprocessorPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 * @class PreprocessorPlugin
 *
 * Abstract class for preprocessor plugins
 */

// $Id$


import('classes.plugins.Plugin');

class PreprocessorPlugin extends Plugin {
	/**
	 * Constructor
	 */
	function PreprocessorPlugin() {
		parent::Plugin();
	}

	/**
	 * Determine whether or not this plugin is currently enabled.
	 * @return boolean
	 */
	function isEnabled() {
		return $this->getSetting('enabled');
	}

	/**
	 * Register the plugin.
	 * @see Plugin::register
	 */
	function register($category, $path) {
		$result = parent::register($category, $path);
		if ($result && $this->isEnabled()) {
			HookRegistry::register('Harvester::preprocessRecord', array(&$this, '_preprocessRecord'));
		}
		return $result;
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
	 * Hook handler for record preprocessing (wraps around preprocessRecord)
	 * @param $hookName string
	 * @param $args array
	 * @return boolean Hook callback status
	 */
	function _preprocessRecord($hookName, $args) {
		$record =& $args[0];
		$archive =& $args[1];
		$schema =& $args[2];
		return $this->preprocessRecord($record, $archive, $schema);
	}

	/**
	 * Preprocess a record.
	 * @param $record Record Record object ready for insertion
	 * @param $archive Archive Archive in which this record will be inserted
	 * @param $schema Schema The schema this record is described in
	 * @return boolean Hook callback status
	 */
	function preprocessRecord(&$record, &$archive, &$schema) {
		assert(false); // To be overridden by subclasses.
	}

	/**
	 * Get the symbolic name of this plugin. Should be unique within
	 * the category.
	 */
	function getName() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		fatalError('ABSTRACT CLASS');
	}
}

?>
