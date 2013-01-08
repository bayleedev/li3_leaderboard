<?php

namespace li3_leaderboard\tests\cases\models;

use li3_leaderboard\tests\Unit;

class BlameTest extends Unit {

	public static $model = 'li3_leaderboard\\models\\Blame';

	public static function create($data = array()) {
		$data += array(
			'name' => 'Blaine Schmeisser     ',
		);
		$class = self::$model;
		return $class::create($data);
	}

	public function testName() {
		$model = self::create(array(
			'name' => 'Blaine Schmeisser     ',
		));
		$expected = 'Blaine Schmeisser';
		$this->assertEqual($expected, $model->name($model));
	}

}

?>