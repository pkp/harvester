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
		switch ($tag) {
			case 'marc':
			case 'oai_marc:marc':
			case 'mx:record':
			case 'mx:leader':
				return;
			case 'varfield':
			case 'fixfield':
			case 'mx:fixfield':
			case 'mx:controlfield':
			case 'mx:datafield':
				$this->id = isset($attributes['id'])?$attributes['id']:(isset($attributes['tag'])?$attributes['tag']:null);
				$this->i1 = isset($attributes['i1'])?$attributes['i1']:(isset($attributes['ind1'])?$attributes['ind1']:null);
				$this->i2 = isset($attributes['i2'])?$attributes['i2']:(isset($attributes['ind2'])?$attributes['ind2']:null);
				$this->label = null;
				break;
			case 'subfield':
			case 'mx:subfield':
				$this->label = isset($attributes['label'])?$attributes['label']:(isset($attributes['code'])?$attributes['code']:null);
				break;
		}
	}

	function endElement(&$parser, $tag) {
		switch ($tag) {
			case 'marc':
			case 'oai_marc:marc':
			case 'subfield':
			case 'mx:record':
			case 'mx:subfield':
			case 'mx:leader':
				return;
		}

		$data = trim($this->characterData);
		if (empty($data)) return;

		$id = trim($this->id);
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
