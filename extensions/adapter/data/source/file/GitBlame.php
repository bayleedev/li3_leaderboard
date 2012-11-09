<?php

namespace li3_leaderboard\extensions\adapter\data\source\file;

use \SplFileInfo,
	\Exception;

class GitBlame extends \lithium\data\source\Mock {

	/**
	 * The config array contains all config options
	 */
	protected $_config = array();

	/**
	 * Classes to use for creation of pages
	 */
	protected $_classes = array(
	    'service' => 'lithium\net\http\Service',
	    'entity'  => 'lithium\data\entity\Document',
	    'set'     => 'lithium\data\collection\DocumentSet',
	    'relationship' => 'lithium\data\model\Relationship'
	);

	/**
	 * Query the api
	 *
	 * @param object $query
	 * @param array $options
	 */
	public function read($query, array $options = array()) {

		// Defaults
		if(empty($options['fields'])) {
			$options['fields'] = array(
				'hash', 'name', 'date', 'line', 'source',
			);
		}

		// Variables to pass
		$params = compact('query', 'options');
		$_config =& $this->_config;
		$_this = $this;

		// Filterable read request
		return $this->_filter(__METHOD__, $params, function($self, $params) use(&$_config, &$_this) {
			// Extract
			extract($params); // $query, $options

			// Params
			$info = pathinfo($options['conditions']['file']);

			// File is readable
			$file = new SplFileInfo($options['conditions']['file']);
			if(!$file->isReadable()) {
				throw new Exception('File is not readable: ' . $options['conditions']['file']);
			}

			// Test if git exists and we are in a repo
			$command = 'cd ' . $info['dirname'] . '; git rev-parse --git-dir;';
			$response = shell_exec($command);
			if(is_null($response)) {
				throw new Exception('GIT not installed, or not a GIT repo: ' . $command);
			}

			// Get the GIT Blame
			if(isset($options['conditions']['line'])) {
				// Single line
				$command = 'cd ' . $info['dirname'] . '; git blame ' . $info['basename'] . " | grep --max-count=1 '{$options['conditions']['line']})';";
			} else if(isset($options['conditions']['lines']) && is_array($options['conditions']['lines'])) {
				// Multi line
				$lineCommand = '\s(' . implode('\)|', $options['conditions']['lines']) . '\))';
				$command = 'cd ' . $info['dirname'] . '; git blame ' . $info['basename'] . " | grep -P '{$lineCommand}';";
			} else {
				// Entire file
				$command = 'cd ' . $info['dirname'] . '; git blame ' . $info['basename'] . ';';
			}
			$response = shell_exec($command);

			// Empty file?
			if(is_null($response)) {
				throw new Exception('No data found in file: ' . $options['conditions']['file']);
			}

			// Filter and split data
			$lines = explode("\n", $response);
			$pattern = '/^(\w+)\s+\(([^0-9]+)\s+([\-:0-9 ]+)\s+(\d+)\)(.*)$/'; // // 'f4d09fb8 (Nate Abele          2010-01-06 14:00:27 -0500   1) <?php'
			$fields = array(
				'hash' => 1, // hash is the 1th item in $matches
				'name' => 2, // ...
				'date' => 3,
				'line' => 4,
				'source' => 5,
			);
			foreach($lines as $key => &$line) {
				preg_match_all($pattern, $line, $matches);
				if(isset($matches[5][0])) {
					$line = array();
					foreach($options['fields'] as $field) {
						if(isset($fields[$field]) && isset($matches[$fields[$field]][0])) {
							$line[$field] = $matches[$fields[$field]][0];
						} else {
							$line[$field] = null;
						}
					}
				} else {
					unset($lines[$key]);
				}
			}

			// Group by
			if(isset($options['groupBy'])) {
				$hashTable = array();
				$groupBy = $options['groupBy'];
				if(!is_array($groupBy)) {
					$groupBy = array($groupBy);
				}
				$_this->group_by_($lines, function($el) use(&$groupBy, &$hashTable) {
					// Create hash
					$hash = null;
					foreach($groupBy as $item) {
						$hash .= $el[$groupBy];
					}
					$hash = md5($hash);
					// Find hash id for prettier keys
					if(($key = array_search($hash, $hashTable)) === false) {
						$key = count($hashTable);
						$hashTable[$key] = $hash;
					}
					return $key;
				});
			}

			// Return
			return $self->item($query->model(), $lines, array('class' => 'set'));
		});
	}

	/**
	 * Methods: group_by, group_by_
	 * 
	 * Each item will be passed into $callback and the return value will be the new "category" of this item.
	 * The param $arr will be replaced with an array of these categories with all of their items.
	 * 
	 * <code>
	 * $arr = range(1,6);
	 * $o = Enumerator::group_by($arr, function($key, &$value) {
	 * 	return ($value % 3);
	 * });
	 * print_r($o);
	 * </code>
	 * <pre>
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [0] => 3
	 *             [1] => 6
	 *         )
	 * 
	 *     [1] => Array
	 *         (
	 *             [0] => 1
	 *             [1] => 4
	 *         )
	 * 
	 *     [2] => Array
	 *         (
	 *             [0] => 2
	 *             [1] => 5
	 *         )
	 * 
	 * )
	 * </pre>
	 *
	 * @link https://github.com/BlaineSch/prettyArray
	 * @link http://ruby-doc.org/core-1.9.3/Enumerable.html#method-i-group_by
	 * @param array &$arr
	 * @param callable $callback The callback will be passed each sliced item as an array. This can be passed by reference.
	 * @param boolean $preserve_keys If you want to preserve the keys or not.
	 * @return mixed Nothing if called destructively, otherwise a new array.
	 */
	public static function group_by_(array &$arr, $callback, $preserve_keys = false) {
		$newArr = array();
		foreach($arr as $key => &$value) {
			$category = $callback($key, $value);
			if(!isset($newArr[$category])) {
				$newArr[$category] = array();
			}
			if($preserve_keys) {
				$newArr[$category][$key] = $value;
			} else {
				$newArr[$category][] = $value;
			}
		}
		ksort($newArr);
		$arr = $newArr;
		return;
	}

	/**
	 * The cast() method is used by the data source to recursively inspect and transform data as
	 * it's placed into a collection. In this case, we'll use cast() to transform arrays into Document objects.
	 *
	 * @param the query model
	 * @param the request results
	 * @param options ie(set, service, entity)
	 */
	public function cast($entity, array $data, array $options = array()) {
		
		$model = $entity->model();

		foreach ($data as $key => &$val) {
			if (!is_array($val)) {
				continue;
			}
			$val = $this->item($model, $val, array('class' => 'entity'));
		}
		return parent::cast($entity, $data, $options);
	}

}