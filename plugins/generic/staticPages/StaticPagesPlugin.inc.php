<?php

/**
 * @file plugins/generic/staticPages/StaticPagesPlugin.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesPlugin
 *
 * StaticPagesPlugin class
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class StaticPagesPlugin extends GenericPlugin {

	/**
	 * Return plugin name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.staticPages.displayName');
	}

	/**
	 * Return plugin description.
	 * @return string
	 */
	function getDescription() {
		$description = __('plugins.generic.staticPages.description');
		return $description;
	}

	/**
	 * Register the plugin, attaching to hooks as necessary.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				$this->import('StaticPagesDAO');
				if (checkPhpVersion('5.0.0')) {
					$staticPagesDAO = new StaticPagesDAO($this->getName());
				} else {
					$staticPagesDAO =& new StaticPagesDAO($this->getName());
				}
				$returner =& DAORegistry::registerDAO('StaticPagesDAO', $staticPagesDAO);

				HookRegistry::register('LoadHandler', array(&$this, 'callbackHandleContent'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Callback function to declare handler for processing static page requests.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callbackHandleContent($hookName, $args) {
		$templateMgr =& TemplateManager::getManager();

		$page =& $args[0];
		$op =& $args[1];

		if ( $page == 'pages' ) {
			define('STATIC_PAGES_PLUGIN_NAME', $this->getName()); // Kludge
			define('HANDLER_CLASS', 'StaticPagesHandler');
			$this->import('StaticPagesHandler');
			return true;
		}
		return false;
	}

	/**
	 * Display verbs for the management interface.
	 * @return array
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.staticPages.editAddContent'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Perform management functions.
	 * @param $verb string
	 * @param $args array
	 * @param $message string
	 * @return boolean
	 */
	function manage($verb, $args, &$message) {
		if (!parent::manage($verb, $args, $message)) return false;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		$templateMgr->assign('pagesPath', Request::url('pages', 'view', 'REPLACEME'));

		$pageCrumbs = array(
			array(
				Request::url('admin'),
				'user.role.siteAdmin'
			),
			array(
				Request::url('admin', 'plugins'),
				'admin.plugins'
			)

		);

		switch ($verb) {
			case 'settings':
				$staticPagesDAO =& DAORegistry::getDAO('StaticPagesDAO');
				$staticPages = $staticPagesDAO->getStaticPages();

				$templateMgr->assign('staticPages', $staticPages);
				$templateMgr->assign('pageHierarchy', $pageCrumbs);
				$templateMgr->display($this->getTemplatePath() . 'settings.tpl');
				return true;
			case 'edit':
			case 'add':
				$this->import('StaticPagesEditForm');

				$staticPageId = isset($args[0])?(int)$args[0]:null;
				$form = new StaticPagesEditForm($this, $staticPageId);

				if ($form->isLocaleResubmit()) {
					$form->readInputData();
					$form->addTinyMCE();
				} else {
					$form->initData();
				}

				$pageCrumbs[] = array(
					Request::url('admin', 'plugin', array('generic', $this->getName(), 'settings')),
					$this->getDisplayName(),
					true
				);
				$templateMgr->assign('pageHierarchy', $pageCrumbs);
				$form->display();
				return true;
			case 'save':
				$this->import('StaticPagesEditForm');

				$staticPageId = isset($args[0])?(int)$args[0]:null;
				$form = new StaticPagesEditForm($this, $staticPageId);

				if (Request::getUserVar('edit')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->save();
						$templateMgr->assign(array(
							'currentUrl' => Request::url(null, null, array($this->getCategory(), $this->getName(), 'settings')),
							'pageTitle' => 'plugins.generic.staticPages.displayName',
							'pageHierarchy' => $pageCrumbs,
							'message' => 'plugins.generic.staticPages.pageSaved',
							'backLink' => Request::url(null, null, array($this->getCategory(), $this->getName(), 'settings')),
							'backLinkLabel' => 'common.continue'
						));
						$templateMgr->display('common/message.tpl');
						exit;
					} else {
						$form->addTinyMCE();
						$form->display();
						exit;
					}
				}
				Request::redirect(null, 'admin', 'plugins');
				return false;
			case 'delete':
				$staticPageId = isset($args[0])?(int) $args[0]:null;
				$staticPagesDAO =& DAORegistry::getDAO('StaticPagesDAO');
				$staticPagesDAO->deleteStaticPageById($staticPageId);

				$templateMgr->assign(array(
					'currentUrl' => Request::url(null, null, array($this->getCategory(), $this->getName(), 'settings')),
					'pageTitle' => 'plugins.generic.staticPages.displayName',
					'message' => 'plugins.generic.staticPages.pageDeleted',
					'backLink' => Request::url(null, null, array($this->getCategory(), $this->getName(), 'settings')),
					'backLinkLabel' => 'common.continue'
				));

				$templateMgr->assign('pageHierarchy', $pageCrumbs);
				$templateMgr->display('common/message.tpl');
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}

	/**
	 * Get the filename path of the ADODB schema for this plugin.
	 * @return string
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/' . 'schema.xml';
	}
}

?>
