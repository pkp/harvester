<?php

/**
 * @file plugins/harvesters/junk/index.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Wrapper for Junk harvester plugin.
 *
 * @package plugins.harvesters.junk
 *
 * $Id$
 */

require('JunkHarvesterPlugin.inc.php');

return new JunkHarvesterPlugin();

?>
