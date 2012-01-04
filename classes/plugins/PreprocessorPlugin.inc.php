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
	function PreprocessorPlugin() {
		parent::Plugin();
	}

	function register($category, $path) {
		$result = parent::register($category, $path);
		if ($result) {
			HookRegistry::register('Harvester::insertEntry', array(&$this, '_preprocessEntry'));
		}
		return $result;
	}

	function _preprocessEntry($hookName, $args) {
		$archive =& $args[0];
		$record =& $args[1];
		$field =& $args[2];
		$value =& $args[3];
		$attributes =& $args[4];
		return $this->preprocessEntry($archive, $record, $field, $value, $attributes);
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

	function preprocessEntry(&$archive, &$record, &$field, &$value, &$attributes) {
		fatalError('ABSTRACT CLASS');
	}
}

?>
