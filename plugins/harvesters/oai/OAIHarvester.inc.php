<?php

/**
 * OAIHarvester.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * OAI Harvester
 *
 * $Id$
 */

import('harvester.XMLHarvester');

class OAIHarvester extends XMLHarvester {
	/** @var $notInHeader boolean */
	var $notInHeader;

	/** @var $characterData string */
	var $characterData;

	/** @var $responseDate string */
	var $responseDate;

	function startElement(&$parser, $tag, $attributes) {
		$this->characterData = null;
		if (!isset($notInHeader) || !$notInHeader) {
			// Non-metadata tag starts.
		} else {
			// Metadata tag starts.
		}
	}

	function endElement(&$parser, $tag) {
		if (!isset($notInHeader) || !$notInHeader) {
			// Non-metadata tag ends.
			switch ($tag) {
				case 'responseDate':
					$this->responseDate = $this->characterData;
					break;
				case 'error':
			}
		} else {
			// Metadata tag ends.
		}
		$this->characterData = null;
	}

	function characterData(&$parser, $data) {
		if ($this->characterData === null) {
			$this->characterData = '';
		}
		$this->characterData .= $data;
	}

}

?>
