<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\net\http\Router;

// View non-published drafts:
Router::connect('/testLeaderboard', array(
	'controller' => 'Leaderboard',
	'action' => 'index',
	'library' => 'li3_testLeaderboard',
));