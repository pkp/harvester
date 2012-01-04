<?php

/**
 * @file EmailTemplateDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package mail
 * @class EmailTemplateDAO
 *
 * Class for Email Template DAO.
 * Operations for retrieving and modifying Email Template objects.
 *
 * $Id$
 */

import('classes.mail.EmailTemplate');

class EmailTemplateDAO extends DAO {

	/**
	 * Constructor.
	 */
	function EmailTemplateDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a base email template by key.
	 * @param $emailKey string
	 * @return BaseEmailTemplate
	 */
	function &getEmailTemplate($emailKey) {
		$result =& $this->retrieve(
			'SELECT d.email_key, d.can_edit, d.can_disable, d.enabled
			FROM email_templates AS d
			WHERE d.email_key = ?',
			$emailKey
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return an email template object from a row.
	 * @param $row array
	 * @return EmailTemplate
	 */
	function &_returnEmailTemplateFromRow(&$row) {
		$emailTemplate = new EmailTemplate();
		$emailTemplate->setEmailKey($row['email_key']);
		$emailTemplate->setEnabled($row['enabled']);
		$emailTemplate->setCanDisable($row['can_disable']);

		if (!HookRegistry::call('EmailTemplateDAO::_returnEmailTemplateFromRow', array(&$emailTemplate, &$row))) {
			$result =& $this->retrieve(
				'SELECT d.locale, d.description, d.subject, d.body
				FROM email_templates_data AS d
				WHERE d.email_key = ?',
				$row['email_key']
			);

			while (!$result->EOF) {
				$dataRow =& $result->GetRowAssoc(false);
				$emailTemplate->addLocale($dataRow['locale']);
				$emailTemplate->setSubject($dataRow['locale'], $dataRow['subject']);
				$emailTemplate->setBody($dataRow['locale'], $dataRow['body']);
				$emailTemplate->setDescription($dataRow['locale'], $dataRow['description']);
				$result->MoveNext();
			}
			$result->Close();
			unset($result);
		}

		return $emailTemplate;
	}

	/**
	 * Insert a new base email template.
	 * @param $emailTemplate BaseEmailTemplate
	 */	
	function insertBaseEmailTemplate(&$emailTemplate) {
		return $this->update(
			'INSERT INTO email_templates
				(email_key, enabled)
				VALUES
				(?, ?)',
			array(
				$emailTemplate->getEmailKey(),
				$emailTemplate->getEnabled() == null ? 0 : 1
			)
		);
		$emailTemplate->setEmailId($this->getInsertEmailId());
		return $emailTemplate->getEmailId();
	}

	/**
	 * Update an existing base email template.
	 * @param $emailTemplate BaseEmailTemplate
	 */
	function updateBaseEmailTemplate(&$emailTemplate) {
		return $this->update(
			'UPDATE email_templates
				SET	enabled = ?
				WHERE email_key = ?',
			array(
				$emailTemplate->getEnabled() == null ? 0 : 1,
				$emailTemplate->getEmailKey()
			)
		);
	}

	/**
	 * Insert a new localized email template.
	 * @param $emailTemplate EmailTemplate
	 */	
	function insertEmailTemplate(&$emailTemplate) {
		$this->insertBaseEmailTemplate($emailTemplate);
		return $this->updateEmailTemplateData($emailTemplate);
	}

	/**
	 * Insert/update locale-specific email template data.
	 * @param $emailTemplate LocaleEmailTemplate
	 */
	function updateEmailTemplateData(&$emailTemplate) {
		foreach ($emailTemplate->getLocales() as $locale) {
			$result =& $this->retrieve(
				'SELECT COUNT(*) FROM email_templates_data
				WHERE email_key = ? AND locale = ?',
				array($emailTemplate->getEmailKey(), $locale)
			);

			if ($result->fields[0] == 0) {
				$this->update(
					'INSERT INTO email_templates_data
					(email_key, locale, subject, body)
					VALUES
					(?, ?, ?, ?)',
					array($emailTemplate->getEmailKey(), $locale, $emailTemplate->getSubject($locale), $emailTemplate->getBody($locale))
				);

			} else {
				$this->update(
					'UPDATE email_templates_data
					SET subject = ?,
						body = ?
					WHERE email_key = ? AND locale = ?',
					array($emailTemplate->getSubject($locale), $emailTemplate->getBody($locale), $emailTemplate->getEmailKey(), $locale)
				);
			}

			$result->Close();
			unset($result);
		}
	}

	/**
	 * Delete an email template by key.
	 * @param $emailKey string
	 */
	function deleteEmailTemplateByKey($emailKey) {
		return $this->update(
			'DELETE FROM email_templates WHERE email_key = ?',
			$emailKey
		);
	}

	/**
	 * Get the ID of the last inserted email template.
	 * @return int
	 */
	function getInsertEmailId() {
		return $this->getInsertId('email_templates', 'emailId');
	}

	/**
	 * Delete all email templates for a specific locale.
	 * @param $locale string
	 */
	function deleteEmailTemplatesByLocale($locale) {
		$this->update(
			'DELETE FROM email_templates_data WHERE locale = ?', $locale
		);
	}

	/**
	 * Delete all default email templates for a specific locale.
	 * @param $locale string
	 */
	function deleteDefaultEmailTemplatesByLocale($locale) {
		/*$this->update(
			'DELETE FROM email_templates_default_data WHERE locale = ?', $locale
		);*/ // Not used in Harvester, but called from PKP. FIXME
	}
}

?>
