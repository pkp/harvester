<?php

/**
 * SearchIndex.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package search
 *
 * Class to add content to the search index.
 *
 * $Id$
 */

define('SEARCH_STOPWORDS_FILE', 'registry/stopwords.txt');

// Words are truncated to at most this length
define('SEARCH_KEYWORD_MAX_LENGTH', 40);

class SearchIndex {
	
	/**
	 * Index a block of text for an object.
	 * @param $objectId int
	 * @param $text string
	 * @param $position int
	 */
	function indexObjectKeywords($objectId, $text, &$position) {
		$searchDao = &DAORegistry::getDAO('SearchDAO');
		$keywords = &SearchIndex::filterKeywords($text);
		for ($i = 0, $count = count($keywords); $i < $count; $i++) {
			$searchDao->insertObjectKeyword($objectId, $keywords[$i], $position);
			$position += 1;
		}
	}

	/**
	 * Add a block of text to the search index.
	 * @param $recordId int
	 * @param $fieldId int
	 * @param $text string
	 */
	function updateTextIndex($text, $assocId = null) {
			$searchDao = &DAORegistry::getDAO('SearchDAO');
			$objectId = $searchDao->insertObject($recordId, $fieldId);
			$position = 0;
			SearchIndex::indexObjectKeywords($objectId, $text, $position);
	}
	
	/**
	 * Delete keywords from the search index.
	 * @param $recordId int
	 * @param $fieldId int optional
	 */
	function deleteTextIndex($recordId, $fieldId = null) {
		$searchDao = &DAORegistry::getDAO('SearchDAO');
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
		$stopwords = &SearchIndex::loadStopwords();
		
		// Remove punctuation
		if (is_array($text)) {
			$text = join("\n", $text);
		}
		
		$cleanText = preg_replace('/[!"\#\$%\'\(\)\.\?@\[\]\^`\{\}~]/', '', $text);
		$cleanText = preg_replace('/[\+,:;&\/<=>\|\\\]/', ' ', $cleanText);
		$cleanText = preg_replace('/[\*]/', $allowWildcards ? '%' : ' ', $cleanText);
		$cleanText = String::strtolower($cleanText);
		
		// Split into words
		$words = preg_split('/\s+/', $cleanText);
		
		// FIXME Do not perform further filtering for some fields, e.g., author names?
		
		// Remove stopwords
		$keywords = array();
		foreach ($words as $k) {
			if (!isset($stopwords[$k]) && String::strlen($k) >= $minLength && !is_numeric($k)) {
				$keywords[] = String::substr($k, 0, SEARCH_KEYWORD_MAX_LENGTH);
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
	 * @param $article Article
	 */
	function indexRecord(&$record) {
		fatalError('Indexing not implemented yet!');
		// SearchIndex::updateTextIndex($articleId, ARTICLE_SEARCH_AUTHOR, $authorText);
	}
	
	/**
	 * Rebuild the search index.
	 */
	function rebuildIndex($log = false) {
		// Clear index
		if ($log) echo 'Clearing index ... ';
		$searchDao = &DAORegistry::getDAO('SearchDAO');
		// FIXME Abstract into SearchDAO?
		$searchDao->update('DELETE FROM search_object_keywords');
		$searchDao->update('DELETE FROM search_objects');
		$searchDao->update('DELETE FROM search_keyword_list');
		$searchDao->_dataSource->CacheFlush();
		if ($log) echo "done\n";

		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$records = &$recordDao->getRecords();
		while (!$records->eof()) {
			$record =& $records->next();
			SearchIndex::indexRecord($record);
			$numIndexed++;
		}
		
		if ($log) echo $numIndexed, " records indexed\n";
	}
	
}

?>
