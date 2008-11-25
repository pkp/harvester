<?php

/**
 * @file plugins/schemas/marc/OAIMetadataFormat_MARC.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_MARC
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- MARC.
 */

// $Id$


class OAIMetadataFormat_MARC extends OAIMetadataFormat {
	/**
	 * @see OAIMetadataFormat#toXml
	 */
	function toXml(&$oaiRecord, $format = null) {
		$record =& $oaiRecord->getData('record');

		switch ($format) {
			case 'oai_dc':
				fatalError('IMPLEMENT ME');
			case 'oai_marc':
			case 'marcxml':
				return $record->getContents();
			default:
				fatalError("Unable to convert MARC to $format!\n");
		}
	}
}

?>
