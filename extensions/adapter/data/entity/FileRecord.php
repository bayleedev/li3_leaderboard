<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_leaderboard\extensions\adapter\data\entity;

use BadMethodCallException;
use SplFileObject;
use ArrayAccess;
use Iterator;
use lithium\data\entity\Record;

class FileRecord extends Record implements Iterator, ArrayAccess {

	/**
	 * Instance of the file object
	 */
	public $fileObj = null;

	/**
	 * Will allow you to call SplFileObject methods
	 *
	 * Methods priority:
	 *  * Model
	 *  * SplFileObject
	 *
	 * @link http://www.php.net/manual/en/class.splfileinfo.php
	 * @param  string $method
	 * @param  array $params
	 * @return mixed
	 */
	public function __call($method, $params) {
		try {
			return parent::__call($method, $params);
		} catch (BadMethodCallException $e) {
			if (method_exists('SplFileObject', $method)) {
				return call_user_func_array(array($this->fileObj(), $method), $params);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * If the SplFileObject object has not yet been created, it will create it for you.
	 *
	 * @return SplFileObject
	 */
	public function fileObj() {
		if (is_null($this->fileObj)) {
			$this->fileObj = new SplFileObject($this->name);
		}
		return $this->fileObj;
	}

	/**
	 * Part of the Iterator interface
	 *
	 * @link http://www.php.net/manual/en/class.iterator.php
	 * @return mixed
	 */
	public function current() {
		return $this->fileObj()->current();
	}

	/**
	 * Part of the Iterator interface
	 *
	 * @link http://www.php.net/manual/en/class.iterator.php
	 * @return mixed
	 */
	public function key() {
		return $this->fileObj()->key();
	}

	/**
	 * Part of the Iterator interface
	 *
	 * @link http://www.php.net/manual/en/class.iterator.php
	 * @return mixed
	 */
	public function next() {
		return $this->fileObj()->next();
	}

	/**
	 * Part of the Iterator interface
	 *
	 * @link http://www.php.net/manual/en/class.iterator.php
	 * @return mixed
	 */
	public function rewind() {
		return $this->fileObj()->rewind();
	}

	/**
	 * Part of the Iterator interface
	 *
	 * @link http://www.php.net/manual/en/class.iterator.php
	 * @return mixed
	 */
	public function valid() {
		return $this->fileObj()->valid();
	}

	/**
	 * Part of the ArrayAccess interface
	 *
	 * @link http://php.net/manual/en/class.arrayaccess.php
	 * @return boolean
	 */
	public function offsetExists($key) {
		$originalKey = $this->key();

		$this->fileObj()->seek($key);
		$exists = $this->fileObj()->valid();

		$this->fileObj()->seek($originalKey);

		return $exists;
	}

	/**
	 * Part of the ArrayAccess interface
	 *
	 * @link http://php.net/manual/en/class.arrayaccess.php
	 * @return boolean
	 */
	public function offsetGet($key) {
		$originalKey = $this->key();
		$value = null;

		$this->fileObj()->seek($key);
		if ($this->fileObj()->valid()) {
			$value = $this->fileObj()->current();
		}

		$this->fileObj()->seek($originalKey);

		return $value;
	}

	/**
	 * Part of the ArrayAccess interface
	 *
	 * @link http://php.net/manual/en/class.arrayaccess.php
	 * @throws BadFunctionCallException
	 * @return null
	 */
	public function offsetSet($key, $value) {
		throw new \BadFunctionCallException('Write operations are not permitted');
	}

	/**
	 * Part of the ArrayAccess interface
	 *
	 * @link http://php.net/manual/en/class.arrayaccess.php
	 * @throws BadFunctionCallException
	 * @return null
	 */
	public function offsetUnset($key) {
		throw new \BadFunctionCallException('Write operations are not permitted');
	}

}

?>