<?php

/**
 * @file plugins/schemas/marc/OAIMetadataFormat_MODS.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_MODS
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- MODS.
 */

// $Id$


class OAIMetadataFormat_MODS extends OAIMetadataFormat {
	/**
	 * @see OAIMetadataFormat#toXml
	 */
	function toXml(&$oaiRecord, $format = null) {
		$record =& $oaiRecord->getData('record');

		switch ($format) {
			case 'oai_dc':
				static $xslDoc, $proc;
				if (!isset($xslDoc) || !isset($proc)) {
					// Cache the XSL
					$xslDoc = new DOMDocument();
					$xslDoc->load('http://www.loc.gov/standards/mods/MODS3-22simpleDC.xsl');
					$proc = new XSLTProcessor();
					$proc->importStylesheet($xslDoc);
				}

				$xmlDoc = new DOMDocument();
				$xmlDoc->loadXML($record->getContents());
				$xml = $proc->transformToXML($xmlDoc);
				// Cheesy: strip the XML header
				if (($pos = strpos($xml, '<oai_dc:dc')) > 0) {
					$xml = substr($xml, $pos);
				}
				return $xml;
			case 'oai_mods':
			case 'mods':
				return $record->getContents();
			default:
				fatalError("Unable to convert MODS to $format!\n");
		}
	}
}

?>
