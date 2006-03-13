<?php

/**
 * OAIXMLHandler.inc.php
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

import('schema.SchemaMap');

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

	/** @var $delegatedParser object If set, the object that will supercede this one for parsing */
	var $delegatedParser;

	/** @var $requestParams array The request parameters
	var requestParams;

	/** @var $result mixed The result of this parsing operation */
	var $result;

	/** @var $verb string The OAI verb being processed */
	var $verb;

	/** @var $recordDao object */
	var $recordDao;

	function OAIXMLHandler(&$oaiHarvester, $verb) {
		$this->oaiHarvester =& $oaiHarvester;
		$this->header = array();

		switch ($verb) {
			case 'ListMetadataFormats':
				$this->result = array();
				break;
			default:
				$this->result = true;
				break;
		}

		$this->requestParams = array();
		$this->verb = $verb;
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
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
				$metadataFormat = $this->oaiHarvester->getMetadataFormat();
				$schemaPlugin = SchemaMap::getSchemaPlugin(OAIHarvesterPlugin::getName(), $metadataFormat);
				$this->delegatedParser =& $schemaPlugin->getXMLHandler($this->oaiHarvester);
				break;
			case 'OAI-PMH':
			case 'responseDate':
			case 'identifier':
			case 'record':
			case 'resumptionToken':
			case 'datestamp':
			case 'setSpec':
			case 'requestURL': // (OAI 1.1)
			case 'metadataFormat':
			case 'metadataPrefix':
			case 'schema':
			case 'metadataNamespace':
			case 'error':
				// Do nothing.
				break;
			case 'request':
				$this->requestParams = $attributes;
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
			case 'header':
				unset($this->header);
				$this->header = array();
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
			case 'header':
				$schema =& $this->oaiHarvester->getSchema();
				$record =& $this->oaiHarvester->getRecordByIdentifier($this->header['identifier']);
				if (!$record) {
					// This is a new record.
					$record =& new Record();
					$archive =& $this->oaiHarvester->getArchive();
					$record->setIdentifier($this->header['identifier']);
					$record->setArchiveId($archive->getArchiveId());
					$record->setSchemaId($schema->getSchemaId());
					$record->setDatestamp($this->header['datestamp']);
					$this->recordDao->insertRecord($record);
				} else {
					// This is an old record: Delete old entries and indexing
					$searchDao =& DAORegistry::getDAO('SearchDAO');
					$searchDao->deleteRecordObjects($record->getRecordId());
					$this->recordDao->deleteEntriesByRecordId($record->getRecordId());
					$record->setDatestamp($this->header['datestamp']);
					$this->recordDao->updateRecord($record);
				}

				$this->oaiHarvester->setRecord($record);

				if ($this->verb === 'ListIdentifiers') {
					// Harvesting via ListIdentifiers: we need to separately request
					// each record.
					$this->result =& $this->oaiHarvester->updateRecord($this->header['identifier'], true);
				}
				break;
			case 'OAI-PMH':
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
			case 'requestURL': // (OAI 1.1)
				$this->request = $this->characterData;
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
			case 'metadataFormat':
			case 'schema':
			case 'metadataNamespace':
				// Do nothing.
				break;
			case 'metadataPrefix':
				if ($this->verb == 'ListMetadataFormats') {
					$this->result[] = $this->characterData;
				}
				break;
			case 'record':
				// Update the index for this record
				import('search.SearchIndex');
				$record =& $this->oaiHarvester->getRecord();
				SearchIndex::indexRecord($record);
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

	/**
	 * Some request types will store a result in the $this->result
	 * variable; it can be fetched using this function.
	 * @return mixed
	 */
	function &getResult() {
		return $this->result;
	}
}

?>
