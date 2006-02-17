<?php

/**
 * DublinCoreXMLHandler.inc.php
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

class DublinCoreXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $metadata array */
	var $metadata;

	/** @var $characterData string */
	var $characterData;

	/**
	 * Constructor
	 * @param $harvester object
	 * @param $metadata array Reference to array to populate with metadata
	 */
	function DublinCoreXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;
		switch ($tag) {
			case 'dc':
			case 'oai_dc:dc':
				unset($this->metadata);
				$this->metadata = array();
				return;
		}
	}

	function endElement(&$parser, $tag) {
		switch ($tag) {
			case 'dc:identifier':
			case 'identifier':
			case 'dc':
			case 'oai_dc:dc':
				return;
		}

		// Strip the "dc:" from the tag, and we have the field key.
		if (substr($tag, 0, 3) === 'dc:') {
			$fieldKey = substr($tag, 3);
		} else {
			$fieldKey = $tag;
		}

		$field =& $this->harvester->getFieldByKey($fieldKey, DublinCorePlugin::getName());
		if (!$field) {
			$this->harvester->addError(Locale::translate('harvester.error.unknownMetadataField', array('name' => $fieldKey)));
			return;
		}
		
		if (isset($this->metadata[$fieldKey])) {
			if (is_array($this->metadata[$fieldKey])) {
				array_push($this->metadata[$fieldKey], $this->characterData);
			} else {
				$this->metadata[$fieldKey] = array($this->metadata[$fieldKey], $this->characterData);
			}
		} else {
			$this->metadata[$fieldKey] = $this->characterData;
		}
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
