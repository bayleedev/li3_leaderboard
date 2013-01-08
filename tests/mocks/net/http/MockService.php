<?php

/**
 * A mock model that stores its params as $self->_find_params and
 * returns whatever is in $self->_data
 */

namespace li3_leaderboard\tests\mocks\net\http;

use lithium\data\collection\DocumentSet;

class MockService {

	/**
	 * The index is the resource, the data is the return type
	 */
	public $resources = array();

	public function __construct($resources) {
		$this->resources = $resources;
	}

	/**
	 * Send HEAD request.
	 *
	 * @param array $options
	 * @return string
	 */
	public function head(array $options = array()) {
		return $this->send(__FUNCTION__, null, array(), $options);
	}

	/**
	 * Send GET request.
	 *
	 * @param string $path
	 * @param array $data
	 * @param array $options
	 * @return string
	 */
	public function get($path = null, $data = array(), array $options = array()) {
		return $this->send(__FUNCTION__, $path, $data, $options);
	}

	/**
	 * Send POST request.
	 *
	 * @param string $path
	 * @param array $data
	 * @param array $options
	 * @return string
	 */
	public function post($path = null, $data = array(), array $options = array()) {
		return $this->send(__FUNCTION__, $path, $data, $options);
	}

	/**
	 * Send PUT request.
	 *
	 * @param string $path
	 * @param array $data
	 * @param array $options
	 * @return string
	 */
	public function put($path = null, $data = array(), array $options = array()) {
		return $this->send(__FUNCTION__, $path, $data, $options);
	}

	/**
	 * Send DELETE request.
	 *
	 * @param string $path
	 * @param array $data
	 * @param array $options
	 * @return string
	 */
	public function delete($path = null, $data = array(), array $options = array()) {
		return $this->send(__FUNCTION__, $path, $data, $options);
	}

	/**
	 * Send request and return response data.
	 *
	 * @param string $method
	 * @param string $path
	 * @param array $data the parameters for the request. For GET/DELETE this is the query string
	 *        for POST/PUT this is the body
	 * @param array $options passed to request and socket
	 * @return string
	 */
	public function send($method, $path = null, $data = array(), array $options = array()) {
		print_r(func_get_args());
		return isset($this->resources[$path]) ? $this->resources[$path] : null;
	}
}

?>