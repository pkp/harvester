<?php

/**
 * @file Search.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package search
 * @class Search
 *
 * Class for retrieving search results.
 *
 * FIXME: NEAR; precedence w/o parens?; stemming; weighted counting
 *
 */

// $Id$


class Search {

	/**
	 * Parses a search query string.
	 * Supports +/-, AND/OR, parens
	 * @param $query
	 * @return array of the form ('+' => <required>, '' => <optional>, '-' => excluded)
	 */
	function parseQuery($query) {
		$count = preg_match_all('/(\+|\-|)("[^"]+"|\(|\)|[^\s\)]+)/', $query, $matches);
		$pos = 0;
		$keywords = Search::_parseQuery($matches[1], $matches[2], $pos, $count);
		return $keywords;
	}

	/**
	 * Query parsing helper routine.
	 * Returned structure is based on that used by the Search::QueryParser Perl module.
	 */
	function _parseQuery($signTokens, $tokens, &$pos, $total) {
		$return = array('+' => array(), '' => array(), '-' => array());
		$postBool = $preBool = '';

		$notOperator = String::strtolower(__('search.operator.not'));
		$andOperator = String::strtolower(__('search.operator.and'));
		$orOperator = String::strtolower(__('search.operator.or'));
		while ($pos < $total) {
			if (!empty($signTokens[$pos])) $sign = $signTokens[$pos];
			else if (empty($sign)) $sign = '+';
			$token = String::strtolower($tokens[$pos++]);
			switch ($token) {
				case $notOperator:
					$sign = '-';
					break;
				case ')':
					return $return;
				case '(':
					$token = Search::_parseQuery($signTokens, $tokens, $pos, $total);
				default:
					$postBool = '';
					if ($pos < $total) {
						$peek = String::strtolower($tokens[$pos]);
						if ($peek == $orOperator) {
							$postBool = 'or';
							$pos++;
						} else if ($peek == $andOperator) {
							$postBool = 'and';
							$pos++;
						}
					}
					$bool = empty($postBool) ? $preBool : $postBool;
					$preBool = $postBool;
					if ($bool == 'or') $sign = '';
					if (is_array($token)) $k = $token;
					else $k = SearchIndex::filterKeywords($token, true);
					if (!empty($k)) $return[$sign][] = $k;
					$sign = '';
					break;
			}
		}
		return $return;
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 */
	function &_getMergedArray(&$keywords, $dates, $archiveIds, &$resultCount) {
		$resultsPerKeyword = Config::getVar('search', 'results_per_keyword');
		$resultCacheHours = Config::getVar('search', 'result_cache_hours');
		if (!is_numeric($resultsPerKeyword)) $resultsPerKeyword = 100;
		if (!is_numeric($resultCacheHours)) $resultCacheHours = 24;

		$mergedKeywords = array('+' => array(), '' => array(), '-' => array());
		foreach ($keywords as $type => $condition) {
			if ($type == 'all') $condition = array($condition);
			foreach ($condition as $id => $keyword) {
				if (!empty($keyword['+']))
					$mergedKeywords['+'][] = array('type' => $type, '+' => $keyword['+'], '' => array(), '-' => array(), 'id' => $id);
				if (!empty($keyword['']))
					$mergedKeywords[''][] = array('type' => $type, '+' => array(), '' => $keyword[''], '-' => array(), 'id' => $id);
				if (!empty($keyword['-']))
					$mergedKeywords['-'][] = array('type' => $type, '+' => array(), '' => $keyword['-'], '-' => array(), 'id' => $id);
			}
		}
		$mergedResults =& Search::_getMergedKeywordResults($mergedKeywords, $dates, $archiveIds, null, null, $resultsPerKeyword, $resultCacheHours);

		$resultCount = count($mergedResults);
		return $mergedResults;
	}

	/**
	 * Recursive helper for _getMergedArray.
	 */
	function &_getMergedKeywordResults(&$keyword, $dates, $archiveIds, $type, $id, $resultsPerKeyword, $resultCacheHours) {
		$mergedResults = null;

		if (isset($keyword['type'])) {
			$type = $keyword['type'];
		}

		if (isset($keyword['id'])) {
			$id = $keyword['id'];
		}

		foreach ($keyword['+'] as $phrase) {
			$results =& Search::_getMergedPhraseResults($phrase, $dates, $archiveIds, $type, $id, $resultsPerKeyword, $resultCacheHours);
			if ($mergedResults === null) {
				$mergedResults = $results;
			} else {
				foreach ($mergedResults as $recordId => $count) {
					if (isset($results[$recordId])) {
						$mergedResults[$recordId] += $results[$recordId];
					} else {
						unset($mergedResults[$recordId]);
					}
				}
			}
		}

		if ($mergedResults == null) {
			$mergedResults = array();
		}

		if (!empty($mergedResults) || empty($keyword['+'])) {
			foreach ($keyword[''] as $phrase) {
				$results =& Search::_getMergedPhraseResults($phrase, $dates, $archiveIds, $type, $id, $resultsPerKeyword, $resultCacheHours);
				foreach ($results as $recordId => $count) {
					if (isset($mergedResults[$recordId])) {
						$mergedResults[$recordId] += $count;
					} else if (empty($keyword['+'])) {
						$mergedResults[$recordId] = $count;
					}
				}
			}

			foreach ($keyword['-'] as $phrase) {
				$results =& Search::_getMergedPhraseResults($phrase, $dates, $archiveIds, $type, $id, $resultsPerKeyword, $resultCacheHours);
				foreach ($results as $recordId => $count) {
					if (isset($mergedResults[$recordId])) {
						unset($mergedResults[$recordId]);
					}
				}
			}
		}

		return $mergedResults;
	}

	/**
	 * Recursive helper for _getMergedArray.
	 */
	function &_getMergedPhraseResults(&$phrase, $dates, $archiveIds, $type, $id, $resultsPerKeyword, $resultCacheHours) {
		if (isset($phrase['+'])) {
			$mergedResults =& Search::_getMergedKeywordResults($phrase, $dates, $archiveIds, $type, $id, $resultsPerKeyword, $resultCacheHours);
			return $mergedResults;
		}

		$mergedResults = array();
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$results =& $searchDao->getPhraseResults(
			$phrase,
			$dates,
			$archiveIds,
			$type,
			$id,
			$resultsPerKeyword,
			$resultCacheHours
		);
		while (!$results->eof()) {
			$result =& $results->next();
			$recordId = $result['record_id'];
			if (!isset($mergedResults[$recordId])) {
				$mergedResults[$recordId] = $result['count'];
			} else {
				$mergedResults[$recordId] += $result['count'];
			}
		}
		return $mergedResults;
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 */
	function &_getSparseArray(&$mergedResults, $resultCount) {
		$results = array();
		$i = 0;
		foreach ($mergedResults as $recordId => $count) {
				$frequencyIndicator = ($resultCount * $count) + $i++;
				$results[$frequencyIndicator] = $recordId;
		}
		krsort($results);
		return $results;
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 * Note that this function is also called externally to fetch
	 * results for the title index, and possibly elsewhere.
	 */
	function &formatResults(&$results) {
		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$returner = array();
		foreach ($results as $recordId) {
			$returner[] =& $recordDao->getRecord($recordId);
		}
		return $returner;
	}

	/**
	 * Return an array of search results matching the supplied
	 * keyword IDs in decreasing order of match quality.
	 * Keywords are supplied in an array of the following format:
	 * $keywords['all'] = array('John', 'Doe');
	 * $keywords['fields'][$fieldId] = array('John', 'Doe');
	 * $keywords['crosswalks'][$crosswalkId] = array('John', 'Doe');
	 * @param $keywords array List of keywords
	 * @param $dates array List of date restrictions
	 * @param $archiveIds Array of archive IDs to include (optional)
	 * @param $rangeInfo Information on the range of results to return
	 */
	function &retrieveResults(&$keywords, $dates, $archiveIds, $rangeInfo = null) {
		// Fetch all the results from all the keywords into one array
		// (mergedResults), where mergedResults[record_id]
		// = sum of all the occurences for all keywords associated with
		// that record ID.
		// resultCount contains the sum of result counts for all keywords.
		$mergedResults =& Search::_getMergedArray($keywords, $dates, $archiveIds, $resultCount);

		// Convert mergedResults into an array (frequencyIndicator =>
		// $recordId).
		// The frequencyIndicator is a synthetically-generated number,
		// where higher is better, indicating the quality of the match.
		// It is generated here in such a manner that matches with
		// identical frequency do not collide.
		$results =& Search::_getSparseArray($mergedResults, $resultCount);

		$totalResults = count($results);

		// Use only the results for the specified page, if specified.
		if ($rangeInfo && $rangeInfo->isValid()) {
			$results = array_slice(
				$results,
				$rangeInfo->getCount() * ($rangeInfo->getPage()-1),
				$rangeInfo->getCount()
			);
			$page = $rangeInfo->getPage();
			$itemsPerPage = $rangeInfo->getCount();
		} else {
			$page = 1;
			$itemsPerPage = max($totalResults, 1);
		}

		$results =& Search::formatResults($results);

		// Return the appropriate iterator.
		import('lib.pkp.classes.core.VirtualArrayIterator');
		$returner = new VirtualArrayIterator($results, $totalResults, $page, $itemsPerPage);
		return $returner;
	}
}

?>
