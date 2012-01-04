<?php

/**
 * @file classes/mail/EmailTemplateDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateDAO
 * @ingroup mail
 * @see EmailTemplate
 *
 * @brief Operations for retrieving and modifying Email Template objects.
 */

// $Id$


import('lib.pkp.classes.mail.PKPEmailTemplateDAO');
import('lib.pkp.classes.mail.EmailTemplate');

class EmailTemplateDAO extends PKPEmailTemplateDAO {
	/**
	 * Retrieve a base email template by key.
	 * @param $emailKey string
	 * @return BaseEmailTemplate
	 */
	function &getBaseEmailTemplate($emailKey) {
		$returner =& parent::getBaseEmailTemplate($emailKey, 0, 0);
		return $returner;
	}

	/**
	 * Retrieve localized email template by key.
	 * @param $emailKey string
	 * @return LocaleEmailTemplate
	 */
	function &getLocaleEmailTemplate($emailKey) {
		$returner =& parent::getLocaleEmailTemplate($emailKey, 0, 0);
		return $returner;
	}

	/**
	 * Retrieve an email template by key.
	 * @param $emailKey string
	 * @param $locale string
	 * @return EmailTemplate
	 */
	function &getEmailTemplate($emailKey, $locale) {
		$returner =& parent::getEmailTemplate($emailKey, $locale, 0, 0);
		return $returner;
	}

	/**
	 * Delete an email template by key.
	 * @param $emailKey string
	 */
	function deleteEmailTemplateByKey($emailKey) {
		return parent::deleteEmailTemplateByKey($emailKey, 0, 0);
	}

	/**
	 * Retrieve all email templates.
	 * @param $locale string
	 * @param $rangeInfo object optional
	 * @return array Email templates
	 */
	function &getEmailTemplates($locale, $rangeInfo = null) {
		$returner =& parent::getEmailTemplates($locale, 0, 0, $rangeInfo);
		return $returner;
	}

	/**
	 * Check if a template exists with the given email key.
	 * @param $emailKey string
	 * @return boolean
	 */
	function templateExistsByKey($emailKey) {
		return parent::templateExistsByKey($emailKey, 0, 0);
	}

	/**
	 * Check if a custom template exists with the given email key for a journal.
	 * @param $emailKey string
	 * @return boolean
	 */
	function customTemplateExistsByKey($emailKey) {
		return parent::customTemplateExistsByKey($emailKey, 0, 0);
	}
}

?>
