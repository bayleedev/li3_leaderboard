<?php

namespace li3_leaderboard\tests\cases\models;

use li3_leaderboard\tests\Unit;

class StatsPresenterTest extends Unit {

	public static $model = 'li3_leaderboard\\models\\StatsPresenter';

	public static $blameModel = 'li3_leaderboard\\models\\BlameTest';

	public static function create($data = array(), $class = null) {
		$data += array();
		$class = is_null($class) ? self::$model : $class;
		return $class::create($data);
	}

	public function testFindBlames() {
		$files = array(
			array(
				'name' => dirname(dirname(__DIR__)) . '/mocks/mock.txt',
				'lines' => array(1),
			),
		);
		$class = self::$model;
		$result = $class::_findBlames($files);

		$this->assertCount(1, $result[0]);
		$this->assertEqual('bd659387', $result[0][0]->hash);
	}

	public function testTotalTestsComplex() {
		// Setup
		$files = array(
			array(
				'name' => '/mocks/mock1.txt',
				'lines' => array(1,2,3),
			),
			array(
				'name' => '/mocks/mock2.txt',
				'lines' => array(1),
			),
			array(
				'name' => '/mocks/mock3.txt',
				'lines' => array(999, 432),
			),
		);
		$class = self::$model;

		$result = $class::_totalTests($files);
		$expected = 6;

		$this->assertEqual($expected, $result);
	}

	public function testTotalTestsSimple() {
		// Setup
		$files = array(
			array(
				'name' => '/mocks/mock1.txt',
				'lines' => array(1,2,3),
			),
		);
		$class = self::$model;

		$result = $class::_totalTests($files);
		$expected = 3;

		$this->assertEqual($expected, $result);
	}

	public function testGetTotalByPerson() {
		// Setup
		$files = array(
			array(
				'name' => dirname(dirname(__DIR__)) . '/mocks/mock.txt',
				'lines' => array(1),
			),
		);
		$class = self::$model;
		$blames = $class::_findBlames($files);
		$total = 1;

		$result = $class::_getTotalByPerson($blames, $total);
		$expected = array(
			'Blaine Schmeisser' => array(
				'count' => $total,
				'percent' => 100,
			),
		);

		$this->assertEqual($expected, $result);
	}

	public function testTotalByPersonSort() {
		// Setup
		$class = self::$model;
		$data = array(
			'Blaine Schmeisser' => array(
				'count' => 199,
				'percent' => 48.5,
			),
			'Tim Morgan' => array(
				'count' => 200,
				'percent' => 50.5,
			),
			'Chris Collins' => array(
				'count' => 1,
				'percent' => 1,
			),
		);

		// Results
		$result = $data;
		$class::_sortTotals($data);
		$expected = array(
			'Tim Morgan' => array(
				'count' => 200,
				'percent' => 50.5,
			),
			'Blaine Schmeisser' => array(
				'count' => 199,
				'percent' => 48.5,
			),
			'Chris Collins' => array(
				'count' => 1,
				'percent' => 1,
			),
		);

		// Test
		$this->assertEqual($expected, $result);
	}

}