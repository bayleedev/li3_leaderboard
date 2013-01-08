<?php

namespace li3_leaderboard\models;

use li3_leaderboard\models\Files,
	li3_leaderboard\models\Blame;

class StatsPresenter extends \lithium\data\Model {

	/**
	 * Finds the specific files and lines that we determine to be tests
	 *
	 * @param  array $options
	 * @return array A multidimensional array.
	 */
	public static function findLines($options) {
		$fileInfo = array();

		foreach ($options['paths'] as $path) {
			foreach ($options['files'] as $name => $prototype) {
				$files = Files::find('all', array(
					'recursive' => true,
					'dir' => $path,
					'conditions' => array(
						'name' => $name,
					),
				));
				foreach ($files as $file) {
					$lines = array();
					foreach ($file as $key => $line) {
						if (preg_match($prototype, $line, $matches) === 1) {
							$lines[] = $key + 1;
						}
					}
					if (!empty($lines)) {
						$fileInfo[] = array(
							'name' => $file->getPath() . '/' . $file->getFilename(),
							'lines' => $lines,
						);
					}
				}
			}
		}
		return $fileInfo;
	}

	/**
	 * Given an multidimensional array it'll find the blame for specific files and lines
	 *
	 * @param  array $files The result of self::_findLines
	 * @return array
	 */
	public static function findBlames($files) {
		$blames = array();
		foreach ($files as $file) {
			$blames[] = Blame::find('all', array(
				'conditions' => array(
					'file' => $file['name'],
					'lines' => $file['lines'],
				),
			));
		}
		return $blames;
	}

	/**
	 * Will give you a count of written tests.
	 *
	 * @param  array $files The result of self::_findLines
	 * @return int
	 */
	public static function totalTests($files) {
		$total = 0;
		foreach ($files as $file) {
			$total += count($file['lines']);
		}
		return $total;
	}

	/**
	 * Will aggregate the data and return a count and percent for each user.
	 *
	 * @param  array $blames result of self::_findBlames
	 * @param  int $total  result of self::_totalTests
	 * @return array
	 */
	public static function getTotalByPerson($blames, $total) {
		$totals = array();
		foreach ($blames as $blameLines) {
			foreach ($blameLines as $line) {
				if (!isset($totals[$line->name()])) {
					$totals[$line->name()] = 0;
				}
				$totals[$line->name()]++;
			}
		}
		foreach ($totals as $person => &$count) {
			$count = array(
				'count' => $count,
				'percent' => round(($count / $total) * 100, 1),
			);
		}
		return $totals;
	}

	/**
	 * Will sort the data based on the count of two elements
	 *
	 * @param  array $data return of self::_getTotalByPerson
	 * @return array
	 */
	public static function sortTotals(&$data) {
		uasort($data, function($el, $el2) {
			return strnatcmp($el2['count'], $el['count']);
		});
	}

	/**
	 * Entry point for
	 *
	 * @param  [type] $type    [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public static function find($type, array $options = array()) {
		$leaderBoard = array();

		$files = self::findLines($options);
		$blames = self::findBlames($files);

		$leaderBoard['total'] = self::totalTests($files);
		$leaderBoard['data'] = self::getTotalByPerson($blames, $leaderBoard['total']);

		self::sortTotals($leaderBoard['data']);

		return $leaderBoard;
	}

}

?>