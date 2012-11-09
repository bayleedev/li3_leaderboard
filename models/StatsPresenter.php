<?php

namespace li3_testLeaderboard\models;

use \li3_filesystem\models\Files;
use li3_testLeaderboard\models\Blame;

class StatsPresenter extends \lithium\data\Model {

	/**
	 * Finds the specific files and lines that we determine to be tests
	 * @param  array $options
	 * @return array A multidimensional array.
	 */
	static public function _findLines($options) {
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

	static public function _findBlames($files) {
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

	static public function _totalTests($files) {
		$total = 0;
		foreach($files as $file) {
			$total += count($file['lines']);
		}
		return $total;
	}

	static public function _getTotalByPerson($blames, $total) {
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

	static public function find($count, array $options) {

		$leaderBoard = array();

		// Get files with lines to blame
		$files = self::_findLines($options);

		// Parse files and lines and get data
		$blames = self::_findBlames($files);

		$leaderBoard['total'] = self::_totalTests($files);
		$leaderBoard['data'] = self::_getTotalByPerson($blames, $leaderBoard['total']);

		uasort($leaderBoard['data'], function($el, $el2) {
			return strcmp($el['count'], $el2['count']);
		});

		return $leaderBoard;

	}

}