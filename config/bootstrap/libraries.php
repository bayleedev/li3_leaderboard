<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\Libraries;
use lithium\core\Environment;

/**
 * Config
 */
if(file_exists(__DIR__ . '/../../vendor/')) {
	$_filesystem_path = dirname(dirname(__DIR__)) . '/vendor/blainesch/li3_filesystem';
} else {
	$_filesystem_path = LITHIUM_APP_PATH . "/libraries/li3_filesystem";
}

/**
 * Add some plugins:
 */
Libraries::add('li3_filesystem', array(
	'path' => $_filesystem_path
));