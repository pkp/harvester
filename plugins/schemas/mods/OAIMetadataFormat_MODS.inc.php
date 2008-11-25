<?php

/**
 * @file plugins/schemas/marc/OAIMetadataFormat_MODS.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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
				fatalError('IMPLEMENT ME');
			case 'oai_mods':
			case 'mods':
				return $record->getContents();
			default:
				fatalError("Unable to convert MODS to $format!\n");
		}
	}
}

?>
