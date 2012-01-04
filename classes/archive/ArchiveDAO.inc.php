<?php

/**
 * @file ArchiveDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package archive
 * @class ArchiveDAO
 *
 * @brief Class for Archive DAO.
 * Operations for retrieving and modifying Archive objects.
 *
 */

// $Id$


import ('classes.archive.Archive');

class ArchiveDAO extends DAO {
	/**
	 * Constructor.
	 */
	function ArchiveDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve an archive by ID.
	 * @param $archiveId int
	 * @param $onlyEnabled boolean
	 * @return Archive
	 */
	function &getArchive($archiveId, $onlyEnabled = true) {
		$result =& $this->retrieve(
			'SELECT * FROM archives WHERE archive_id = ?' . ($onlyEnabled?' AND enabled = 1':''), (int) $archiveId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnArchiveFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an archive by public archive ID.
	 * @param $publicArchiveId string
	 * @param $onlyEnabled boolean
	 * @return Archive
	 */
	function &getArchiveByPublicArchiveId($publicArchiveId, $onlyEnabled = true) {
		$result =& $this->retrieve(
			'SELECT	*
			FROM	archives
			WHERE	public_archive_id = ?' .
			($onlyEnabled?' AND enabled = 1':''),
			array($publicArchiveId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnArchiveFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an archive by title.
	 * @param $title string
	 * @param $onlyEnabled boolean
	 * @return Archive
	 */
	function &getArchiveByTitle($title, $onlyEnabled = true) {
		$result =& $this->retrieve(
			'SELECT	*
			FROM	archives
			WHERE	title = ?' .
			($onlyEnabled?' AND enabled = 1':''),
			array($title)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnArchiveFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a Archive object from a row.
	 * @param $row array
	 * @return Archive
	 */
	function &_returnArchiveFromRow(&$row) {
		$archive = new Archive();
		$archive->setArchiveId($row['archive_id']);
		$archive->setUserId($row['user_id']);
		$archive->setPublicArchiveId($row['public_archive_id']);
		$archive->setTitle($row['title']);
		$archive->setEnabled($row['enabled']);
		$archive->setUrl($row['url']);
		$archive->setHarvesterPluginName($row['harvester_plugin']);
		$archive->setSchemaPluginName($row['schema_plugin']);

		HookRegistry::call('ArchiveDAO::_returnArchiveFromRow', array(&$archive, &$row));

		return $archive;
	}

	/**
	 * Insert a new archive.
	 * @param $archive Archive
	 */	
	function insertArchive(&$archive) {
		$this->update(
			'INSERT INTO archives
				(user_id, public_archive_id, title, url, schema_plugin, harvester_plugin, enabled)
				VALUES
				(?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $archive->getUserId(),
				$archive->getPublicArchiveId(),
				$archive->getTitle(),
				$archive->getUrl(),
				$archive->getSchemaPluginName(),
				$archive->getHarvesterPluginName(),
				$archive->getEnabled()?1:0
			)
		);

		$archiveId = $this->getInsertArchiveId();
		$archive->setArchiveId($archiveId);

		return $archive->getArchiveId();
	}

	/**
	 * Update an existing archive.
	 * @param $archive Archive
	 */
	function updateArchive(&$archive) {
		return $this->update(
			'UPDATE archives
				SET
					user_id = ?,
					public_archive_id = ?,
					title = ?,
					url = ?,
					schema_plugin = ?,
					harvester_plugin = ?,
					enabled = ?
				WHERE archive_id = ?',
			array(
				(int) $archive->getUserId(),
				$archive->getPublicArchiveId(),
				$archive->getTitle(),
				$archive->getUrl(),
				$archive->getSchemaPluginName(),
				$archive->getHarvesterPluginName(),
				$archive->getEnabled()?1:0,
				(int) $archive->getArchiveId()
			)
		);
	}

	/**
	 * Delete an archive, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $archive Archive
	 */
	function deleteArchive(&$archive) {
		return $this->deleteArchiveById($archive->getArchiveId());
	}

	/**
	 * Delete an archive by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $archiveId int
	 */
	function deleteArchiveById($archiveId) {
		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$recordDao->deleteRecordsByArchiveId($archiveId);

		$archiveSettingsDao =& DAORegistry::getDAO('ArchiveSettingsDAO');
		$archiveSettingsDao->deleteSettingsByArchiveId($archiveId);

		return $this->update(
			'DELETE FROM archives WHERE archive_id = ?', $archiveId
		);
	}

	/**
	 * Retrieve all archives.
	 * @param $onlyEnabled boolean
	 * @param $rangeInfo object
	 * @return DAOResultFactory containing matching archives
	 */
	function &getArchives($onlyEnabled = true, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$result =& $this->retrieveRange(
			'SELECT a.*,
				u.username AS archive_manager
			FROM archives a
				LEFT JOIN users u ON a.user_id = u.user_id' . 
			($onlyEnabled?' WHERE enabled = 1':'') . 
			($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
			false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnArchiveFromRow');
		return $returner;
	}

	/**
	 * Retrieve all archives by user ID.
	 * @param $onlyEnabled boolean
	 * @param $rangeInfo object
	 * @return DAOResultFactory containing matching archives
	 */
	function &getArchivesByUserId($userId, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$result =& $this->retrieveRange(
			'SELECT * FROM archives WHERE user_id = ?' . ($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
			array((int) $userId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnArchiveFromRow');
		return $returner;
	}

	/**
	 * Get the ID of the last inserted archive.
	 * @return int
	 */
	function getInsertArchiveId() {
		return $this->getInsertId('archives', 'archive_id');
	}

	/**
	 * Retrieve a count of the archives available.
	 * @return int
	 */
	function getArchiveCount() {
		$result =& $this->retrieve('SELECT COUNT(*) AS count FROM archives');

		$count = 0;
		if ($result->RecordCount() != 0) {
			$row =& $result->GetRowAssoc(false);
			$count = $row['count'];
		}
		$result->Close();
		unset($result);
		return $count;
	}

	/**
	 * Determine whether or not an archive exists with the supplied public archive ID
	 * @param $publicArchiveId string
	 * @param $excludeArchiveId int optional
	 * @return boolean
	 */
	function archiveExistsByPublicArchiveId($publicArchiveId, $excludeArchiveId = null) {
		$archive =& $this->getArchiveByPublicArchiveId($publicArchiveId);
		if ($archive && $archive->getArchiveId() != $excludeArchiveId) return true;
		return false;
	}
			
	/**
	 * Map a column heading value to a database value for sorting
	 * @param string
	 * @return string
	 */
	function getSortMapping($heading) {
		switch ($heading) {
			case 'title': return 'title';
			case 'url': return 'url';
			case 'manager': return 'archive_manager';
			case 'type': return '';
			default: return null;
		}
	}
}

?>
