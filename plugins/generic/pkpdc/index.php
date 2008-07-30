<?php

/**
 * @file plugins/generic/pkpdc/index.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Wrapper for PKP extension to Dublin Core schema plugin.
 *
 * @package plugins.generic.pkpdc
 *
 * $Id$
 */

require_once('PKPDublinCorePlugin.inc.php');

return new PKPDublinCorePlugin();

?>
