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

	/** @var $nameAssocId int */
	var $nameAssocId;

	/** @var $titleAssocId int */
	var $titleAssocId;

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

		if (String::substr($tag, 0, 5) === 'mods:') {
			$tag = String::substr($tag, 5);
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
			case 'name':
				unset($this->nameAssocId);
				break;
			case 'titleInfo':
				unset($this->titleAssocId);
				break;
		}
		$this->attributes[$tag] = $attributes;
	}

	function endElement(&$parser, $tag) {
		// Strip the "mods:" from the tag, and we have the field key.
		if (String::substr($tag, 0, 5) === 'mods:') {
			$tag = String::substr($tag, 5);
		}

		if ($this->inRelatedItem) {
			if ($tag == 'relatedItem') {
				$this->inRelatedItem = false;
			}
			return;
		}

		switch ($tag) {
			case 'namePart':
			case 'affiliation':
			case 'roleTerm':
			case 'description':
				// For subelements of the "name" tag, group them with an attribute
				if (isset($this->nameAssocId)) $this->attributes[$tag]['nameAssocId'] = $this->nameAssocId;
				else unset($this->attributes[$tag]['nameAssocId']);
				$field =& $this->harvester->getFieldByKey($tag, ModsPlugin::getName());
				$entryId = $this->harvester->insertEntry($field, $this->characterData, $this->attributes[$tag]);
				if ($entryId !== null && !isset($this->nameAssocId)) {
					$recordDao =& DAORegistry::getDAO('RecordDAO');
					$recordDao->insertEntryAttribute($entryId, 'nameAssocId', $entryId);
					$this->nameAssocId = $entryId;
				}
				break;
			case 'title':
			case 'subTitle':
			case 'partNumber':
			case 'partName':
			case 'nonSort':
				// For subelements of the "titleInfo" tag, group them with an attribute
				if (isset($this->titleAssocId)) $this->attributes[$tag]['titleAssocId'] = $this->titleAssocId;
				else unset($this->attributes[$tag]['titleAssocId']);
				$field =& $this->harvester->getFieldByKey($tag, ModsPlugin::getName());
				$entryId = $this->harvester->insertEntry($field, $this->characterData, $this->attributes[$tag]);
				if ($entryId && !isset($this->titleAssocId)) {
					$recordDao =& DAORegistry::getDAO('RecordDAO');
					$recordDao->insertEntryAttribute($entryId, 'titleAssocId', $entryId);
					$this->titleAssocId = $entryId;
				}
				break;
			case 'displayForm':
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
			case 'role':
			case 'name':
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
