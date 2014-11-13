<?php

/**
 * @file pages/user/EmailHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user emails.
 */

// $Id$

import('pages.user.UserHandler');

class EmailHandler extends UserHandler {
	function email($args) {
		$this->validate();

		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();

		$userDao =& DAORegistry::getDAO('UserDAO');

		$user =& Request::getUser();

		// See if this is the Editor or Manager and an email template has been chosen
		$template = Request::getUserVar('template');
		if (empty($template) || (
			!Validation::isSiteAdmin()
		)) {
			$template = null;
		}

		// Determine whether or not this account is subject to
		// email sending restrictions.
		$canSendUnlimitedEmails = Validation::isSiteAdmin();
		$unlimitedEmailRoles = array(
			ROLE_ID_SITE_ADMIN
		);
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roles =& $roleDao->getRolesByUserId($user->getId());
		foreach ($roles as $role) {
			if (in_array($role->getRoleId(), $unlimitedEmailRoles)) $canSendUnlimitedEmails = true;
		}

		// Check when this user last sent an email, and if it's too
		// recent, make them wait.
		if (!$canSendUnlimitedEmails) {
			$dateLastEmail = $user->getDateLastEmail();
			if ($dateLastEmail && strtotime($dateLastEmail) + ((int) Config::getVar('email', 'time_between_emails')) > strtotime(Core::getCurrentDate())) {
				$templateMgr->assign('pageTitle', 'email.compose');
				$templateMgr->assign('message', 'email.compose.tooSoon');
				$templateMgr->assign('backLink', 'javascript:history.back()');
				$templateMgr->assign('backLinkLabel', 'email.compose');
				return $templateMgr->display('common/message.tpl');
			}
		}

		import('classes.mail.MailTemplate');
		$email = new MailTemplate($template);

		if (Request::getUserVar('send') && !$email->hasErrors()) {
			$recipients = $email->getRecipients();
			$ccs = $email->getCcs();
			$bccs = $email->getBccs();

			// Make sure there aren't too many recipients (to
			// prevent use as a spam relay)
			$recipientCount = 0;
			if (is_array($recipients)) $recipientCount += count($recipients);
			if (is_array($ccs)) $recipientCount += count($ccs);
			if (is_array($bccs)) $recipientCount += count($bccs);

			if (!$canSendUnlimitedEmails && $recipientCount > ((int) Config::getVar('email', 'max_recipients'))) {
				$templateMgr->assign('pageTitle', 'email.compose');
				$templateMgr->assign('message', 'email.compose.tooManyRecipients');
				$templateMgr->assign('backLink', 'javascript:history.back()');
				$templateMgr->assign('backLinkLabel', 'email.compose');
				return $templateMgr->display('common/message.tpl');
			}
			$email->send();
			$redirectUrl = Request::getUserVar('redirectUrl');
			if (empty($redirectUrl)) $redirectUrl = Request::url('user');
			$user->setDateLastEmail(Core::getCurrentDate());
			$userDao->updateObject($user);
			Request::redirectUrl($redirectUrl);
		} else {
			$email->displayEditForm(Request::url(null, 'email'), array('redirectUrl' => Request::getUserVar('redirectUrl')), null, array('disableSkipButton' => true));
		}
	}
}

?>
