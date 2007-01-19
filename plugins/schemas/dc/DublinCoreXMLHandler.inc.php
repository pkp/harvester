<?php

/**
 * @file DublinCoreXMLHandler.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.dc
 * @class DublinCoreXMLHandler
 *
 * DC XML parser
 *
 * $Id$
 */

class DublinCoreXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $characterData string */
	var $characterData;

	/**
	 * Constructor
	 * @param $harvester object
	 */
	function DublinCoreXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;
		switch ($tag) {
			case 'dc':
			case 'oai_dc:dc':
				return;
		}
	}

	function endElement(&$parser, $tag) {
		switch ($tag) {
			case 'dc':
			case 'oai_dc:dc':
				return;
		}

		// Strip the "dc:" from the tag, and we have the field key.
		if (String::substr($tag, 0, 3) === 'dc:') {
			$fieldKey = String::substr($tag, 3);
		} else {
			$fieldKey = $tag;
		}

		$field =& $this->harvester->getFieldByKey($fieldKey, DublinCorePlugin::getName());
		if (!$field) {
			$this->harvester->addError(Locale::translate('harvester.error.unknownMetadataField', array('name' => $fieldKey)));
			return;
		}

		$this->harvester->insertEntry($field, $this->characterData);
	}

	function characterData(&$parser, $data) {
		if ($this->characterData === null) {
			$this->characterData = '';
		}
		$this->characterData .= $data;
	}
}

?>
