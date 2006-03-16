<?php

/**
 * CrosswalkDAO.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * Class for Crosswalk DAO.
 * Operations for retrieving and modifying Crosswalk objects.
 *
 * $Id$
 */

import ('field.Crosswalk');

class CrosswalkDAO extends DAO {
	/**
	 * Constructor.
	 */
	function CrosswalkDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a crosswalk by ID.
	 * @param $crosswalkId int
	 * @return Crosswalk
	 */
	function &getCrosswalkById($crosswalkId) {

		$result = &$this->retrieve(
			'SELECT * FROM crosswalks WHERE crosswalk_id = ?', $crosswalkId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnCrosswalkFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}
	
	/**
	 * Internal function to return a Crosswalk object from a row.
	 * @param $row array
	 * @return Crosswalk
	 */
	function &_returnCrosswalkFromRow(&$row) {
		$crosswalk = &new Crosswalk();
		$crosswalk->setCrosswalkId($row['crosswalk_id']);
		$crosswalk->setName($row['name']);
		$crosswalk->setDescription($row['description']);
		$crosswalk->setSeq($row['seq']);
		
		HookRegistry::call('CrosswalkDAO::_returnCrosswalkFromRow', array(&$crosswalk, &$row));

		return $crosswalk;
	}

	/**
	 * Insert a new crosswalk.
	 * @param $crosswalk Crosswalk
	 */	
	function insertCrosswalk(&$crosswalk) {
		$this->update(
			'INSERT INTO crosswalks
				(name, description, seq)
				VALUES
				(?, ?, ?)',
			array(
				$crosswalk->getName(),
				$crosswalk->getDescription(),
				$crosswalk->getSeq()
			)
		);
		
		$crosswalk->setCrosswalkId($this->getInsertCrosswalkId());
		return $crosswalk->getCrosswalkId();
	}

	/**
	 * Sequentially renumber crosswalks in their sequence order.
	 */
	function resequenceCrosswalks() {
		$result = &$this->retrieve(
			'SELECT crosswalk_id FROM crosswalks ORDER BY seq'
		);
		
		for ($i=1; !$result->EOF; $i++) {
			list($crosswalkId) = $result->fields;
			$this->update(
				'UPDATE crosswalks SET seq = ? WHERE crosswalk_id = ?',
				array(
					$i,
					$crosswalkId
				)
			);
			
			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Update an existing field.
	 * @param $crosswalk Crosswalk
	 */
	function updateCrosswalk(&$crosswalk) {
		return $this->update(
			'UPDATE crosswalks
				SET
					name = ?,
					description = ?,
					seq = ?
				WHERE crosswalk_id = ?',
			array(
				$crosswalk->getName(),
				$crosswalk->getDescription(),
				$crosswalk->getSeq(),
				$crosswalk->getCrosswalkId()
			)
		);
	}
	
	/**
	 * Delete a crosswalk, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalk Crosswalk
	 */
	function deleteCrosswalk(&$crosswalk) {
		return $this->deleteCrosswalkById($crosswalk->getCrosswalkId());
	}
	
	/**
	 * Delete a crosswalk by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalkId int
	 */
	function deleteCrosswalkById($crosswalkId) {
		$this->deleteCrosswalkFieldsByCrosswalkId($crosswalkId);
		return $this->update(
			'DELETE FROM crosswalks WHERE crosswalk_id = ?', $crosswalkId
		);
	}
	
	/**
	 * Retrieve all crosswalks.
	 * @return DAOResultFactory containing matching crosswalks
	 */
	function &getCrosswalks($rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM crosswalks ORDER BY seq',
			false, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnCrosswalkFromRow');
		return $returner;
	}
	
	/**
	 * Get the ID of the last inserted crosswalk.
	 * @return int
	 */
	function getInsertCrosswalkId() {
		return $this->getInsertId('crosswalks', 'crosswalk_id');
	}
	
	/**
	 * Retrieve all field IDs for a crosswalk.
	 * @return DAOResultFactory containing matching crosswalks
	 */
	function &getFieldsByCrosswalkId($crosswalkId, $rangeInfo = null) {
		$fieldDao =& DAORegistry::getDAO('FieldDAO');

		$result = &$this->retrieveRange(
			'SELECT f.* FROM raw_fields f, crosswalk_fields c WHERE f.raw_field_id = c.raw_field_id AND c.crosswalk_id = ?',
			$crosswalkId, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $fieldDao, '_returnFieldFromRow');
		return $returner;
	}
	
	/**
	 * Delete crosswalk fields by crosswalk ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalkId int
	 */
	function deleteCrosswalkFieldsByCrosswalkId($crosswalkId) {
		return $this->update(
			'DELETE FROM crosswalk_fields WHERE crosswalk_id = ?', $crosswalkId
		);
	}
	
	/**
	 * Insert fields for a crosswalk.
	 * @param $crosswalkId int
	 */
	function insertCrosswalkField($crosswalkId, $fieldId) {
		return $this->update(
			'INSERT INTO crosswalk_fields(crosswalk_id, raw_field_id) VALUES (?, ?)', array($crosswalkId, $fieldId)
		);
	}

	/**
	 * Fetch a list of crosswalks that can be searched
	 * on the set of schemas provided.
	 * @param $schemas array
	 * @return array
	 */
	function &getCrosswalksForSchemas($schemas) {
		$params = array();
		$schemaTableList = '';
		$schemaWhereList = '';
		$schemaIndex = 0;
		foreach ($schemas as $schema) {
			$schemaTableList .= ", raw_fields f$schemaIndex, crosswalk_fields cf$schemaIndex";
			if (!empty($schemaWhereList)) $schemaWhereList .= ' AND ';
			$schemaWhereList .= "f$schemaIndex.schema_plugin_id = ? AND f$schemaIndex.raw_field_id = cf$schemaIndex.raw_field_id AND cf$schemaIndex.crosswalk_id = c.crosswalk_id";
			array_push($params, $schema->getSchemaId());
			$schemaIndex++;
		}
		$result = &$this->retrieveRange(
			'SELECT DISTINCT c.* FROM crosswalks c' . $schemaTableList . ' WHERE ' . $schemaWhereList . ' ORDER BY c.seq',
			$params
		);

		$returner = &new DAOResultFactory($result, $this, '_returnCrosswalkFromRow');
		return $returner;
	}

	/**
	 * Used internally by installCrosswalks to perform variable and translation replacements.
	 * @param $rawInput string contains text including variable and/or translate replacements.
	 * @param $paramArray array contains variables for replacement
	 * @returns string
	 */
	function _performReplacement($rawInput, $paramArray = array()) {
		$value = preg_replace_callback('{{translate key="([^"]+)"}}', '_installer_crosswalk_regexp_callback', $rawInput);
		foreach ($paramArray as $pKey => $pValue) {
			$value = str_replace('{$' . $pKey . '}', $pValue, $value);
		}
		return $value;
	}

	/**
	 * Install crosswalks from an XML file.
	 * @param $filename string Name of XML file to parse and install
	 * @param $paramArray array Optional parameters for variable replacement in crosswalks
	 */
	function installCrosswalks($filename, $paramArray = array()) {
		$xmlParser = &new XMLParser();
		$tree = $xmlParser->parse($filename);

		if (!$tree) {
			$xmlParser->destroy();
			return false;
		}

		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$fieldDao =& DAORegistry::getDAO('FieldDAO');

		foreach ($tree->getChildren() as $crosswalkNode) {
			$nameNode = &$crosswalkNode->getChildByName('name');
			$descriptionNode = &$crosswalkNode->getChildByName('description');

			if (isset($nameNode) && isset($descriptionNode)) {
				$name = $this->_performReplacement($nameNode->getValue());
				$description = $this->_performReplacement($descriptionNode->getValue());

				$crosswalk =& new Crosswalk();
				$crosswalk->setName($name);
				$crosswalk->setDescription($description);
				$crosswalk->setSeq(99999); // KLUDGE
				$this->insertCrosswalk($crosswalk);
				$this->resequenceCrosswalks();

				foreach ($crosswalkNode->getChildren() as $node) if ($node->getName() == 'field') {
					$schemaPluginName = $node->getAttribute('schema');
					$fieldName = $node->getAttribute('name');

					$schema =& $schemaDao->buildSchema($schemaPluginName);
					$field =& $fieldDao->buildField($fieldName, $schemaPluginName);

					$this->insertCrosswalkField($crosswalk->getCrosswalkId(), $field->getFieldId());
					unset($schema);
					unset($field);
				}
				unset($crosswalk);
			}
		}

		$xmlParser->destroy();

	}
}

/**
 * Used internally by crosswalk installation code to perform translation function.
 */
function _installer_crosswalk_regexp_callback($matches) {
	return Locale::translate($matches[1]);
}

?>
