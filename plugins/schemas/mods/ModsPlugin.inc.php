<?php

/**
 * @file ModsPlugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.mods
 * @class ModsPlugin
 *
 * MODS schema plugin
 *
 * $Id$
 */

import('plugins.SchemaPlugin');

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
		return Locale::translate('plugins.schemas.mods.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.schemas.mods.description');
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array(
				'identifier',
				'title',
				'subTitle',
				'partNumber',
				'partName',
				'nonSort',
				'namePart',
				'displayForm',
				'affiliation',
				'role',
				'roleTerm',
				'typeOfResource',
				'genre',
				'placeTerm',
				'publisher',
				'dateIssued',
				'dateCreated',
				'dateCaptured',
				'dateValid',
				'dateModified',
				'copyrightDate',
				'dateOther',
				'recordCreationDate',
				'recordChangeDate',
				'edition',
				'issuance',
				'frequency',
				'languageTerm',
				'form',
				'reformattingQuality',
				'internetMediaType',
				'extent',
				'digitalOrigin',
				'physicalDescriptionNote',
				'abstract',
				'tableOfContents',
				'targetAudience',
				'topic',
				'geographic',
				'temporal',
				'geographicCode',
				'continent',
				'country',
				'province',
				'region',
				'state',
				'territory',
				'county',
				'city',
				'island',
				'area',
				'scale',
				'projection',
				'coordinates',
				'occupation',
				'classification',
				'physicalLocation',
				'url',
				'accessCondition',
				'extension',
				'recordContentSource',
				'recordIdentifier',
				'recordOrigin',
				'languageOfCataloging',
				'note',
				'titleInfo',
				'relatedItem',
				'subject',
				'name',
				'list',
				'start',
				'part',
				'originInfo'
			);
		}
		return $fieldList;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.mods.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.mods.fields.$fieldSymbolic.description", $locale);
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

		$modsNode =& $result->getChildByName(array('oai_mods:mods', 'mods:mods', 'mods'));
		if (!isset($modsNode)) {
			$returner = null;
			return $returner;
		}

		$returner =& $this->handleRootNode($modsNode);
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
				$returner['languages'][] =& $languageTerm;
				unset($languageTermNode, $languageTerm);
			}
			unset($languageNode);
		}

		$physicalDescriptionNode =& $modsNode->getChildByName(array('physicalDescription', 'mods:physicalDescription', 'oai_mods:physicalDescription'));
		if (isset($physicalDescriptionNode)) {
			foreach ($originNode->getChildren() as $child) {
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

			foreach (array('shelfLocator', 'holdingExternal') as $name) {
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
		$fieldValue = null;
		$parsedContents = $record->getParsedContents();
		if (isset($parsedContents[$name])) switch ($type) {
			case SORT_ORDER_TYPE_STRING:
				$fieldValue = join(';', $parsedContents[$name]);
				break;
			case SORT_ORDER_TYPE_NUMBER:
				$fieldValue = (int) array_shift($parsedContents[$name]);
				break;
			case SORT_ORDER_TYPE_DATE:
				$fieldValue = strtotime($thing = array_shift($parsedContents[$name]));
				if ($fieldValue === -1 || $fieldValue === false) $fieldValue = null;
				break;
			default:
				fatalError('UNKNOWN TYPE');
		}
		HookRegistry::call('DublinCorePlugin::getFieldValue', array(&$this, &$fieldValue));
		return $fieldValue;
	}

	/**
	 * Get the authors for the supplied record, if available; null otherwise
	 * @param $record object
	 * @param $entries array
	 * @return array
	 */
/*	function getAuthors(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);
		return $authors;
	}*/

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
/*	function getTitle(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);
		return $title;
	}*/

	/**
	 * Display a record summary.
	 */
/*	function displayRecordSummary(&$record) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('record', $record);

		$entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);

		$templateMgr->assign('title', $title);
		$templateMgr->assign('authors', $authors);
		$templateMgr->assign('url', $this->getUrl($record, $entries));

		$templateMgr->display($this->getTemplatePath() . 'summary.tpl', null);
	}*/

	/**
	 * Display a record.
	 */
/*	function displayRecord(&$record) {
		$templateMgr =& TemplateManager::getManager();

		$entries = $record->getEntries();
		$archive =& $record->getArchive();
		if (!$archive || !$archive->getEnabled()) return false;

		list($version, $defineTermsContextId) = $this->getRtVersion($archive);
		if ($version) {
			$templateMgr->assign('sidebarTemplate', 'rt/rt.tpl');
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign('defineTermsContextId', $defineTermsContextId);
		}

		function displayEntry(&$returner, &$entries, &$entry, $entryId, $indent = 0) {
			$fieldName = Locale::translate('plugins.schemas.mods.fields.' . $entry['name'] . '.name');
			$pad = str_repeat('&nbsp;&nbsp;', $indent);

			switch ($entry['name']) {
				case 'titleInfo':
				case 'relatedItem':
					$returner .= '<tr><td colspan="2"><h5>' . $pad . $fieldName . '</h5></td></tr>';
					break;
				default:
					$value = trim($entry['value']);
					if ($indent == 0) $returner .= "<tr><td><strong>$fieldName</strong></td><td>$value</td></tr>\n";
					else $returner .= "<tr><td>$pad$fieldName</td><td>$value</td></tr>\n";
			}

			foreach (array_keys($entries) as $childEntryId) {
				if ($entries[$childEntryId]['parent_entry_id'] == $entryId) {
					displayEntry($returner, $entries, $entries[$childEntryId], $childEntryId, $indent + 1);
				}
			}
		}

		$recordHtml = '';
		$entries = $this->flattenEntries($record->getEntries());
		foreach ($entries as $entryId => $junk) {
			if (!$entries[$entryId]['parent_entry_id']) displayEntry($recordHtml, $entries, $entries[$entryId], $entryId);
		}

		$templateMgr->assign_by_ref('recordHtml', $recordHtml);
		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->assign_by_ref('archive', $archive);
		$templateMgr->display($this->getTemplatePath() . 'record.tpl', null);
		return true;
	}*/

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
			case 'languageTerm':
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
