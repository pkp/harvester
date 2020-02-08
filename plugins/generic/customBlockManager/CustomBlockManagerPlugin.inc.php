<?php
/**
 * @file plugins/generic/customBlockManager/CustomBlockManagerPlugin.inc.php
 *
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.customBlockManager
 * @class CustomBlockManagerPlugin
 *
 * Plugin to let site admins add and delete sidebar blocks
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class CustomBlockManagerPlugin extends GenericPlugin {

	/**
	 * Return plugin display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.customBlockManager.displayName');
	}

	/**
	 * Return plugin description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.customBlockManager.description');
	}

	/**
	 * Register this plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
			if ($this->getEnabled()) {
				HookRegistry::register('PluginRegistry::loadCategory', array(&$this, 'callbackLoadCategory'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Register as a block plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a block plugin, i.e. to
	 * have layout tasks performed on it.
	 * @param $hookName string
	 * @param $args array
	 */
	function callbackLoadCategory($hookName, $args) {
		$category =& $args[0];
		$plugins =& $args[1];

		if ($category != 'blocks') return false;

		$this->import('CustomBlockPlugin');

		$blocks = $this->getSetting('blocks');
		if (!is_array($blocks)) return false;

		$i = 0;
		foreach ($blocks as $block) {
			$blockPlugin = new CustomBlockPlugin($block, $this->getName());
			$plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath() . $i++] =& $blockPlugin;
			unset($blockPlugin);
		}
	}

	/**
	 * Display verbs for the management interface.
	 * @return array
	 */
	function getManagementVerbs($verbs = []) {
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.customBlockManager.settings'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Perform management functions
	 * @param $verb string
	 * @param $args array
	 * @param $message string
	 */
	function manage($verb, $args, &$message) {
		if (!parent::manage($verb, $args, $message)) return false;

		if ($verb != 'settings') {
			assert(false);
			return false;
		}

		$this->import('CustomBlockPlugin');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));

		$pageCrumbs = array(
			array(
				Request::url('admin'),
				'user.role.siteAdmin'
			),
			array(
				Request::url('admin', 'plugins'),
				__('admin.plugins'),
				true
			)
		);
		$templateMgr->assign('pageHierarchy', $pageCrumbs);

		$this->import('SettingsForm');
		$form = new SettingsForm($this);
		$form->readInputData();

		if (Request::getUserVar('addBlock')) {
			// Add a block
			$editData = true;
			$blocks = $form->getData('blocks');
			array_push($blocks, '');
			$form->_data['blocks'] = $blocks;

		} else if (($delBlock = Request::getUserVar('delBlock')) && count($delBlock) == 1) {
			// Delete an block
			$editData = true;
			list($delBlock) = array_keys($delBlock);
			$delBlock = (int) $delBlock;
			$blocks = $form->getData('blocks');
			if (isset($blocks[$delBlock]) && !empty($blocks[$delBlock])) {
				$deletedBlocks = explode(':', $form->getData('deletedBlocks'));
				array_push($deletedBlocks, $blocks[$delBlock]);
				$form->setData('deletedBlocks', join(':', $deletedBlocks));
			}
			array_splice($blocks, $delBlock, 1);
			$form->_data['blocks'] = $blocks;

		} else if (Request::getUserVar('save')) {
			$editData = true;
			$form->execute();

			// Enable the block plugin and place it in the right sidebar
			if ($form->validate()) {
				foreach ($form->getData('blocks') as $block) {
					$blockPlugin = new CustomBlockPlugin($block, $this->getName());

					// Default the block to being enabled
					if (!is_bool($blockPlugin->getEnabled())) {
						$blockPlugin->setEnabled(true);
					}

					// Default the block to the right sidebar
					if (!is_numeric($blockPlugin->getBlockContext())) {
						$blockPlugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
					}
				}
			}

		} else {
			$form->initData();
		}

		if (!isset($editData) && $form->validate()) {
			$form->execute();
			$form->display();
			exit;
		} else {
			$form->display();
			exit;
		}
		return true;
	}
}

?>
