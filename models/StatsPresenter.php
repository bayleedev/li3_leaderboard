<?php

namespace li3_leaderboard\models;

use \li3_filesystem\models\Files;
use li3_leaderboard\models\Blame;

class StatsPresenter extends \lithium\data\Model {

	/**
	 * Finds the specific files and lines that we determine to be tests
	 * 
	 * @param  array $options
	 * @return array A multidimensional array.
	 */
	public static function _findLines($options) {
		// Variables
		$fileInfo = array();

		// Find lines to blame!
		foreach($options['paths'] as $path) { // Iterate paths
			foreach($options['files'] as $name => $prototype) { // Iterate specific file types
				$files = Files::find('all', array(
					'recursive' => true,
					'dir' => $path,
					'conditions' => array(
						'name' => $name,
					),
				));
				foreach($files as $file) { // Iterate specific files
					$lines = array();

					// Find files
					foreach($file as $key => $line) { // Iterate specific lines
						if(preg_match($prototype, $line, $matches) === 1) {
							// Found item to blame!
							$lines[] = $key+1; // file line(1+) and array index(0+) have an offset of 1
						}
					}

					// Add to blame list!
					if(!empty($lines)) {
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
	public static function _findBlames($files) {
		$blames = array();
		foreach($files as $file) {
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
	public static function _totalTests($files) {
		$total = 0;
		foreach($files as $file) {
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
	public static function _getTotalByPerson($blames, $total) {
		$totals = array();
		foreach($blames as $blameLines) { // iterate recordsets
			foreach($blameLines as $line) { // Iterate records
				if(!isset($totals[$line->name()])) {
					$totals[$line->name()] = 0;
				}
				$totals[$line->name()]++;
			}
		}
		foreach($totals as $person => &$count) {
			$count = array(
				'count' => $count,
				'percent' => round(($count/$total)*100, 1),
			);
		}
		return $totals;
	}

	/**
	 * Will sort the data based on the count of two elements
	 * @param  array $data return of self::_getTotalByPerson
	 * @return array
	 */
	public static function _sortTotals(&$data) {
		uasort($data, function($el, $el2) {
			return strnatcmp($el2['count'], $el['count']);
		});
	}

	/**
	 * Entry point for 
	 * @param  [type] $type    [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public static function find($type, array $options = array()) {

		$leaderBoard = array();

		// Get files with lines to blame
		$files = self::_findLines($options);

		// Parse files and lines and get data
		$blames = self::_findBlames($files);

		$leaderBoard['total'] = self::_totalTests($files);
		$leaderBoard['data'] = self::_getTotalByPerson($blames, $leaderBoard['total']);

		$leaderBoard['data'] = self::_sortTotals($leaderBoard['data']);

		return $leaderBoard;

	}

}