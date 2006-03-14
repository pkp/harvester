<?php

/**
 * ModsXMLHandler.inc.php
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

class ModsXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $metadata array */
	var $metadata;

	/** @var $characterData string */
	var $characterData;

	/** @var $attributes array */
	var $attributes;

	/** @var $inPhysicalDescription boolean */
	var $inPhysicalDescription;

	/** @var $inRelatedItem */
	var $inRelatedItem;

	/**
	 * Constructor
	 * @param $harvester object
	 * @param $metadata array Reference to array to populate with metadata
	 */
	function ModsXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
		$this->inPhysicalDescription = false;
	}

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;

		if (substr($tag, 0, 5) === 'mods:') {
			$tag = substr($tag, 5);
		}
		switch ($tag) {
			case 'physicalDescription':
				$this->inPhysicalDescription = true; // For differentiating note
				break;
			case 'relatedItem':
				$this->inRelatedItem = true;
				break;
			case 'note':
				if ($this->inPhysicalDescription) $tag = 'physicalDescriptionNote';
				break;
		}
		$this->attributes[$tag] = $attributes;
	}

	function endElement(&$parser, $tag) {
		// Strip the "mods:" from the tag, and we have the field key.
		if (substr($tag, 0, 5) === 'mods:') {
			$tag = substr($tag, 5);
		}

		if ($this->inRelatedItem) {
			if ($tag == 'relatedItem') {
				$this->inRelatedItem = false;
			}
			return;
		}

		switch ($tag) {
			case 'title':
			case 'subTitle':
			case 'partNumber':
			case 'partName':
			case 'nonSort':
			case 'namePart':
			case 'displayForm':
			case 'affiliation':
			case 'role':
			case 'roleTerm':
			case 'description':
			case 'typeOfResource':
			case 'genre':
			case 'placeTerm':
			case 'publisher':
			case 'edition':
			case 'issuance':
			case 'frequency':
			case 'languageTerm':
			case 'form':
			case 'reformattingQuality':
			case 'internetMediaType':
			case 'extent':
			case 'digitalOrigin':
			case 'abstract':
			case 'tableOfContents':
			case 'targetAudience':
			case 'topic':
			case 'geographic':
			case 'temporal':
			case 'geographicCode':
			case 'continent':
			case 'country':
			case 'region':
			case 'state':
			case 'territory':
			case 'county':
			case 'city':
			case 'island':
			case 'area':
			case 'scale':
			case 'projection':
			case 'coordinates':
			case 'occupation':
			case 'classification':
			case 'identifier':
			case 'accessCondition':
			case 'url':
			case 'physicalLocation':
			case 'extension':
			case 'recordContentSource':
			case 'recordIdentifier':
			case 'recordOrigin':
			case 'languageOfCataloging':
				$field =& $this->harvester->getFieldByKey($tag, ModsPlugin::getName());
				$entryId = $this->harvester->insertEntry($field, $this->characterData, $this->attributes[$tag]);
				break;
			case 'note':
				$tagName = $this->inPhysicalDescription?'physicalDescriptionNote':'note';
				$field =& $this->harvester->getFieldByKey($tagName, ModsPlugin::getName());
				$entryId = $this->harvester->insertEntry($field, $this->characterData, $this->attributes[$tagName]);
				break;
			case 'name':
			case 'dateIssued':
			case 'dateCreated':
			case 'dateCaptured':
			case 'dateValid':
			case 'dateModified':
			case 'copyrightDate':
			case 'dateOther':
			case 'recordCreationDate':
			case 'recordChangeDate':
				// FIXME;
				break;
			case 'mods':
			case 'titleInfo':
			case 'originInfo':
			case 'place':
			case 'language':
			case 'hierarchicalGeographic':
			case 'cartographics':
			case 'identifier':
			case 'location':
			case 'recordInfo':
			case 'subject':
				// Do nothing.
				break;
			case 'physicalDescription':
				$this->inPhysicalDescription = false; // For differentiating note
				break;
			default:
				fatalError("Unknown tag \"$tag\"!");
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
