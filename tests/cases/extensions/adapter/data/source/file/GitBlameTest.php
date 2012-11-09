<?php

namespace li3_leaderboard\tests\cases\extensions\adapter\data\source\file;

use \li3_leaderboard\tests\Unit,
	\li3_leaderboard\models\Blame;

class GitBlameTest extends Unit {

	public static $model = 'li3_leaderboard\\models\\Blame';

	public function create($data) {
		$data += array(
			// defaults
		);
		$class = self::$model;
		$model::create($data);
		$page->connection()->connection = new MockService(array(
			'request' => 'response'
		));
	}

	public function testSingleLine() {
		$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
		$blames = Blame::find('first', array(
			'conditions' => array(
				'file' => $file,
				'line' => 1,
			),
		));
		
		$this->assertEqual(1, $blames->line);
		$this->assertEqual('bd659387', $blames->hash);
	}

	public function testMultiLine() {
		$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
		$blames = Blame::find('all', array(
			'conditions' => array(
				'file' => $file,
				'lines' => array(1, 2),
			),
		));

		$this->assertCount(2, $blames);
		
		$this->assertEqual(1, $blames[0]->line);
		$this->assertEqual('bd659387', $blames[0]->hash);
		
		$this->assertEqual(2, $blames[1]->line);
		$this->assertEqual('0a7fd3e9', $blames[1]->hash);
	}

	public function testLessFields() {
		$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
		$blames = Blame::find('first', array(
			'conditions' => array(
				'file' => $file,
				'line' => 1,
			),
			'fields' => array(
				'hash',
			),
		));

		$this->assertTrue(isset($blames->hash));
		$this->assertFalse(isset($blames->name));
		$this->assertFalse(isset($blames->date));
		$this->assertFalse(isset($blames->line));
		$this->assertFalse(isset($blames->date));
	}

	public function testNonGitRepo() {
		try {
			$blames = Blame::find('first', array(
				'conditions' => array(
					'file' => '/',
					'line' => 1,
				),
			));
		} catch(\Exception $e) {
			$this->assertPattern('/GIT not installed/', $e->getMessage());
			return;
		}
		$this->assertTrue(false);
	}

	public function testUnreadableFile() {
		try {
			$blames = Blame::find('first', array(
				'conditions' => array(
					'file' => '/thisfileshouldnotexist.txt.r2',
					'line' => 1,
				),
			));
		} catch(\Exception $e) {
			$this->assertPattern('/File is not readable/', $e->getMessage());
			return;
		}
		$this->assertTrue(false);
	}

	public function testNoBlameData() {
		try {
			$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
			$blames = Blame::find('first', array(
				'conditions' => array(
					'file' => $file,
					'line' => 999,
				),
			));
		} catch(\Exception $e) {
			$this->assertPattern('/No data found/', $e->getMessage());
			return;
		}
		$this->assertTrue(false);
	}

	public function testGroupByName() {
		$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
		$blames = Blame::find('all', array(
			'conditions' => array(
				'file' => $file,
				'lines' => array(1,2),
			),
			'groupBy' => 'name',
		))->to('array');

		$this->assertCount(1, $blames); // Single group
		$this->assertCount(2, $blames[0]); // All by Blaine
	}

	public function testGroupByHash() {
		$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
		$blames = Blame::find('all', array(
			'conditions' => array(
				'file' => $file,
				'lines' => array(1,2),
			),
			'groupBy' => 'hash',
		))->to('array');

		$this->assertCount(2, $blames); // Two groups
	}

	public function testGroupByNameHash() {
		$file = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/mocks/mock.txt';
		$blames = Blame::find('all', array(
			'conditions' => array(
				'file' => $file,
				'lines' => array(1,2),
			),
			'groupBy' => array('name', 'hash'),
		))->to('array');

		$this->assertCount(2, $blames); // Two groups
	}

}