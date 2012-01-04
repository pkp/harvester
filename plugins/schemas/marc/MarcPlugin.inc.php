<?php

/**
 * @file MarcPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.marc
 * @class MarcPlugin
 *
 * Marc schema plugin
 *
 * $Id$
 */

import('classes.plugins.SchemaPlugin');

class MarcPlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			// Smarty has trouble with string indexes to arrays
			// that look like numbers. Work around by adding a new
			// Smarty function to help look up MARC record elements
			// for display.
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->register_function('get_marc_element', array(&$this, 'smartyGetMarcElement'));
		}
		$this->addLocaleData();
		return $success;
	}

	function smartyGetMarcElement($params, &$smarty) {
		$id = $params['id'];
		$i1 = $params['i1'];
		$i2 = $params['i2'];
		$label = $params['label'];
		$record = $params['record'];
		$elements = $record->getParsedContents();
		if (isset($elements["$id"]["$i1"]["$i2"]["$label"])) {
			if (isset($params['firstOnly']) && $params['firstOnly']) {
				return array_shift($elements["$id"]["$i1"]["$i2"]["$label"]);
			}
			return $elements["$id"]["$i1"]["$i2"]["$label"];
		}
		return null;
	}

	function getName() {
		return 'MarcPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return __('plugins.schemas.marc.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.schemas.marc.description');
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array('leader', '001', '003', '005', '006', '007', '008', '010', '013', '015', '016', '017', '020', '022', '024', '025', '026', '027', '028', '030', '031', '032', '033', '034', '035', '036', '037', '038', '040', '041', '042', '043', '044', '045', '046', '047', '048', '050', '051', '052', '055', '060', '061', '066', '070', '071', '072', '074', '080', '082', '084', '086', '088', '100', '110', '111', '130', '210', '222', '240', '242', '243', '245', '246', '247', '250', '254', '255', '256', '257', '258', '260', '263', '270', '300', '306', '307', '310', '321', '340', '342', '343', '351', '352', '355', '357', '362', '365', '366', '440', '490', '500', '501', '502', '504', '505', '506', '507', '508', '510', '511', '513', '514', '515', '516', '518', '520', '521', '522', '524', '525', '526', '530', '533', '534', '535', '536', '538', '540', '541', '544', '545', '546', '547', '550', '552', '555', '556', '561', '562', '563', '565', '567', '580', '581', '583', '584', '585', '586', '600', '610', '611', '630', '648', '650', '651', '653', '654', '655', '656', '657', '658', '700', '710', '711', '720', '730', '740', '752', '753', '754', '760', '762', '765', '767', '770', '772', '773', '774', '775', '776', '777', '780', '785', '786', '787', '810', '811', '830', '841', '842', '843', '844', '845', '850', '852', '853', '854', '855', '856', '863', '864', '865', '866', '867', '868', '876', '877', '878', '880', '886', '887');
		}
		return $fieldList;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return __("plugins.schemas.marc.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return __("plugins.schemas.marc.fields.$fieldSymbolic.description", $locale);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @return string
	 */
	function getUrl(&$record) {
		$entries = $record->getParsedContents();
		if (isset($entries['856']['4']['0']['u'])) foreach ($entries['856']['4']['0']['u'] as $entry) {
			if (preg_match('/^[a-z]+:\/\//', $entry)) {
				return $entry;
			}
		}
		return null;
	}

	/**
	 * Get the authors for the supplied record, if available; null otherwise
	 * @param $record object
	 * @return array
	 */
	function getAuthors(&$record) {
		$entries = $record->getParsedContents();
		if (isset($entries['720'][' '][' ']['a'])) return $entries['720'][' '][' ']['a'];
		return array();
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @return string
	 */
	function getTitle(&$record) {
		$entries = $record->getParsedContents();
		if (isset($entries['245']['0']['0']['a'])) return array_shift($entries['245']['0']['0']['a']);
		if (isset($entries['520'][' '][' ']['a'])) return array_shift($entries['520'][' '][' ']['a']);
		return null;
	}

	function getFieldType($name) {
		switch ($name) {
			case '005':
			case '008':
			case '260':
				return FIELD_TYPE_DATE;
			case '041':
				return FIELD_TYPE_SELECT;
			default:
				return FIELD_TYPE_STRING;
		}
	}

	function parseDate($fieldName, $value, $attributes = null) {
		switch ($fieldName) {
			case '005': // YYYYMMDDHHMMSS.0 Date and time of latest transaction
				if (String::strlen($value) < 14) return null;
				$year = String::substr($value, 0, 4);
				$month = String::substr($value, 4, 2);
				$day = String::substr($value, 6, 2);
				$hour = String::substr($value, 8, 2);
				$minute = String::substr($value, 10, 2);
				$second = String::substr($value, 12);

				// Make sure the values fetched are all numeric
				foreach (array('year', 'month', 'day', 'hour', 'minute', 'second') as $var) {
					if (!is_numeric($$var)) return null;
				}
				return mktime($hour, $minute, $second, $month, $day, $year);
			case '008': // YYMMDD[junk] Date entered on file
				$date = String::substr($value, 0, 6);
				$date = strtotime($date);
				if ($date !== -1 && $date !== false) return $date;
				break;
			case '260':
				if (isset($attributes['label']) && $attributes['label'] == 'c') {
					$date = strtotime($value);
					if ($date !== -1 && $date !== false) return $date;
				}
				break;
		}
		return null;
	}

	function getMetadataPrefix() {
		return 'oai_marc';
	}

	function getFormatClass() {
		$this->import('OAIMetadataFormat_MARC');
		return 'OAIMetadataFormat_MARC';
	}

	function getSchemaName() {
		return 'http://www.openarchives.org/OAI/1.1/oai_marc.xsd';
	}

	function getNamespace() {
		return 'http://www.openarchives.org/OAI/1.1/oai_marc';
	}

	/**
	 * Parse a record's contents into an object
	 * @param $contents string
	 * @return object
	 */
	function &parseContents(&$contents) {
		$xmlParser = new XMLParser();
		$result =& $xmlParser->parseText($contents);

		$returner = array();
		foreach ($result->getChildren() as $child) {
			$name = $child->getName();
			switch ($child->getName()) {
				case 'varfield':
					$id = $child->getAttribute('id');
					$i1 = $child->getAttribute('i1');
					$i2 = $child->getAttribute('i2');
					foreach ($child->getChildren() as $subfield) {
						if ($subfield->getName() != 'subfield') continue;
						$value = $subfield->getValue();
						if (empty($value)) continue;
						$label = $subfield->getAttribute('label');
						$returner[$id][$i1][$i2][$label][] = $value;
					}
					break;
				case 'datafield':
					$id = $child->getAttribute('tag');
					$i1 = $child->getAttribute('ind1');
					$i2 = $child->getAttribute('ind2');
					foreach ($child->getChildren() as $subfield) {
						if ($subfield->getName() != 'subfield') continue;
						$value = $subfield->getValue();
						if (empty($value)) continue;
						$label = $subfield->getAttribute('code');
						$returner[$id][$i1][$i2][$label][] = $value;
					}
					break;
				case 'controlfield':
					break;
			}
		}

		$result->destroy();
		$xmlParser->destroy();
		unset($result, $xmlParser);

		return $returner;
	}

	/**
	 * Get the value of a field by symbolic name.
	 * @var $record object
	 * @var $name string
	 * @var $type SORT_ORDER_TYPE_...
	 * @return mixed
	 */
	function getFieldValue(&$record, $name, $type) {
		$fieldValue = null;
		$parsedContents = $record->getParsedContents();
		if (isset($parsedContents[$name])) switch ($type) {
			case SORT_ORDER_TYPE_STRING:
				$fieldValue = join(';', array_shift((array_shift(array_shift($parsedContents[$name])))));
				break;
			case SORT_ORDER_TYPE_NUMBER:
				$fieldValue = (int) array_shift($parsedContents[$name]);
				break;
			case SORT_ORDER_TYPE_DATE:
				$fieldValue = strtotime($thing = array_shift($parsedContents[$name]));
				if ($fieldValue === -1 || $fieldValue === false) $fieldValue = null;
				break;
			default:
				fatalError('UNKNOWN TYPE');
		}
		HookRegistry::call('MarcPlugin::getFieldValue', array(&$this, &$fieldValue));
		return $fieldValue;
	}
}

?>
