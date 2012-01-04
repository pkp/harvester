<?php

/**
 * @file classes/core/Handler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Handler
 * @ingroup core
 *
 * @brief Base request handler application class
 */


import('lib.pkp.classes.handler.PKPHandler');

class Handler extends PKPHandler {
	function Handler() {
		parent::PKPHandler();
	}
}

?>
