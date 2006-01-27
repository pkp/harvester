<?php

/**
 * OAIXMLHandler.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * OAI XML parser
 *
 * $Id$
 */

class OAIXMLHandler extends XMLParserHandler {
	/** @var $characterData string */
	var $characterData;

	/** @var $responseDate string */
	var $responseDate;

	/** @var $request string */
	var $request;

	/** @var $responseType string OAI Verb */
	var $responseType;

	/** @var $depth int Depth in nested XML nodes, once out of headers */
	var $depth = 0;

	/** @var $oaiHarvester object */
	var $oaiHarvester;

	/** @var $header array */
	var $header;

	/** @var $metadata array */
	var $metadata;

	/** @var $delegatedParser object If set, the object that will supercede this one for parsing */
	var $delegatedParser;

	function OAIXMLHandler(&$oaiHarvester) {
		$this->oaiHarvester =& $oaiHarvester;
		$this->header = array();
		$this->metadata = array();
	}

	function startElement(&$parser, $tag, $attributes) {
		if (isset($this->delegatedParser)) {
			$this->depth++;
			return $this->delegatedParser->startElement($parser, $tag, $attributes);
		}

		unset($this->characterData);
		$this->characterData = null;

		switch ($tag) {
			case 'metadata':
				// Delegate metadata processing to another parser
				$this->depth = 0;
				switch ($metadataFormat = $this->oaiHarvester->getMetadataFormat()) {
					case 'oai_dc':
						import('harvester.DublinCoreXMLHandler');
						$this->delegatedParser =& new DublinCoreXMLHandler($this->oaiHarvester, $this->metadata);
						break;
					default:
						fatalError('Unknown metadata format ' . $metadataFormat);
						break;
				}

				break;
			case 'OAI-PMH':
			case 'responseDate':
			case 'identifier':
			case 'record':
			case 'resumptionToken':
			case 'datestamp':
			case 'setSpec':
				// Do nothing.
				break;
			case 'request':
				$this->oaiHarvester->setRequestParams($attributes);
				break;
			case 'GetRecord':
			case 'Identify':
			case 'ListIdentifiers':
			case 'ListMetadataFormats':
			case 'ListRecords':
			case 'ListSets':
				$this->responseType = $tag;
				$this->notInHeader = true;
				break;
				break;
			case 'header':
				unset($this->header);
				$this->header = array();
				break;
			case 'metadata':
				unset($this->metadata);
				$this->metadata = array();
				break;
			default:
				$this->oaiHarvester->addError(Locale::translate('plugins.harvesters.oai.errors.unknownHeaderTag', array('tag' => $tag)));
		}
	}

	function endElement(&$parser, $tag) {
		if (isset($this->delegatedParser)) {
			if (--$this->depth < 0) {
				unset($this->delegatedParser);
				$this->delegatedParser = null;
			} else {
				return $this->delegatedParser->endElement($parser, $tag);
			}
		}

		switch ($tag) {
			case 'OAI-PMH':
			case 'header':
			case 'metadata':
				// Do nothing.
				break;
			case 'responseDate':
				$this->oaiHarvester->setResponseDate($this->oaiHarvester->UTCtoTimestamp($this->characterData));
				break;
			case 'error':
				$this->oaiHarvester->addError($this->characterData);
				break;
			case 'request':
				$this->oaiHarvester->setRequest($this->characterData);
				break;
			case 'setSpec':
			case 'identifier':
				$this->header[$tag] = $this->characterData;
				break;
			case 'datestamp':
				$this->header[$tag] = $this->oaiHarvester->UTCtoTimestamp($this->characterData);
				break;
			case 'GetRecord':
			case 'Identify':
			case 'ListIdentifiers':
			case 'ListMetadataFormats':
			case 'ListRecords':
			case 'ListSets':
				// Do nothing.
				break;
			case 'record':
				$record =& $this->oaiHarvester->getRecordByIdentifier($this->header['identifier']);
				if (!$record) {
					// This is a new record.
					$record =& new Record();
					$archive =& $this->oaiHarvester->getArchive();
					$record->setIdentifier($this->header['identifier']);
					$record->setArchiveId($archive->getArchiveId());
					$record->setDatestamp(Core::getCurrentDate());
					$this->oaiHarvester->insertRecord($record);
				} else {
					// This is an old record: Delete old
					// entires. FIXME: Indexing?
					$this->oaiHarvester->deleteEntries($record);
				}

				$record->setDatestamp($this->header['datestamp']);
				foreach ($this->metadata as $name => $value) {
					$field =& $this->oaiHarvester->getFieldByKey($name);
					$this->oaiHarvester->addEntry($record, $field, $value);
				}

				break;
			case 'resumptionToken':
				// Received a resumption token. Fetch the next set
				$token = $this->characterData;
				if (!empty($token)) {
					$this->oaiHarvester->handleResumptionToken($token);
				}
				break;
			default:
				$this->oaiHarvester->addError(Locale::translate('plugins.harvesters.oai.errors.unknownHeaderTag', array('tag' => $tag)));
				break;
		}
	}

	function characterData(&$parser, $data) {
		if (isset($this->delegatedParser)) {
			return $this->delegatedParser->characterData($parser, $data);
		}

		if ($this->characterData === null) {
			$this->characterData = '';
		}
		$this->characterData .= $data;
	}

	function &getResult() {
		// Because indexing is done as the XML is parsed (for the obvious
		// reasons -- efficiency), no actual result need be returned save
		// a "true" to indicate that parsing was actually successful.
		$result = true;
		return $result;
	}
}

?>
