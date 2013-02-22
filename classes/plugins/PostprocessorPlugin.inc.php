<?php

/**
 * @file classes/plugins/PostprocessorPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 * @class PostprocessorPlugin
 *
 * Abstract class for preprocessor plugins; these are invoked after an
 * entry is stored but before it is indexed.
 *
 */

// $Id$


import('classes.plugins.Plugin');

class PostprocessorPlugin extends Plugin {
	/**
	 * Constructor
	 */
	function PostprocessorPlugin() {
		parent::Plugin();
	}

	/**
	 * Determine whether or not this plugin is currently enabled.
	 * @return boolean
	 */
	function isEnabled() {
		return $this->getSetting('enabled');
	}

	function register($category, $path) {
		$result = parent::register($category, $path);
		if ($result) {
			HookRegistry::register('Harvester::postprocessRecord', array(&$this, '_postprocessRecord'));
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
	 * Wrapper around postprocessRecord to process a record via a hook.
	 * @param $hookName string
	 * @param $args array
	 * @retrn boolean Hook callback status
	 */
	function _postprocessRecord($hookName, $args) {
		$record =& $args[0];
		$archive =& $args[1];
		$schema =& $args[2];
		return $this->postprocessRecord($record, $archive, $schema);
	}

	/**
	 * Post-process a record.
	 * @param $record Record record
	 * @param $archive Archive Archive in which this record will be inserted
	 * @param $schema Schema The schema this record is described in
	 * @return boolean Hook processing status
	 */
	function postprocessRecord(&$record, &$archive, &$schema) {
		assert(false); // Subclasses to override
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
