<?php

/**
 * XMLHarvester.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package harvester
 *
 * Generic XML Harvester
 *
 * $Id$
 */

import('xml.XMLParser');

class XMLHarvester extends XMLParserHandler {
	/** @var $parser XMLParser */
	var $parser;

	/**
	 * Constructor
	 */
	function XMLHarvester() {
		$this->parser =& new XMLParser();
		$this->parser->setHandler($this);
	}

	/**
	 * Harvest from the supplied URL.
	 */
	function harvest($url) {
		$this->parser->parse($url);
	}

}

?>
