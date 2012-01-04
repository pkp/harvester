<?php

/**
 * @file SchemaAlias.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SchemaAlias
 * @in_group schema
 *
 * Provides methods for accessing the schema map.
 *
 */

// $Id$


define('SCHEMA_MAP_REGISTRY_FILE', Config::getVar('general', 'registry_dir') . '/schemaMap.xml');

import('lib.pkp.classes.db.DAO');

class SchemaAliasDAO extends DAO {
	/**
	 * Constructor.
	 */
	function SchemaAliasDAO() {
		parent::DAO();
	}

	function &parseSchemaMap() {
		$xmlDao = new XMLDAO();
		$schemaMapHandler = new SchemaMapHandler();
		$schemaMap =& $xmlDao->parseWithHandler(SCHEMA_MAP_REGISTRY_FILE, $schemaMapHandler);
		return $schemaMap;
	}

	function insertSchemaAlias($alias, $schemaPluginId) {
		return $this->update(
			'INSERT INTO schema_aliases (alias, schema_plugin_id) VALUES (?, ?)',
			array($alias, (int) $schemaPluginId)
		);
	}

	function installSchemaAliases() {
		$schemaMap =& $this->parseSchemaMap();
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		foreach ($schemaMap as $alias => $schemaPluginName) {
			$schema =& $schemaDao->buildSchema($schemaPluginName);
			$this->insertSchemaAlias($alias, $schema->getSchemaId());
			unset($schema);
		}
	}
}

/**
 * Handle XML parsing events for the SchemaMap class.
 */
class SchemaMapHandler extends XMLParserHandler {
	var $result;
	var $currentSchemaPlugin;
	var $characterData;

	function SchemaMapHandler() {
		$this->result = array();
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = '';
		switch ($tag) {
			case 'schema':
				$this->currentSchemaPlugin = $attributes['plugin'];
				break;
		}
	}
	function endElement(&$parser, $tag) {
		switch ($tag) {
			case 'alias':
				$schemaPlugin = $this->currentSchemaPlugin;
				$schemaAlias = $this->characterData;

				$this->result[$schemaAlias] = $schemaPlugin;
				break;
		}
	}
	function characterData(&$parser, $data) {
		$this->characterData .= $data;
	}
	function &getResult() {
		return $this->result;
	}
}

?>
