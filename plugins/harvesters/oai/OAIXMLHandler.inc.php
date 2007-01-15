<?php

/**
 * @file OAIXMLHandler.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.harvesters.oai
 * @class OAIXMLHandler
 *
 * OAI XML parser
 *
 * $Id$
 */

import('schema.SchemaMap');

class OAIXMLHandler extends XMLParserHandler {
	/** @var $params array */
	var $params;

	/** @var $recordCount int */
	var $recordCount;

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

	function OAIXMLHandler(&$oaiHarvester, $verb, $params = array(), $recordOffset = 0) {
		$this->oaiHarvester =& $oaiHarvester;
		$this->header = array();
		$this->params =& $params;
		$this->recordCount = $recordOffset;

		switch ($verb) {
			case 'ListMetadataFormats':
			case 'ListSets':
			case 'Identify':
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
			case 'requestURL': // (OAI 1.1)
			case 'metadataFormat':
			case 'metadataPrefix':
			case 'schema':
			case 'metadataNamespace':
			case 'error':
			case 'set':
			case 'setName':
			case 'setSpec':
			case 'setDescription':
			case 'repositoryName':
			case 'baseUrl':
			case 'protocolVersion':
			case 'adminEmail':
			case 'earliestDatestamp':
			case 'deletedRecord':
			case 'granularity':
			case 'compression':
			case 'description':
			case 'oai-identifier':
			case 'scheme':
			case 'repositoryIdentifier':
			case 'delimiter':
			case 'sampleIdentifier':
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
				if (isset($attributes['status'])) {
					$this->header['status'] = $attributes['status'];
				}
				break;
			default:
				$this->oaiHarvester->addError(Locale::translate('plugins.harvesters.oai.errors.unknownHeaderTag', array('tag' => $tag)));
		}
	}

	function endElement(&$parser, $tag) {
		$isDeletion = $this->header['status'] == 'deleted';

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
					if (!$isDeletion) {
						// This is a new record.
						$record =& new Record();
						$archive =& $this->oaiHarvester->getArchive();
						$record->setIdentifier($this->header['identifier']);
						$record->setArchiveId($archive->getArchiveId());
						$record->setSchemaId($schema->getSchemaId());
						$record->setDatestamp($this->header['datestamp']);
						$this->recordDao->insertRecord($record);
					}
				} elseif ($isDeletion) {
					$this->recordDao->deleteRecord($record);
					unset($record);
					$record = null;
				} else {
					if (isset($this->params['skipExistingEntries'])) {
						// Make sure the record isn't overwritten
						unset($record);
						$record = null;
					} else {
						// This is an old record: Delete old entries and indexing
						if (!isset($this->params['skipIndexing'])) {
							$searchDao =& DAORegistry::getDAO('SearchDAO');
							$searchDao->deleteRecordObjects($record->getRecordId());
						}
						$this->recordDao->deleteEntriesByRecordId($record->getRecordId());

						// Prepare to re-harvest an existing record.
						$record->setDatestamp($this->header['datestamp']);
						$this->recordDao->updateRecord($record);
					}
				}

				// Give the record object to the designated XML parser (i.e. for the schema
				// in question). If this is a deletion, $record will be null.
				$this->oaiHarvester->setRecord($record);

				if ($this->verb === 'ListIdentifiers' && !$isDeletion) {
					// Harvesting via ListIdentifiers: we need to separately request
					// each record.
					$this->result =& $this->oaiHarvester->updateRecord($this->header['identifier'], $this->params);
				}

				if (isset($this->params['callback']) && ++$this->recordCount % 50 == 0) {
					call_user_func($this->params['callback'], $this->recordCount . " records indexed.");
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
				$this->oaiHarvester->setStatus(false);
				break;
			case 'request':
			case 'requestURL': // (OAI 1.1)
				$this->request = $this->characterData;
				break;
			case 'setSpec':
			case 'setName':
			case 'setDescription':
			case 'identifier':
				$this->header[$tag] = $this->characterData;
				break;
			case 'datestamp':
				$this->header[$tag] = $this->oaiHarvester->UTCtoTimestamp($this->characterData);
				break;
			case 'Identify':
			case 'GetRecord':
			case 'ListIdentifiers':
			case 'ListMetadataFormats':
			case 'ListRecords':
			case 'ListSets':
			case 'metadataFormat':
			case 'schema':
			case 'metadataNamespace':
				// Do nothing.
				break;
			case 'set':
				if ($this->verb == 'ListSets') {
					$this->result[$this->header['setSpec']] = $this->header['setName'];
				}
				break;
			case 'metadataPrefix':
				if ($this->verb == 'ListMetadataFormats') {
					$this->result[] = $this->characterData;
				}
				break;
			case 'record':
				if (!isset($this->params['skipIndexing'])) {
					// Update the index for this record
					import('search.SearchIndex');
					$record =& $this->oaiHarvester->getRecord();
					$archive =& $this->oaiHarvester->getArchive();
					if ($record) SearchIndex::indexRecord($archive, $record);
				}
				break;
			case 'resumptionToken':
				// Received a resumption token. Fetch the next set
				$token = $this->characterData;
				if (!empty($token)) {
					$this->oaiHarvester->handleResumptionToken($token, $this->params, $this->recordCount);
				}
				break;
			case 'repositoryName':
			case 'baseUrl':
			case 'protocolVersion':
			case 'adminEmail':
			case 'earliestDatestamp':
			case 'deletedRecord':
			case 'granularity':
			case 'compression':
			case 'description':
			case 'oai-identifier':
			case 'scheme':
			case 'repositoryIdentifier':
			case 'delimiter':
			case 'sampleIdentifier':
				if ($this->verb == 'Identify') $this->result[$tag] = $this->characterData;
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
