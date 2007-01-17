<?php

/**
 * @file plugins/harvesters/oai/index.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Wrapper for OAI harvester plugin.
 *
 * @package plugins.harvesters.oai
 *
 * $Id$
 */

require('OAIHarvesterPlugin.inc.php');

return new OAIHarvesterPlugin();

?>
