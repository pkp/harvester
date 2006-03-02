<?php

/**
 * MarcXMLHandler.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * OAI XML parser
 *
 * $Id$
 */

class MarcXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $metadata array */
	var $metadata;

	/** @var $characterData string */
	var $characterData;

	var $id;
	var $i1;
	var $i2;
	var $label;

	/**
	 * Constructor
	 * @param $harvester object
	 * @param $metadata array Reference to array to populate with metadata
	 */
	function MarcXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;
		switch ($tag) {
			case 'marc':
			case 'oai_marc:marc':
				unset($this->metadata);
				$this->metadata = array();
				return;
			case 'varfield':
			case 'fixfield':
				$this->id = isset($attributes['id'])?$attributes['id']:null;
				$this->i1 = isset($attributes['i1'])?$attributes['i1']:null;
				$this->i2 = isset($attributes['i2'])?$attributes['i2']:null;
				break;
			case 'subfield':
				$this->label = isset($attributes['label'])?$attributes['label']:null;
		}
	}

	function endElement(&$parser, $tag) {
		switch ($tag) {
			case 'marc':
			case 'oai_marc:marc':
				return;
		}

		$data = trim($this->characterData);
		if (empty($data)) return;

		// Strip the "marc:" from the tag, and we have the field key.
		if (substr($tag, 0, 5) === 'marc:') {
			$fieldKey = substr($tag, 5);
		} else {
			$fieldKey = $tag;
		}

		$id = trim($this->id);
		$i1 = trim($this->i1);
		$i2 = trim($this->i2);

		$field =& $this->harvester->getFieldByKey($id, MarcPlugin::getName());
		if (!$field) {
			$this->harvester->addError(Locale::translate('harvester.error.unknownMetadataField', array('name' => $fieldKey)));
			return;
		}

		if (isset($this->metadata[$id])) {
			if (is_array($this->metadata[$id])) {
				array_push($this->metadata[$id], $data);
			} else {
				$this->metadata[$id] = array($this->metadata[$id], $data);
			}
		} else {
			$this->metadata[$id] = $data;
		}

		$this->characterData = null;
	}

	function characterData(&$parser, $data) {
		if ($this->characterData === null) {
			$this->characterData = '';
		}
		$this->characterData .= $data;
	}

	function &getMetadata() {
		return $this->metadata;
	}
}

?>
