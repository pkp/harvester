<?php

/**
 * @file NullXMLHandler.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package xml
 * @class NullXMLHandler
 *
 * Dummy XML parser
 *
 * $Id$
 */

class NullXMLHandler extends XMLParserHandler {
	/**
	 * Constructor
	 */
	function NullXMLHandler() {
	}

	function startElement(&$parser, $tag, $attributes) {
	}

	function endElement(&$parser, $tag) {
	}

	function characterData(&$parser, $data) {
	}
}

?>
