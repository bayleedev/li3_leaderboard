<?php

namespace li3_leaderboard\models;

/**
 * Generic Files model
 */
class Files extends \lithium\data\Model {

	/**
	 * Tell it to use the amazing Filesystem adapter
	 * @var array
	 */
	public $_meta = array('connection' => 'Filesystem');

	/**
	 * Will return the name of the file
	 * 
	 * @param  object $entity This $entity will be inserted using magic.
	 * @return string
	 */
	public function name($entity) {
		return $entity->fileObj()->getFilename();
	}

}