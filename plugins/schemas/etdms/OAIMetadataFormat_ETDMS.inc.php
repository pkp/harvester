<?php

/**
 * @file plugins/schemas/marc/OAIMetadataFormat_ETDMS.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_ETDMS
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- ETDMS.
 */

// $Id$


class OAIMetadataFormat_ETDMS extends OAIMetadataFormat {
	/**
	 * @see OAIMetadataFormat#toXml
	 */
	function toXml(&$oaiRecord, $format = null) {
		$record =& $oaiRecord->getData('record');

		switch ($format) {
			case 'oai_dc':
				// FIXME: This is almost certainly not correct
				return $record->getContents();
			case 'oai_etdms':
				return $record->getContents();
			default:
				fatalError("Unable to convert ETDMS to $format!\n");
		}
	}
}

?>
