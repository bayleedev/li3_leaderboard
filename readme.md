# Test Leaderboard
A leaderboard to show off which developers are creating unit tests

## Installation

### Composer
~~~ json
{
    "require": {
        ...
        "blainesch/li3_testLeaderboard": "dev-master"
        ...
    }
}
~~~
~~~ bash
php composer.phar install
~~~

### Submodule
~~~ bash
git submodule add git://github.com/BlaineSch/li3_testLeaderboard.git libraries/li3_testLeaderboard
~~~

### Clone Directly
~~~ bash
git clone git://github.com/BlaineSch/li3_testLeaderboard.git libraries/li3_testLeaderboard
~~~


## Setting up
In your libraries.php file:

~~~ php
<?php
// ...
if(!lithium\core\Environment::is('production')) {
	Libraries::add('li3_testLeaderboard', array(
		'paths' => array(
			dirname(dirname(__DIR__)) . '/tests/',
		),
		'files' => array(
			'/Test\.php$/' => '/function test/',
			'/\.feature$/' => '/Scenario:/',
		),
	));
}
// ...
?>
~~~

## Running
To view the results simply view:
~~~ bash
localhost/testLeaderboard
~~~~