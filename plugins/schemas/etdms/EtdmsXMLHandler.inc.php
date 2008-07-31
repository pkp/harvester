<?php

/**
 * @file EtdmsXMLHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * * Edited and modified by Kennedy Onyancha - DoKS (KHK Kempen) (2007)
 * @package plugins.schemas.etdms
 * @class EtdmsXMLHandler
 *
 * DC XML parser
 *
 * $Id$
 */

class EtdmsXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $characterData string */
	var $characterData;

	/**
	 * Constructor
	 * @param $harvester object
	 */
	function EtdmsXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;
		switch ($tag) {
			case 'oai_etdms':
			return;
		}
	}

	function endElement(&$parser, $tag) {
		switch ($tag) {
			case 'oai_etdms':
			return;
		}

		// Strip the "thesis:" from the tag, and we have the field key.  
		if (String::substr($tag, 0, 10) === 'oai_etdms:') {
			$fieldKey = String::substr($tag, 10);

		} elseif (String::substr($tag, 0, 10) === 'oai_etdms:'){

		$fieldKey = String::substr($tag, 10);

		} else {
			$fieldKey = $tag;
		}

		$field =& $this->harvester->getFieldByKey($fieldKey, EtdmsPlugin::getName());
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
