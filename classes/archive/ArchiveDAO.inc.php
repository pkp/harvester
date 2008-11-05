<?php

/**
 * @file ArchiveDAO.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
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


import ('archive.Archive');

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
			'SELECT * FROM archives WHERE public_archive_id = ?' . ($onlyEnabled?' AND enabled = 1':''), $publicArchiveId
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
				(user_id, public_archive_id, title, url, harvester_plugin, enabled)
				VALUES
				(?, ?, ?, ?, ?, ?)',
			array(
				(int) $archive->getUserId(),
				$archive->getPublicArchiveId(),
				$archive->getTitle(),
				$archive->getUrl(),
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
					harvester_plugin = ?,
					enabled = ?
				WHERE archive_id = ?',
			array(
				(int) $archive->getUserId(),
				$archive->getPublicArchiveId(),
				$archive->getTitle(),
				$archive->getUrl(),
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
	function &getArchives($onlyEnabled = true, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM archives' . ($onlyEnabled?' WHERE enabled = 1 ORDER BY title':''),
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
	function &getArchivesByUserId($userId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM archives WHERE user_id = ? ORDER BY title',
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
}

?>
