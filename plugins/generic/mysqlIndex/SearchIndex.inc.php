<?php

/**
 * @file SearchIndex.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package search
 * @class SearchIndex
 *
 * Class to add content to the search index.
 *
 * $Id$
 */

define('SEARCH_STOPWORDS_FILE', 'registry/stopwords.txt');

class SearchIndex {

	/**
	 * Index a block of text for an object.
	 * @param $objectId int
	 * @param $text string
	 * @param $position int
	 */
	function indexObjectKeywords($objectId, $text, &$position) {
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$keywords =& SearchIndex::filterKeywords($text);
		for ($i = 0, $count = count($keywords); $i < $count; $i++) {
			if ($searchDao->insertObjectKeyword($objectId, $keywords[$i], $position) !== null) {
				$position += 1;
			}
		}
	}

	/**
	 * Add a block of text to the search index.
	 * @param $recordId int
	 * @param $fieldId int
	 * @param $text string
	 * @param $flush boolean Whether or not to flush entries for an existing object
	 */
	function updateTextIndex($recordId, $fieldId, $text, $flush = true) {
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$position = 0;
		$objectId = $searchDao->insertObject($recordId, $fieldId, $position, null, $flush);
		SearchIndex::indexObjectKeywords($objectId, $text, $position);
	}

	/**
	 * Add an item to the date index.
	 * @param $recordId int
	 * @param $fieldId int
	 * @param $date string
	 * @param $text string optional -- if set, index the text value as well
	 * @param $flush boolean Whether or not to flush entries for an existing object
	 */
	function updateDateIndex($recordId, $fieldId, $date, $text = null, $flush = true) {
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$position = 0;
		$objectId = $searchDao->insertObject($recordId, $fieldId, $position, $date, $flush);
		if (!empty($text)) {
			SearchIndex::indexObjectKeywords($objectId, $text, $position);
		}
	}

	/**
	 * Delete keywords from the search index.
	 * @param $recordId int
	 * @param $fieldId int optional
	 */
	function deleteTextIndex($recordId, $fieldId = null) {
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		return $searchDao->deleteRecordObjects($recordId, $fieldId);
	}

	/**
	 * Split a string into a clean array of keywords
	 * @param $text string
	 * @param $allowWildcards boolean
	 * @return array of keywords
	 */
	function &filterKeywords($text, $allowWildcards = false) {
		$minLength = Config::getVar('search', 'min_word_length');
		$maxLength = Config::getVar('search', 'max_word_length');
		$stopwords =& SearchIndex::loadStopwords();

		// Remove punctuation
		if (is_array($text)) {
			$text = join("\n", $text);
		}

		$cleanText = String::regexp_replace('/[!"\#\$%\'\(\)\.\?@\[\]\^`\{\}~]/', '', $text);
		$cleanText = String::regexp_replace('/[\+,:;&\/<=>\|\\\]/', ' ', $cleanText);
		$cleanText = String::regexp_replace('/[\*]/', $allowWildcards ? '%' : ' ', $cleanText);
		$cleanText = String::strtolower($cleanText);

		// Split into words
		$words = String::regexp_split('/\s+/', $cleanText);

		// FIXME Do not perform further filtering for some fields, e.g., author names?

		// Remove stopwords
		$keywords = array();
		foreach ($words as $k) {
			if (!isset($stopwords[$k]) && String::strlen($k) >= $minLength && !is_numeric($k)) {
				$keywords[] = String::substr($k, 0, $maxLength);
			}
		}
		return $keywords;
	}

	/**
	 * Return list of stopwords.
	 * FIXME Should this be locale-specific?
	 * @return array with stopwords as keys
	 */
	function &loadStopwords() {
		static $searchStopwords;

		if (!isset($searchStopwords)) {
			// Load stopwords only once per request (FIXME Cache?)
			$searchStopwords = array_count_values(array_filter(file(SEARCH_STOPWORDS_FILE), create_function('&$a', 'return ($a = trim($a)) && !empty($a) && $a[0] != \'#\';')));
			$searchStopwords[''] = 1;
		}

		return $searchStopwords;
	}

	/**
	 * Index record metadata.
	 * @param $record Article
	 */
	function indexRecord(&$archive, &$record) {
		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$schemaPlugin =& $record->getSchemaPlugin();
		$schemaPlugin->indexRecord($archive, $record);
	}
}

?>
