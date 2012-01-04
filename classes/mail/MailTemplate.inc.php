<?php

/**
 * @file MailTemplate.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package mail
 * @class MailTemplate
 *
 * Subclass of PKPMailTemplate for mailing a template email.
 *
 * $Id$
 */

import('lib.pkp.classes.mail.PKPMailTemplate');

class MailTemplate extends PKPMailTemplate {
	/**
	 * Constructor.
	 * @param $emailKey string unique identifier for the template
	 * @param $locale string locale of the template
	 */
	function MailTemplate($emailKey = null, $locale = null) {
		parent::PKPMailTemplate($emailKey, $locale);

		if (isset($this->emailKey)) {
			$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate =& $emailTemplateDao->getEmailTemplate($this->emailKey, $this->locale);
		}

		if (isset($emailTemplate) && Request::getUserVar('subject')==null && Request::getUserVar('body')==null) {
			$this->setSubject($emailTemplate->getSubject());
			$this->setBody($emailTemplate->getBody());
			$this->enabled = $emailTemplate->getEnabled();

			if (Request::getUserVar('usePostedAddresses')) {
				$to = Request::getUserVar('to');
				if (is_array($to)) {
					$this->setRecipients($this->processAddresses ($this->getRecipients(), $to));
				}
				$cc = Request::getUserVar('cc');
				if (is_array($cc)) {
					$this->setCcs($this->processAddresses ($this->getCcs(), $cc));
				}
				$bcc = Request::getUserVar('bcc');
				if (is_array($bcc)) {
					$this->setBccs($this->processAddresses ($this->getBccs(), $bcc));
				}
			}
		} else {
			$this->setSubject(Request::getUserVar('subject'));
			$this->setBody(Request::getUserVar('body'));
			$this->skip = (($tmp = Request::getUserVar('send')) && is_array($tmp) && isset($tmp['skip']));
			$this->enabled = true;

			if (is_array($toEmails = Request::getUserVar('to'))) {
				$this->setRecipients($this->processAddresses ($this->getRecipients(), $toEmails));
			}
			if (is_array($ccEmails = Request::getUserVar('cc'))) {
				$this->setCcs($this->processAddresses ($this->getCcs(), $ccEmails));
			}
			if (is_array($bccEmails = Request::getUserVar('bcc'))) {
				$this->setBccs($this->processAddresses ($this->getBccs(), $bccEmails));
			}
		}

		$site =& Request::getSite();
		$this->setFrom($site->getLocalizedSetting('contactEmail'), $site->getLocalizedSetting('contactName'));
	}

	/**
	 * Assigns values to e-mail parameters.
	 * @param $paramArray array
	 * @return void
	 */
	function assignParams($paramArray = array()) {
		// Add commonly-used variables to the list
		$site =& Request::getSite();
		$paramArray['principalContactSignature'] = $site->getLocalizedSetting('contactName');

		return parent::assignParams($paramArray);
	}
}

?>
