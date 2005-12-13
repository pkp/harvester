<?php

/**
 * Site.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package site
 *
 * Site class.
 * Describes system-wide site properties.
 *
 * $Id$
 */

class Site extends DataObject {
	var $siteSettingsDao;

	/**
	 * Constructor.
	 */
	function Site() {
		parent::DataObject();
		$this->siteSettingsDao =& DAORegistry::getDAO('SiteSettingsDAO');
	}
	
	/**
	 * Return associative array of all locales supported by the site.
	 * These locales are used to provide a language toggle on the main site pages.
	 * @return array
	 */
	function &getSupportedLocaleNames() {
		static $supportedLocales;
		
		if (!isset($supportedLocales)) {
			$supportedLocales = array();
			$localeNames = &Locale::getAllLocales();
			
			$locales = $this->getSupportedLocales();
			foreach ($locales as $localeKey) {
				$supportedLocales[$localeKey] = $localeNames[$localeKey];
			}
			
			asort($supportedLocales);
		}
		
		return $supportedLocales;
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get site title.
	 * @return string
	 */
	function getTitle() {
		return $this->getSetting('title');
	}
	
	/**
	 * Set site title.
	 * @param $title string
	 */
	function setTitle($title) {
		$this->updateSetting('title', $title);
	}
	
	/**
	 * Get site username.
	 * @return string
	 */
	function getUsername() {
		return $this->getSetting('username');
	}
	
	/**
	 * Set site username.
	 * @param $username string
	 */
	function setUsername($username) {
		$this->updateSetting('username', $username);
	}
	
	/**
	 * Get site password.
	 * @return string
	 */
	function getPassword() {
		return $this->getSetting('password');
	}
	
	/**
	 * Set site password.
	 * @param $password string
	 */
	function setPassword($password) {
		$this->updateSetting('password', $password);
	}
	
	/**
	 * Get site introduction.
	 * @return string
	 */
	function getIntro() {
		return $this->getSetting('intro');
	}
	
	/**
	 * Set site introduction.
	 * @param $intro string
	 */
	function setIntro($intro) {
		$this->updateSetting('intro', $intro);
	}
	
	/**
	 * Get site about description.
	 * @return string
	 */
	function getAbout() {
		return $this->getSetting('about');
	}
	
	/**
	 * Set site about description.
	 * @param $about string
	 */
	function setAbout($about) {
		$this->updateSetting('about', $about);
	}
	
	/**
	 * Get site contact name.
	 * @return string
	 */
	function getContactName() {
		return $this->getSetting('contact_name');
	}
	
	/**
	 * Set site contact name.
	 * @param $contactName string
	 */
	function setContactName($contactName) {
		$this->updateSetting('contact_name', $contactName);
	}
	
	/**
	 * Get site contact email.
	 * @return string
	 */
	function getContactEmail() {
		return $this->getSetting('contact_email');
	}
	
	/**
	 * Set site contact email.
	 * @param $contactEmail string
	 */
	function setContactEmail($contactEmail) {
		$this->updateSetting('contact_email', $contactEmail);
	}
	
	/**
	 * Get primary locale.
	 * @return string
	 */
	function getLocale() {
		return $this->getSetting('locale');
	}
	
	/**
	 * Set primary locale.
	 * @param $locale string
	 */
	function setLocale($locale) {
		$this->updateSetting('locale', $locale);
	}
	
	/**
	 * Get installed locales.
	 * @return array
	 */
	function getInstalledLocales() {
		$returner =& $this->getSetting('installed_locales');
		if (!isset($returner)) $returner = array();
		return $returner;
	}
	
	/**
	 * Set installed locales.
	 * @param $installedLocales array
	 */
	function setInstalledLocales($installedLocales) {
		$this->updateSetting('installed_locales', $installedLocales);
	}
	
	/**
	 * Get array of all supported locales (for static text).
	 * @return array
	 */
	function getSupportedLocales() {
		$returner = $this->getSetting('supported_locales');
		if (!isset($returner)) $returner = array();
		return $returner;
	}
	
	/**
	 * Set array of all supported locales (for static text).
	 * @param $supportedLocales array
	 */
	function setSupportedLocales($supportedLocales) {
		$this->updateSetting('supported_locales', $supportedLocales);
	}

	/**
	 * Install settings from an XML file.
	 * @param $filename
	 */
	function installSettings($filename, $paramArray = array()) {
		return $this->siteSettingsDao->installSettings($filename, $paramArray);
	}

	/**
	 * Get a site setting.
	 * @param $name
	 */
	function getSetting($name) {
		return $this->siteSettingsDao->getSetting($name);
	}

	/**
	 * Update a site setting.
	 * @param $name
	 * @param $value
	 * @param $type
	 */
	function updateSetting($name, $value, $type = null) {
		$this->siteSettingsDao->updateSetting($name, $value, $type);
	}
}

?>
