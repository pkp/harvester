<?php

/**
 * @file plugins/harvesters/junk/index.php
 *
 * Copyright (c) 2005-2009 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Wrapper for Junk harvester plugin.
 *
 * @package plugins.harvesters.junk
 *
 * $Id$
 */

require_once('JunkHarvesterPlugin.inc.php');

return new JunkHarvesterPlugin();

?>
