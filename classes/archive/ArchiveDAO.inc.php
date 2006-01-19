<?php

/**
 * ArchiveDAO.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package archive
 *
 * Class for Archive DAO.
 * Operations for retrieving and modifying Archive objects.
 *
 * $Id$
 */

import ('archive.Archive');

class ArchiveDAO extends DAO {

	/**
	 * Constructor.
	 */
	function ArchiveDAO() {
		parent::DAO();
	}
	
	/**
	 * Retrieve a archive by ID.
	 * @param $archiveId int
	 * @return Archive
	 */
	function &getArchive($archiveId) {
		$result = &$this->retrieve(
			'SELECT * FROM archives WHERE archive_id = ?', $archiveId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnArchiveFromRow($result->GetRowAssoc(false));
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
		$archive = &new Archive();
		$archive->setArchiveId($row['archive_id']);
		$archive->setTitle($row['title']);
		$archive->setDescription($row['description']);
		$archive->setUrl($row['url']);
		$archive->setHarvesterPlugin($row['harvester_plugin']);
		
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
				(title, description, url, harvester_plugin)
				VALUES
				(?, ?, ?, ?)',
			array(
				$archive->getTitle(),
				$archive->getDescription(),
				$archive->getUrl(),
				$archive->getHarvesterPlugin()
			)
		);
		
		$archive->setArchiveId($this->getInsertArchiveId());
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
					title = ?,
					description = ?,
					url = ?,
					harvester_plugin = ?
				WHERE archive_id = ?',
			array(
				$archive->getTitle(),
				$archive->getDescription(),
				$archive->getUrl(),
				$archive->getHarvesterPlugin(),
				$archive->getArchiveId()
			)
		);
	}
	
	/**
	 * Delete a archive, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $archive Archive
	 */
	function deleteArchive(&$archive) {
		return $this->deleteArchiveById($archive->getArchiveId());
	}
	
	/**
	 * Delete a archive by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $archiveId int
	 */
	function deleteArchiveById($archiveId) {
		return $this->update(
			'DELETE FROM archives WHERE archive_id = ?', $archiveId
		);
	}
	
	/**
	 * Retrieve all archives.
	 * @return DAOResultFactory containing matching archives
	 */
	function &getArchives($rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM archives',
			false, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnArchiveFromRow');
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
		$result = &$this->retrieve('SELECT COUNT(*) AS count FROM archives');

		$count = 0;
		if ($result->RecordCount() != 0) {
			$row =& $result->GetRowAssoc(false);
			$count = $row['count'];
		}
		$result->Close();
		unset($result);
		return $count;
	}
	
}

?>
