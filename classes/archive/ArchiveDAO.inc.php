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
			$returner = $this->_fromRow($result->GetRowAssoc(false));
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
			$returner = $this->_fromRow($result->GetRowAssoc(false));
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
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Claims the next flagged archive found in the database and
	 * returns it. This method is idempotent and parallelisable.
	 * It uses an atomic locking strategy to avoid race conditions.
	 *
	 * @param $request Request
	 * @param $lockId string a globally unique id that
	 *  identifies the calling process.
	 * @return mixed Archive if one could be found and locked, otherwise
	 *  false.
	 */
	function getNextFlaggedArchive($request, $lockId) {
		// NB: We implement an atomic locking strategy to make
		// sure that no two parallel background processes can claim the
		// same archive.
		$archive = null;

		for ($try = 0; $try < 3; $try++) {
			// We use three statements (read, write, read) rather than
			// MySQL's UPDATE ... LIMIT ... to guarantee compatibility
			// with ANSI SQL.

			// Get the ID of the next flagged archive.
			$result = $this->retrieve(
				'SELECT archive_id
				FROM archives
				WHERE awaiting_harvest = 1
				LIMIT 1'
			);
			if ($result->RecordCount() > 0) {
				$nextArchive = $result->GetRowAssoc(false);
				$nextArchiveId = $nextArchive['archive_id'];
			} else {
				// Nothing to do.
				$result->Close();
				return false;
			}
			$result->Close();
			unset($result);

			// Lock the archive.
			$this->update(
				'UPDATE archives
				SET awaiting_harvest = 0, lock_id = ?
				WHERE archive_id = ? AND awaiting_harvest = 1',
				array($lockId, (int) $nextArchiveId)
			);

			// Make sure that no other concurring process
			// has claimed this archive before we could
			// lock it.
			$result = $this->retrieve(
				'SELECT *
				FROM archives
				WHERE lock_id = ?',
				$lockId
			);
			if ($result->RecordCount() > 0) {
				$archive = $this->_fromRow($result->GetRowAssoc(false));
				break;
			}
		}
		$result->Close();
		if (!is_a($archive, 'Archive')) return false;

		// Updating the archive will release the lock.
		$this->updateObject($archive);

		return $archive;
	}

	/**
	 * Construct a new Archive object.
	 * @return Archive
	 */
	function newDataObject() {
		return new Archive();
	}

	/**
	 * Internal function to return a Archive object from a row.
	 * @param $row array
	 * @return Archive
	 */
	function _fromRow($row) {
		$archive = $this->newDataObject();
		$archive->setArchiveId($row['archive_id']);
		$archive->setUserId($row['user_id']);
		$archive->setPublicArchiveId($row['public_archive_id']);
		$archive->setTitle($row['title']);
		$archive->setEnabled($row['enabled']);
		$archive->setUrl($row['url']);
		$archive->setLockId($row['lock_id']);
		$archive->setAwaitingHarvest($row['awaiting_harvest']);
		$archive->setHarvesterPluginName($row['harvester_plugin']);
		$archive->setSchemaPluginName($row['schema_plugin']);

		HookRegistry::call('ArchiveDAO::_returnArchiveFromRow', array(&$archive, &$row));

		return $archive;
	}

	/**
	 * Insert a new archive.
	 * @param $archive Archive
	 */	
	function insertObject($archive) {
		$this->update(
			'INSERT INTO archives
				(user_id, public_archive_id, title, url, schema_plugin, harvester_plugin, enabled, awaiting_harvest)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $archive->getUserId(),
				$archive->getPublicArchiveId(),
				$archive->getTitle(),
				$archive->getUrl(),
				$archive->getSchemaPluginName(),
				$archive->getHarvesterPluginName(),
				$archive->getEnabled()?1:0,
				$archive->getAwaitingHarvest()?1:0,
				$archive->getLockId(),
			)
		);

		$archiveId = $this->getInsertId();
		$archive->setArchiveId($archiveId);

		return $archive->getArchiveId();
	}

	/**
	 * Update an existing archive.
	 * @param $archive Archive
	 */
	function updateObject($archive) {
		return $this->update(
			'UPDATE archives
				SET
					user_id = ?,
					public_archive_id = ?,
					title = ?,
					url = ?,
					schema_plugin = ?,
					harvester_plugin = ?,
					enabled = ?,
					awaiting_harvest = ?,
					lock_id = NULL
				WHERE archive_id = ?',
			array(
				(int) $archive->getUserId(),
				$archive->getPublicArchiveId(),
				$archive->getTitle(),
				$archive->getUrl(),
				$archive->getSchemaPluginName(),
				$archive->getHarvesterPluginName(),
				$archive->getEnabled()?1:0,
				$archive->getAwaitingHarvest()?1:0,
				(int) $archive->getArchiveId()
			)
		);
	}

	/**
	 * Delete an archive, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $archive Archive
	 */
	function deleteArchive($archive) {
		return $this->deleteArchiveById($archive->getArchiveId());
	}

	/**
	 * Delete an archive by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $archiveId int
	 */
	function deleteArchiveById($archiveId) {
		$recordDao = DAORegistry::getDAO('RecordDAO');
		$recordDao->deleteRecordsByArchiveId($archiveId);

		$archiveSettingsDao = DAORegistry::getDAO('ArchiveSettingsDAO');
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
		$result = $this->retrieveRange(
			'SELECT a.*,
				u.username AS archive_manager
			FROM archives a
				LEFT JOIN users u ON a.user_id = u.user_id' . 
			($onlyEnabled?' WHERE enabled = 1':'') . 
			($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
			false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve all archives by user ID.
	 * @param $onlyEnabled boolean
	 * @param $rangeInfo object
	 * @return DAOResultFactory containing matching archives
	 */
	function &getArchivesByUserId($userId, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$result = $this->retrieveRange(
			'SELECT * FROM archives WHERE user_id = ?' . ($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
			array((int) $userId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get the ID of the last inserted archive.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('archives', 'archive_id');
	}

	/**
	 * Retrieve a count of the archives available.
	 * @return int
	 */
	function getArchiveCount() {
		$result = $this->retrieve('SELECT COUNT(*) AS count FROM archives');

		$count = 0;
		if ($result->RecordCount() != 0) {
			$row = $result->GetRowAssoc(false);
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
