<?php

namespace li3_leaderboard\tests\cases\models;

use lithium\test\Unit;
use li3_leaderboard\models\Files;

require_once(__DIR__ . '/../../../../../config/bootstrap/connections.php');

class FileTest extends Unit {

	public $base;

	public function setUp() {
		$this->base = __DIR__ . '/../../mocks/directoryMock/';
	}

	/**
	 * @covers Filesystem::read
	 */
	public function testNameFilterMockSimple() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/mock/',
			),
		));
		$expected = 'mockFile.txt';
		$result = $file->name();
		$this->assertEqual($expected, $result);
	}

	/**
	 * @covers Filesystem::read
	 */
	public function testNameFilterFooSimple() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));
		$expected = 'foobarMock.txt';
		$result = $file->name();
		$this->assertEqual($expected, $result);
	}

	/**
	 * @covers Filesystem::read
	 */
	public function testBadName() {
		$files = Files::find('all', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/nothingshouldmatchthis/',
			),
		));
		$expected = 0;
		$result = count($files);
		$this->assertEqual($expected, $result);
	}

	/**
	 * @covers Filesystem::read
	 */
	public function testRecursiveByNameMock() {
		$files = Files::find('all', array(
			'recursive' => false,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/mock/',
			),
		));
		$expected = 1;
		$result = count($files);
		$this->assertEqual($expected, $result);
	}

	/**
	 * @covers Filesystem::read
	 */
	public function testRecursiveByNameFoo() {
		$files = Files::find('all', array(
			'recursive' => false,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));
		$expected = 0;
		$result = count($files);
		$this->assertEqual($expected, $result);
	}

	/**
	 * @covers FileRecord::offsetGet
	 */
	public function testArrayAccessGet() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));

		$expected = '/^Lorem/';
		$this->assertPattern($expected, $file[0]);
	}

	/**
	 * @covers FileRecord::offsetExists
	 */
	public function testArrayAccessExistsLow() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));
		$this->assertTrue($file->offsetExists(0));
	}

	/**
	 * @covers FileRecord::offsetExists
	 */
	public function testArrayAccessExistsHigh() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));
		$this->assertFalse($file->offsetExists(99999));
	}

	/**
	 * @covers FileRecord::offsetSet
	 */
	public function testArrayAccessSet() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));
		try {
			$file[0] = 'foobar';
		} catch(\BadFunctionCallException $e) {
			$this->assertTrue(true);
			return;
		}
		$this->assertTrue(false, 'BadFunctionCallException was not thrown.');
	}

	/**
	 * @covers FileRecord::offsetUnset
	 */
	public function testArrayAccessUnset() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));
		try {
			unset($file[0]);
		} catch(\BadFunctionCallException $e) {
			$this->assertTrue(true);
			return;
		}
		$this->assertTrue(false, 'BadFunctionCallException was not thrown.');
	}

	/**
	 * @covers FileRecord::current
	 */
	public function testIteratorCurrent() {
		$file = Files::find('first', array(
			'recursive' => true,
			'dir' => $this->base,
			'conditions' => array(
				'name' => '/foo/',
			),
		));

		$expected = '/^Lorem/';
		$result = $file->current();
		$this->assertPattern($expected, $result);
	}

	/**
	 * @covers FileRecord::key
	 */
	public function testIteratorKey() {
		$file = Files::find('first', array(
			'recursive' => false,
			'dir' => $this->base,
		));
		$expected = 0;
		$response = $file->key();
		$this->assertEqual($expected, $response);
	}

	/**
	 * @covers FileRecord::next
	 */
	public function testIteratorNext() {
		$file = Files::find('first', array(
			'recursive' => false,
			'dir' => $this->base,
		));
		$this->assertEqual(0, $file->key());
		$file->next();
		$this->assertEqual(1, $file->key());
	}

	/**
	 * @covers FileRecord::rewind
	 */
	public function testIteratorRewind() {
		$file = Files::find('first', array(
			'recursive' => false,
			'dir' => $this->base,
		));
		$this->assertEqual(0, $file->key());
		$file->next();
		$this->assertEqual(1, $file->key());
		$file->next();
		$this->assertEqual(2, $file->key());
		$file->rewind();
		$this->assertEqual(0, $file->key());
	}

	/**
	 * @covers FileRecord::__call
	 * @covers Files::name
	 */
	public function testName() {
		$file = Files::find('first', array(
			'recursive' => false,
			'dir' => $this->base,
		));
		$name = $file->name();
		$expected = 'mockFile.txt';
		$this->assertEqual($expected, $name);
	}

	/**
	 * @covers FileRecord::__call
	 */
	public function testSplFileObject() {
		$file = Files::find('first', array(
			'recursive' => false,
			'dir' => $this->base,
		));
		$name = $file->getFileName();
		$expected = 'mockFile.txt';
		$this->assertEqual($expected, $name);
	}

}