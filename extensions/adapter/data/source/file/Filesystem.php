<?php

namespace li3_leaderboard\extensions\adapter\data\source\file;

use DirectoryIterator,
	ArrayIterator;

class Filesystem extends \lithium\data\source\Mock {

	/**
	 * The config array contains all config options
	 */
	protected $_config = array();

	/**
	 * Classes to use for creation of pages
	 */
	protected $_classes = array(
	    'service' => 'lithium\net\http\Service',
	    'entity'  => 'li3_leaderboard\extensions\adapter\data\entity\FileRecord',
	    'set'     => 'lithium\data\collection\RecordSet',
	    'relationship' => 'lithium\data\model\Relationship'
	);

	/**
	 * Query the api
	 *
	 * @param object $query
	 * @param array $options
	 */
	public function read($query, array $options = array()) {

		// Merge in defaults
		$options += array(
			'recursive' => false,
			'dir' => $this->_config['base'],
		);
		if(!isset($options['conditions']['name'])) {
			$options['conditions']['name'] = '/.*/';
		}

		// Variables to pass
		$params = compact('query', 'options');
		$_config =& $this->_config;

		// Filterable read request
		return $this->_filter(__METHOD__, $params, function($self, $params) use(&$_config) {
			// Extract
			extract($params); // $query, $options

			// Determine directory
			if(substr($params['options']['dir'], 0, 1) !== '/') {
				$params['options']['dir'] = $_config['base'] . $params['options']['dir'];
			}

			// A list of all the files
			$files = array();

			// The direcetories you want to traverse
			$directories = new ArrayIterator(array(
				$params['options']['dir']
			));

			// The worker bee
			foreach($directories as $dir) {
				$it = new DirectoryIterator($dir);
				foreach ($it as $fileinfo) {
					if (!$fileinfo->isDot()) {
						if($fileinfo->isDir()) {
							if($params['options']['recursive']) {
								$directories[] = $fileinfo->getPathname();
							}
						} else if(preg_match($params['options']['conditions']['name'], $fileinfo->getFilename())) {
							$files[] = array('name' => $fileinfo->getPathname());
						}
					}
				}
			}

			// Return
			return $self->item($query->model(), $files, array('class' => 'set'));
		});
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