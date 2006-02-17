<?php

/**
 * Validation.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package security
 *
 * Class providing user validation/authentication operations. 
 *
 * $Id$
 */

class Validation {

	/**
	 * Authenticate user credentials and mark the user as logged in in the current session.
	 * @param $username string
	 * @param $password string unencrypted password
	 * @param $reason string reference to string to receive the reason an account was disabled; null otherwise
	 * @param $remember boolean remember a user's session past the current browser session
	 * @return boolean True IFF login was successful
	 */
	function login($username, $password, &$reason, $remember = false) {
		if (!Validation::checkCredentials($username, $password)) {
			return false;
		}

		$sessionManager = &SessionManager::getManager();

		// Regenerate session ID first
		$sessionManager->regenerateSessionId();

		$session = &$sessionManager->getUserSession();
		$session->setLoggedIn(true);
		$session->setSessionVar('username', $username);
		$session->setRemember($remember);

		if ($remember && Config::getVar('general', 'session_lifetime') > 0) {
			// Update session expiration time
			$sessionManager->updateSessionLifetime(time() +  Config::getVar('general', 'session_lifetime') * 86400);
		}

		return true;
	}
	
	/**
	 * Mark the user as logged out in the current session.
	 * @return boolean
	 */
	function logout() {
		$sessionManager = &SessionManager::getManager();
		$session = &$sessionManager->getUserSession();
		
		if ($session->getRemember()) {
			$session->setRemember(0);
			$sessionManager->updateSessionLifetime(0);
		}

		$session->setLoggedIn(false);
			
		$sessionDao = &DAORegistry::getDAO('SessionDAO');
		$sessionDao->updateSession($session);

		return true;
	}
	
	/**
	 * Redirect to the login page, appending the current URL as the source.
	 */
	function redirectLogin() {
		if (isset($_SERVER['REQUEST_URI'])) {
			Request::redirect('login', null, null, array('source' => $_SERVER['REQUEST_URI']));
		} else {
			Request::redirect('login');
		}
	}
	
	/**
	 * Check if a user's credentials are valid.
	 * @param $username string username
	 * @param $password string unencrypted password
	 * @return boolean
	 */
	function checkCredentials($username, $password) {
		$site =& Request::getSite();
		return (Validation::encryptCredentials($username, $password) === $site->getPassword());
	}
	
	/**
	 * Encrypt user passwords for database storage.
	 * The username is used as a unique salt to make dictionary
	 * attacks against a compromised database more difficult.
	 * @param $username string username
	 * @param $password string unencrypted password
	 * @param $encryption string optional encryption algorithm to use, defaulting to the value from the site configuration
	 * @return string encrypted password
	 */
	function encryptCredentials($username, $password, $encryption = false) {
		$valueToEncrypt = $username . $password;
		
		if ($encryption == false) {
			$encryption = Config::getVar('security', 'encryption');
		}
		
		switch ($encryption) {
			case 'sha1':
				if (function_exists('sha1')) {
					return sha1($valueToEncrypt);
				}
			case 'md5':
			default:
				return md5($valueToEncrypt);
		}
	}
	
	/**
	 * Generate a random password.
	 * Assumes the random number generator has already been seeded.
	 * @param $length int the length of the password to generate (default 8)
	 * @return string
	 */
	function generatePassword($length = 8) {
        $password = "";
		for ($i=0; $i<$length; $i++) {
			$password .= mt_rand(1, 4) == 4 ? mt_rand(0,9) : (mt_rand(0,1) == 0 ? chr(mt_rand(65, 90)) : chr(mt_rand(97, 122)));
		}
        return $password;
	}
	
	/**
	 * Check if the user must change their password in order to log in.
	 * @return boolean
	 */
	function isLoggedIn() {
		$sessionManager = &SessionManager::getManager();
		$session = &$sessionManager->getUserSession();
		
		return $session->getLoggedIn();
	}
	
}

?>
