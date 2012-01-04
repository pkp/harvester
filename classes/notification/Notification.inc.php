<?php

/**
 * @file classes/notification/Notification.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSNotification
 * @ingroup notification
 * @see NotificationDAO
 * @brief Harvester subclass for Notifications
 */

// $Id$


import('lib.pkp.classes.notification.PKPNotification');
import('lib.pkp.classes.notification.NotificationDAO');

class Notification extends PKPNotification {
	/**
	 * Constructor.
	 */
	function Notification() {
		parent::PKPNotification();
	}
}

?>
