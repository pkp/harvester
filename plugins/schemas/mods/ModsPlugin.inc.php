<?php

/**
 * @file ModsPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.mods
 * @class ModsPlugin
 *
 * MODS schema plugin
 *
 * $Id$
 */

import('classes.plugins.SchemaPlugin');

class ModsPlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'ModsPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return __('plugins.schemas.mods.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.schemas.mods.description');
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array(
				'title',
				'subTitle',
				'partNumber',
				'partName',
				'nonSort',
				'name',
				'nameType',
				'displayForm',
				'nameAffiliation',
				'nameDescription',
				'role',
				'typeOfResource',
				'publisher',
				'edition',
				'issuance',
				'frequency',
				'dateIssued',
				'dateCreated',
				'dateCaptured',
				'dateValid',
				'dateModified',
				'copyrightDate',
				'dateOther',
				'place',
				'language',
				'form',
				'reformattingQuality',
				'internetMediaType',
				'extent',
				'digitalOrigin',
				'note',
				'genre',
				'abstract',
				'tableOfContents',
				'targetAudience',
				'note',
				'classification',
				'accessCondition',
				'extension',
				'relatedItem',
				'subjectTopic',
				'subjectGeographic',
				'subjectTemporal',
				'subjectGeographicCode',
				'subjectGenre',
				'subjectOccupation',
				'subjectTitleInfo',
				'identifier',
				'location'
			);
		}
		return $fieldList;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return __("plugins.schemas.mods.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return __("plugins.schemas.mods.fields.$fieldSymbolic.description", $locale);
	}

	/**
	 * Parse a record's contents into an object
	 * @param $contents string
	 * @return object
	 */
	function &parseContents(&$contents) {
		$xmlParser = new XMLParser();
		$result =& $xmlParser->parseText($contents);
		$returner = array();

		$returner =& $this->handleRootNode($result);

		$result->destroy();
		$xmlParser->destroy();

		return $returner;
	}

	function &handleNameNode(&$nameNode) {
		$nameReturner = array();
		foreach (array('type', 'transliteration', 'authority', 'script') as $name) {
			if (($value = $nameNode->getAttribute($name)) !== null) $nameReturner[$name] = $value;
		}
		$namePartNode =& $nameNode->getChildByName(array('namePart', 'oai_mods:namePart', 'mods:namePart'));
		if ($namePartNode) {
			$nameReturner['namePart'] = $namePartNode->getValue();
			if (($namePartType = $namePartNode->getAttribute('type')) !== null) $nameReturner['namePartType'] = $namePartType;
		}
		foreach (array('displayForm', 'affiliation', 'description') as $nodeName) {
			$node =& $nameNode->getChildByName(array($nodeName, 'oai_mods:' . $nodeName, 'mods:' . $nodeName)) && $nameReturner[$nodeName] = $node->getValue();
			unset($node);
		}

		$roleNode =& $nameNode->getChildByName(array('role', 'oai_mods:role', 'mods:role'));
		if ($roleNode) {
			$nameReturner['roles'] = array();
			for ($j=0; $roleTermNode =& $roleNode->getChildByName(array('roleTerm', 'oai_mods:roleTerm', 'mods:roleTerm'), $j); $j++) {
				$roleTerm = array('term' => $roleTermNode->getValue());
				foreach (array('authority', 'type') as $name) {
					if (($value = $roleTermNode->getAttribute($name)) !== null) $roleTerm[$name] = $value;
				}
				$nameReturner['roles'][] =& $roleTerm;
				unset($roleTermNode, $roleTerm);
			}
		}
		return $nameReturner;
	}

	function &handleRootNode(&$modsNode) {
		// Handle titleInfo
		$titleInfoNode =& $modsNode->getChildByName(array('titleInfo', 'mods:titleInfo', 'oai_mods:titleInfo'));
		if (isset($titleInfoNode)) foreach($titleInfoNode->getChildren() as $child) {
			$returner[$child->getName(false)] = $child->getValue();
		}
		unset($titleInfoNode);

		// Handle "name" nodes
		for ($i=0; $nameNode =& $modsNode->getChildByName(array('name', 'oai_mods:name', 'mods:name'), $i); $i++) {
			$returner['names'][] =& $this->handleNameNode($nameNode);
			unset($nameNode);
		}

		// Handle typeOfResource
		$typeNode =& $modsNode->getChildByName(array('typeOfResource', 'mods:typeOfResource', 'oai_mods:typeOfResource'));
		if (isset($typeNode)) {
			$returner['typeOfResource'] = $typeNode->getValue();
			foreach (array('collection' => 'typeOfResourceCollection', 'manuscript' => 'typeOfResourceManuscript') as $sourceName => $targetName) {
				if (($value = $typeNode->getAttribute($sourceName)) !== null) $returner[$targetName] = $value;
			}
			
			unset($typeNode);
		}

		// Handle originInfo
		$originNode =& $modsNode->getChildByName(array('originInfo', 'mods:originInfo', 'oai_mods:originInfo'));
		if (isset($originNode)) {
			foreach ($originNode->getChildren() as $child) {
				$name = $child->getName(false);
				switch ($name) {
					case 'publisher':
					case 'edition':
					case 'issuance':
					case 'frequency':
						$returner['originInfo'][$name] = $child->getValue();
						break;
					case 'dateIssued':
					case 'dateCreated':
					case 'dateCaptured':
					case 'dateValid':
					case 'dateModified':
					case 'copyrightDate':
					case 'dateOther':
						$returner['originInfo'][$name]['value'] = $child->getValue();
						foreach (array('encoding', 'point', 'keyDate', 'qualifier', 'type') as $attributeName) if (($value = $child->getAttribute($attributeName)) !== null) $returner['originInfo'][$name][$attributeName] = $value;
						break;
					case 'place':
						for ($j=0; $placeTermNode =& $child->getChildByName(array('placeTerm', 'oai_mods:placeTerm', 'mods:placeTerm'), $j); $j++) {
							$placeTerm = array('term' => $placeTermNode->getValue());
							foreach (array('authority', 'type') as $name) {
								if (($value = $placeTermNode->getAttribute($name)) !== null) $placeTerm[$name] = $value;
							}
							$returner['originInfo']['places'][] =& $placeTerm;
							unset($placeTermNode, $placeTerm);
						}
						
						break;
					default: fatalError('Unknown node!');
				}
			}
			unset($originNode);
		}

		// Handle language
		$languageNode =& $modsNode->getChildByName(array('language', 'mods:language', 'oai_mods:language'));
		if (isset($languageNode)) {
			for ($j=0; $languageTermNode =& $child->getChildByName(array('languageTerm', 'oai_mods:languageTerm', 'mods:languageTerm'), $j); $j++) {
				$languageTerm = array('term' => $languageTermNode->getValue());
				foreach (array('authority', 'type') as $name) {
					if (($value = $placeTermNode->getAttribute($name)) !== null) $placeTerm[$name] = $value;
				}
				$returner['language'][] =& $languageTerm;
				unset($languageTermNode, $languageTerm);
			}
			unset($languageNode);
		}

		$physicalDescriptionNode =& $modsNode->getChildByName(array('physicalDescription', 'mods:physicalDescription', 'oai_mods:physicalDescription'));
		if (isset($physicalDescriptionNode)) {
			foreach ($physicalDescriptionNode->getChildren() as $child) {
				$name = $child->getName(false);
				switch ($name) {
					case 'form':
						$returner['form'] = $child->getValue();
						foreach (array('authority' => 'formAuthority', 'type' => 'formType') as $sourceName => $targetName) {
							if (($value = $child->getAttribute($sourceName)) !== null) $roleTerm[$targetName] = $value;
						}
						break;
					case 'reformattingQuality':
					case 'internetMediaType':
					case 'extent':
					case 'digitalOrigin':
						$returner[$name] = $child->getValue();
						break;
					case 'note':
						$returner['note'] = $child->getValue();
						foreach (array('type' => 'noteType', 'transliteration' => 'noteTransliteration', 'authority' => 'noteAuthority', 'script' => 'noteScript') as $sourceName => $targetName) {
							if (($value = $child->getAttribute($sourceName)) !== null) $returner[$targetName] = $value;
						}
						break;
					default:
						die('Unknown node!');
				}
			}
			unset($physicalDescriptionNode);
		}

		// Handle abstract, tableOfContents, etc.
		foreach (array('genre', 'abstract', 'tableOfContents', 'targetAudience', 'note', 'classification', 'accessCondition', 'extension') as $nodeName) {
			$node =& $modsNode->getChildByName(array($nodeName, 'mods:' . $nodeName, 'oai_mods:' . $nodeName));
			if (!isset($node)) continue;

			foreach (array('authority' => $nodeName . 'Authority', 'type' => $nodeName . 'Type', 'transliteration' => $nodeName . 'Transliteration', 'script' => $nodeName . 'Script', 'displayLabel' => $nodeName . 'DisplayLabel') as $sourceName => $targetName) {
				if (($value = $node->getAttribute($sourceName)) !== null) $returner[$targetName] = $value;
			}

			$returner[$nodeName] = $node->getValue();
			unset($node);
		}

		// Handle relatedItem
		for ($j=0; $relatedItemNode =& $modsNode->getChildByName(array('relatedItem', 'oai_mods:relatedItem', 'mods:relatedItem'), $j); $j++) {
			$returner['relatedItems'][] = $this->handleRootNode($relatedItemNode);
		}

		// Handle subject
		$subjectNode =& $modsNode->getChildByName(array('subject', 'mods:subject', 'oai_mods:subject'));
		if (isset($subjectNode)) {
			foreach (array('topic' => 'subjectTopic', 'geographic' => 'subjectGeographic', 'temporal' => 'subjectTemporal', 'geographicCode' => 'subjectGeographicCode', 'genre' => 'subjectGenre', 'occupation' => 'subjectOccupation') as $sourceNodeName => $targetNodeName) {
				$node =& $subjectNode->getChildByName(array($sourceNodeName, 'mods:' . $sourceNodeName, 'oai_mods:' . $sourceNodeName));
				if ($node) {
					$returner[$targetNodeName] = $node->getValue();
					unset($node);
				}
			}

			// Handle titleInfo
			$titleInfoNode =& $subjectNode->getChildByName(array('titleInfo', 'mods:titleInfo', 'oai_mods:titleInfo'));
			if (isset($titleInfoNode)) foreach($titleInfoNode->getChildren() as $child) {
				$returner['subjectTitleInfo'][$child->getName(false)] = $child->getValue();
			}
			unset($titleInfoNode);

			// Handle "name" nodes
			for ($i=0; $nameNode =& $subjectNode->getChildByName(array('name', 'oai_mods:name', 'mods:name'), $i); $i++) {
				$returner['subjectNames'][] =& $this->handleNameNode($nameNode);
				unset($nameNode);
			}

			// Handle hierarchicalGeographic and cartographics
			foreach (array('hierarchicalGeographic', 'cartographics') as $nodeName) {
				$node =& $subjectNode->getChildByName(array($nodeName, 'mods:' . $nodeName, 'oai_mods:' . $nodeName));
				if ($node) {
					$returner['subject'][$nodeName][] = $node->getValue();
					unset($node);
				}
			}
			unset($subjectNode);
		}

		// Handle identifier
		$identifierNode =& $modsNode->getChildByName(array('identifier', 'mods:identifier', 'oai_mods:identifier'));
		if (isset($identifierNode)) {
			$returner['identifier'] = $identifierNode->getValue();
			foreach (array('type' => 'identifierType', 'displayLabel' => 'identifierDisplayLabel', 'invalid' => 'identifierInvalid') as $sourceName => $targetName) {
				$value = $identifierNode->getAttribute($sourceName);
				if ($value != '') $returner[$targetName] = $value;
			}
			unset($identifierNode);
		}

		// Handle location
		for ($i=0; $locationNode =& $modsNode->getChildByName(array('location', 'mods:location', 'oai_mods:location'), $i); $i++) {
			$locationReturner = array();
			$physicalLocationNode =& $locationNode->getChildByName(array('physicalLocation', 'mods:physicalLocation', 'oai_mods:physicalLocation'));
			if ($physicalLocationNode) {
				$locationReturner['physicalLocation'] = $physicalLocationNode->getValue();
				foreach (array('authority' => 'physicalLocationAuthority', 'displayLabel' => 'physicalLocationDisplayLabel', 'type' => 'physicalLocationType') as $sourceName => $targetName) {
					$value = $physicalLocationNode->getAttribute($sourceName);
					if ($value != '') $locationReturner[$targetName] = $value;
				}
				unset($physicalLocationNode);
			}

			foreach (array('shelfLocator', 'holdingExternal', 'url') as $name) {
				$node =& $locationNode->getChildByName(array($name, 'mods:' . $name, 'oai_mods:' . $name));
				if ($node) {
					$locationReturner[$name] = $node->getValue();
					unset($node);
				}
			}

			$returner['locations'][] =& $locationReturner;
			unset($locationReturner, $locationNode, $physicalLocationNode);
		}

		// FIXME: Handle part, recordInfo

		unset($result, $xmlParser);
		return $returner;
	}

	/**
	 * Get the value of a field by symbolic name.
	 * @var $record object
	 * @var $name string
	 * @var $type SORT_ORDER_TYPE_...
	 * @return mixed
	 */
	function getFieldValue(&$record, $name, $type) {
		$returner = null;
		$parsedContents = $record->getParsedContents();
		switch ($name) {
			case 'title':
			case 'subTitle':
			case 'partNumber':
			case 'partName':
			case 'nonSort':
			case 'typeOfResource':
			case 'language':
			case 'form':
			case 'reformattingQuality':
			case 'internetMediaType':
			case 'extent':
			case 'digitalOrigin':
			case 'note':
			case 'genre':
			case 'abstract':
			case 'tableOfContents':
			case 'targetAudience':
			case 'note':
			case 'classification':
			case 'accessCondition':
			case 'extension':
			case 'subjectTopic':
			case 'subjectGeographic':
			case 'subjectTemporal':
			case 'subjectGeographicCode':
			case 'subjectGenre':
			case 'subjectOccupation':
			case 'subjectTitleInfo':
			case 'identifier':
				if (isset($parsedContents[$name])) {
					$returner = $parsedContents[$name];
				}
				break;
			case 'name':
			case 'displayForm':
				if (isset($parsedContents['names'])) $returner = $this->collapse($parsedContents['names']);
				break;
			case 'publisher':
			case 'edition':
			case 'issuance':
			case 'frequency':
				if (isset($parsedContents['originInfo'][$name])) {
					$returner = $parsedContents['originInfo'][$name];
				}
				break;
			case 'dateIssued':
			case 'dateCreated':
			case 'dateCaptured':
			case 'dateValid':
			case 'dateModified':
			case 'copyrightDate':
			case 'dateOther':
				if (isset($parsedContents['originInfo'][$name]['value'])) {
					$returner = $parsedContents['originInfo'][$name]['value'];
				}
				break;
			case 'place':
				if (isset($parsedContents['places'])) foreach ($parsedContents['places'] as $placeValue) {
					$returner .= join('; ', $placeValue);
				}
				break;
			case 'relatedItem':
				// Simply dump all related item pieces into a
				// single string.
				if (isset($parsedContents['relatedItems'])) $returner = $this->collapse($parsedContents['relatedItems']);
				break;
			case 'location':
				// Simply dump all related item pieces into a
				// single string.
				if (isset($parsedContents['locations'])) $returner = $this->collapse($parsedContents['locations']);
				break;
		}
		if ($returner === null) return $returner;

		// Handle type here
		if ($type === SORT_ORDER_TYPE_DATE) $returner = strtotime($returner);

		return $returner;
	}

	function collapse($val) {
		if (is_array($val)) foreach ($val as $subval) {
			return $this->collapse($subval) . '; ';
		}
		return $val;
	}

	function getFieldType($name) {
		switch ($name) {
			case 'dateIssued':
			case 'dateCreated':
			case 'dateCaptured':
			case 'dateValid':
			case 'dateModified':
			case 'copyrightDate':
			case 'dateOther':
			case 'recordCreationDate':
			case 'recordChangeDate':
				return FIELD_TYPE_DATE;
			case 'language':
				return FIELD_TYPE_SELECT;
			default:
				return FIELD_TYPE_STRING;
		}
	}

	function getMetadataPrefix() {
		return 'oai_mods';
	}

	function getFormatClass() {
		$this->import('OAIMetadataFormat_MODS');
		return 'OAIMetadataFormat_MODS';
	}

	function getSchemaName() {
		return 'http://www.loc.gov/standards/mods//v3/mods-3-3.xsd';
	}

	function getNamespace() {
		return 'http://www.loc.gov/mods/v3';
	}
}

?>
