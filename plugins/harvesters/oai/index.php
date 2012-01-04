<?php

/**
 * @file plugins/harvesters/oai/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Wrapper for OAI harvester plugin.
 *
 * @package plugins.harvesters.oai
 *
 * $Id$
 */

require_once('OAIHarvesterPlugin.inc.php');

return new OAIHarvesterPlugin();

?>
