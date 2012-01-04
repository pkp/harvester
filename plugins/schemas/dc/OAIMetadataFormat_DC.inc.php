<?php

/**
 * @defgroup oai_format
 */

/**
 * @file plugins/oaiMetadata/dc/OAIMetadataFormat_DC.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_DC
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- Dublin Core.
 */
 
class OAIMetadataFormat_DC extends OAIMetadataFormat {
	/**
	 * @see OAIMetadataFormat#toXml
	 */
	function toXml(&$oaiRecord, $format = null) {
		$record =& $oaiRecord->getData('record');

		switch ($format) {
			case 'oai_dc':
				return $record->getContents();
			default:
				fatalError("Unable to convert DC to $format!\n");
		}
	}
}

?>
