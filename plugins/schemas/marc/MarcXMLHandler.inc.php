<?php

/**
 * @file MarcXMLHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.marc
 * @class MarcXMLHandler
 *
 * MarcXML parser
 *
 * $Id$
 */

class MarcXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $characterData string */
	var $characterData;

	var $id;
	var $i1;
	var $i2;
	var $label;

	/**
	 * Constructor
	 * @param $harvester object
	 */
	function MarcXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;
		switch (strtolower($tag)) {
			case 'marc':
			case 'oai_marc:marc':
			case 'mx:record':
			case 'mx:leader':
				return;
			case 'varfield':
			case 'fixfield':
			case 'datafield':
			case 'controlfield':
			case 'mx:varfield':
			case 'mx:fixfield':
			case 'mx:datafield':
			case 'mx:controlfield':
				$this->id = !empty($attributes['id'])?$attributes['id']:(!empty($attributes['tag'])?$attributes['tag']:null);
				$this->i1 = !empty($attributes['i1'])?$attributes['i1']:(!empty($attributes['ind1'])?$attributes['ind1']:null);
				$this->i2 = !empty($attributes['i2'])?$attributes['i2']:(!empty($attributes['ind2'])?$attributes['ind2']:null);
				$this->label = null;
				break;
			case 'subfield':
			case 'mx:subfield':
				$this->label = isset($attributes['label'])?$attributes['label']:(isset($attributes['code'])?$attributes['code']:null);
				break;
		}
	}

	function endElement(&$parser, $tag) {
		switch (strtolower($tag)) {
			case 'marc':
			case 'oai_marc:marc':
			case 'subfield':
			case 'mx:record':
			case 'mx:subfield':
			case 'mx:leader':
				return;
			case 'leader':
				$id = $tag;
				break;
			default:
				$id = trim($this->id);
				break;
		}

		$data = trim($this->characterData);
		if (empty($data)) return;

		$i1 = trim($this->i1);
		$i2 = trim($this->i2);
		$label = trim($this->label);
		$field =& $this->harvester->getFieldByKey($id, MarcPlugin::getName());
		if (!$field) {
			$this->harvester->addError(Locale::translate('harvester.error.unknownMetadataField', array('name' => $id)));
			return;
		}

		// Store the attributes i1, i2, and label, if set
		$attributes = array();
		if ($i1 != '') $attributes['i1'] = $i1;
		if ($i2 != '') $attributes['i2'] = $i2;
		if ($label != '') $attributes['label'] = $label;

		$this->harvester->insertEntry($field, $this->characterData, $attributes);
		$this->characterData = null;
	}

	function characterData(&$parser, $data) {
		if ($this->characterData === null) {
			$this->characterData = '';
		}
		$this->characterData .= $data;
	}
}

?>
