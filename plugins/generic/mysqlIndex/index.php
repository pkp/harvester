<?php

/**
 * @defgroup plugins_generic_mysqlIndex
 */
 
/**
 * @file plugins/generic/mysqlIndex/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_mysqlIndex
 * @brief Wrapper for MySQL Index plugin.
 *
 */

// $Id$


require_once('MysqlIndexPlugin.inc.php');

return new MysqlIndexPlugin();

?> 
