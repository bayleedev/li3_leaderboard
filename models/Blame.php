<?php

namespace li3_testLeaderboard\models;

class Blame extends \lithium\data\Model {

	/**
	 * Tell it to use the amazing GitBlame adapter
	 * @var array
	 */
	public $_meta = array('connection' => 'GitBlame');

	/**
	 * Just return the trimmed name
	 * @return  string
	 */
	public function name($entity) {
		return trim($entity->name);
	}

}