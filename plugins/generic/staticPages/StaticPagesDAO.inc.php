<?php
/**
 * @file plugins/generic/staticPages/StaticPagesDAO.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesDAO
 *
 * Operations for retrieving and modifying StaticPages objects.
 *
 */
import('lib.pkp.classes.db.DAO');

class StaticPagesDAO extends DAO {
	/** @var $parentPluginName Name of parent plugin */
	var $parentPluginName;

	/**
	 * Constructor
	 * @param $parentPluginName string
	 */
	function StaticPagesDAO($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
		parent::DAO();
	}

	/**
	 * Retrieve a static page by id
	 * @param $staticPageId int
	 * @return object
	 */
	function getStaticPage($staticPageId) {
		$result =& $this->retrieve(
			'SELECT * FROM static_pages WHERE static_page_id = ?', $staticPageId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnStaticPageFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all static pages
	 * @param $rangeInfo DBResultRange optional
	 * @return object
	 */
	function &getStaticPages($rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM static_pages', false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnStaticPageFromRow');
		return $returner;
	}

	/**
	 * Retrieve a static page by path
	 * @param $path string
	 * @return object
	 */
	function getStaticPageByPath($path) {
		$result =& $this->retrieve(
			'SELECT * FROM static_pages WHERE path = ?', array($path)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnStaticPageFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Add a new static page
	 * @param $staticPage object
	 * @return int
	 */
	function insertStaticPage(&$staticPage) {
		$this->update(
			'INSERT INTO static_pages
				(path)
				VALUES
				(?)',
			array(
				$staticPage->getPath()
			)
		);

		$staticPage->setId($this->getInsertStaticPageId());
		$this->updateLocaleFields($staticPage);

		return $staticPage->getId();
	}

	/**
	 * Update an existing static page
	 * @param $staticPage object
	 * @return boolean
	 */
	function updateStaticPage(&$staticPage) {
		$returner = $this->update(
			'UPDATE static_pages
				SET path = ?
				WHERE static_page_id = ?',
				array(
					$staticPage->getPath(),
					$staticPage->getId()
					)
			);
		$this->updateLocaleFields($staticPage);
		return $returner;
	}

	/**
	 * Delete an existing static page by id
	 * @param $staticPageId int
	 * @return boolean
	 */
	function deleteStaticPageById($staticPageId) {
		$returner = $this->update(
			'DELETE FROM static_pages WHERE static_page_id = ?', $staticPageId
		);
		return $this->update(
			'DELETE FROM static_page_settings WHERE static_page_id = ?', $staticPageId
		);
	}

	/**
	 * Return a StaticPage object corresponding to a row
	 * @param $row array
	 * @return object
	 */
	function &_returnStaticPageFromRow(&$row) {
		$staticPagesPlugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		$staticPagesPlugin->import('StaticPage');

		$staticPage = new StaticPage();
		$staticPage->setId($row['static_page_id']);
		$staticPage->setPath($row['path']);

		$this->getDataObjectSettings('static_page_settings', 'static_page_id', $row['static_page_id'], $staticPage);
		return $staticPage;
	}

	/**
	 * Return id of newly added static page
	 * @return int
	 */
	function getInsertStaticPageId() {
		return $this->getInsertId('static_pages', 'static_page_id');
	}

	/**
	 * Get field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'content');
	}

	/**
	 * Update the localized data for this object
	 * @param $staticPage object
	 */
	function updateLocaleFields(&$staticPage) {
		$this->updateDataObjectSettings('static_page_settings', $staticPage, array(
			'static_page_id' => $staticPage->getId()
		));
	}

	/**
	 * Check if a duplicate path exists
	 * @param $path string
	 * @param $staticPageId	int
	 * @return boolean
	 */
	function duplicatePathExists ($path, $staticPageId = null) {
		$params = array($path);
		if (isset($staticPageId)) $params[] = $staticPageId;

		$result = $this->retrieve(
			'SELECT *
				FROM static_pages
				WHERE path = ?' .
				(isset($staticPageId)?' AND NOT (static_page_id = ?)':''),
				$params
			);

		if($result->RecordCount() == 0) {
			// no duplicate exists
			$returner = false;
		} else {
			$returner = true;
		}
		return $returner;
	}
}
?>
