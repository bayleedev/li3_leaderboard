<?php

namespace li3_leaderboard\extensions\adapter\data\source\file;

use SplFileInfo,
	Exception;

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
		if (empty($options['fields'])) {
			$options['fields'] = array(
				'hash', 'name', 'date', 'line', 'source',
			);
		}

		$params = compact('query', 'options');
		$_config =& $this->_config;
		$_this = $this;

		$method = __METHOD__;
		return $this->_filter($method, $params, function($self, $params) use(&$_config, &$_this) {
			extract($params);

			$info = pathinfo($options['conditions']['file']);

			$file = new SplFileInfo($options['conditions']['file']);
			if (!$file->isReadable()) {
				throw new Exception('File is not readable: ' . $options['conditions']['file']);
			}

			$command = 'cd ' . $info['dirname'] . '; git rev-parse --git-dir 2>&1;';
			$response = shell_exec($command);
			if (preg_match('/Not a git repository/', $response) === 1) {
				throw new Exception('GIT not installed, or not a GIT repo: ' . $command);
			}

			$lineSet = isset($options['conditions']['line']);
			$linesSet = isset($options['conditions']['lines']);
			if ($lineSet) {
				$command = 'cd ' . $info['dirname'] . '; git blame ' . $info['basename'];
				$command .= " | grep --max-count=1 '{$options['conditions']['line']})';";
			} elseif ($linesSet && is_array($options['conditions']['lines'])) {
				$lineCommand = '\s(' . implode('\)|', $options['conditions']['lines']) . '\))';
				$command = 'cd ' . $info['dirname'] . '; git blame ' . $info['basename'];
				$command .= " | grep -P '{$lineCommand}';";
			} else {
				$command = 'cd ' . $info['dirname'] . '; git blame ' . $info['basename'] . ';';
			}
			$response = shell_exec($command);

			if (is_null($response)) {
				throw new Exception('No data found in file: ' . $options['conditions']['file']);
			}

			$lines = explode("\n", $response);
			$pattern = '/^(\w+)\s+\(([^0-9]+)\s+([\-:0-9 ]+)\s+(\d+)\)(.*)$/';
			$fields = array(
				'hash' => 1,
				'name' => 2,
				'date' => 3,
				'line' => 4,
				'source' => 5,
			);
			foreach ($lines as $key => &$line) {
				preg_match_all($pattern, $line, $matches);
				if (isset($matches[5][0])) {
					$line = array();
					foreach ($options['fields'] as $field) {
						if (isset($fields[$field]) && isset($matches[$fields[$field]][0])) {
							$line[$field] = $matches[$fields[$field]][0];
						} else {
							$line[$field] = null;
						}
					}
				} else {
					unset($lines[$key]);
				}
			}

			if (isset($options['groupBy'])) {
				$hashTable = array();
				$groupBy = $options['groupBy'];
				if (!is_array($groupBy)) {
					$groupBy = array($groupBy);
				}
				$_this->_groupBy($lines, function($key, $el) use(&$groupBy, &$hashTable) {
					$hash = null;
					foreach ($groupBy as $item) {
						$hash .= $el[$item];
					}
					$hash = md5($hash);
					if (($key = array_search($hash, $hashTable)) === false) {
						$key = count($hashTable);
						$hashTable[$key] = $hash;
					}
					return $key;
				});
			}

			return $self->item($query->model(), $lines, array('class' => 'set'));
		});
	}

	/**
	 * Each item will be passed into $callback and the return value will be the
	 * new "category" of this item. The param $arr will be replaced with an
	 * array of these categories with all of their items.
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
	 * @param callable $callback The callback will be passed each sliced item as an array.
	 * @param boolean $preserveKeys If you want to preserve the keys or not.
	 * @return mixed Nothing if called destructively, otherwise a new array.
	 */
	protected static function _groupBy(array &$arr, $callback, $preserveKeys = false) {
		$newArr = array();
		foreach ($arr as $key => &$value) {
			$category = $callback($key, $value);
			if (!isset($newArr[$category])) {
				$newArr[$category] = array();
			}
			if ($preserveKeys) {
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
	 * The cast() method is used by the data source to recursively inspect and
	 * transform data as it's placed into a collection. In this case, we'll use
	 * cast() to transform arrays into Document objects.
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

?>