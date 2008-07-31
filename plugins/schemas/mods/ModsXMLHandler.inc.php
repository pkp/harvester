<?php

/**
 * @file ModsXMLHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.mods
 * @class ModsXMLHandler
 *
 * XML parser for the ModsXML schema
 *
 * $Id$
 */

class ModsXMLHandler extends XMLParserHandler {
	/** @var $harvester object */
	var $harvester;

	/** @var $metadata array */
	var $metadata;

	/** @var $attributes array */
	var $attributes;

	/** @var $rootElement array */
	var $rootElement;

	/** @var $modsElement array Pointer within rootElement */
	var $modsElement;

	/**
	 * Constructor
	 * @param $harvester object
	 * @param $metadata array Reference to array to populate with metadata
	 */
	function ModsXMLHandler(&$harvester) {
		$this->harvester =& $harvester;
	}

	function initializeElements() {
		unset($this->rootElement);
		unset($this->modsElement);
		$nullParent = null;
		$this->rootElement =& new ModsElement($nullParent, null, null, null);
		$this->modsElement =& $this->rootElement;
	}

	function startElement(&$parser, $tag, $attributes) {
		if (String::substr($tag, 0, 5) === 'mods:') {
			$tag = String::substr($tag, 5);
		}

		switch ($tag) {
			case 'mods':
				$this->initializeElements();
			case 'oai_mods:mods':
				return;
		}

		$oldCurrent =& $this->modsElement;
		$newNode =& new ModsElement($oldCurrent, $tag, $attributes);
		unset($this->modsElement);
		$oldCurrent->addChild($newNode);
		$this->modsElement =& $newNode;
	}

	function endElement(&$parser, $tag) {
		// Strip the "mods:" from the tag, and we have the field key.
		if (String::substr($tag, 0, 5) === 'mods:') {
			$tag = String::substr($tag, 5);
		}

		switch ($tag) {
			case 'mods':
				$this->rootElement->flush($this->harvester);
			case 'oai_mods:mods':
				return;
		}

		$oldCurrent =& $this->modsElement;
		unset($this->modsElement);
		$this->modsElement =& $oldCurrent->getParent();
	}

	function characterData(&$parser, $data) {
		if ($this->modsElement) $this->modsElement->setValue($data);
	}

	function &getMetadata() {
		return $this->metadata;
	}
}

class ModsElement {
	/** @var $tag string */
	var $tag;

	/** @var $value string */
	var $value;

	/** @var $attributes array */
	var $attributes;

	/** @var $children array */
	var $children;

	/** @var $parentElement array */
	var $parentElement;

	function ModsElement(&$parentElement, $tag, $attributes) {
		$this->parentElement =& $parentElement;
		$this->tag = $tag;
		$this->value = null;
		$this->attributes = $attributes;

		$this->children = array();
	}

	function addChild(&$modsElement) {
		$this->children[] =& $modsElement;
	}

	function setValue($value) {
		$this->value = $value;
	}

	function &getParent() {
		return $this->parentElement;
	}

	function flush(&$harvester, $parentId = null) {
		$field = $entryId = null;

		if ($this->tag !== null) {
			$field =& $harvester->getFieldByKey($this->tag, ModsPlugin::getName());
			if ($field) {
				$entryId = $harvester->insertEntry($field, $this->value, $this->attributes, $parentId);
			} else {
				$harvester->addError("Unknown tag \"$this->tag\"!");
				return;
			}
		}

		foreach ($this->children as $key => $junk) {
			$this->children[$key]->flush($harvester, $entryId);
		}
	}
}

?>
