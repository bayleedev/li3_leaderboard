<?php

namespace li3_leaderboard\tests;

class Unit extends \lithium\test\Unit {

	/**
	 * Will return true if $count and count($arr) are equal
	 *
	 * @param  int $count Expected value
	 * @param  array $array Result
	 * @param  string $message optional
	 * @return mixed
	 */
	public function assertCount($count, $array, $message = '') {
		if (is_array($array) || $array instanceof \Countable) {
			if (empty($message)) {
				$message = 'Array did not have ' . $count . ' element: ' . print_r($array, true);
			}
			return $this->assertEqual(count($array), $count, $message);
		} else {
			$message = 'Second argument must be an array or extend Countable.';
			throw new \InvalidArgumentException($message);
		}
	}
}

?>