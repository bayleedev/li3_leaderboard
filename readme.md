# Lithium Leaderboard

A leaderboard to show off which developers are creating unit tests

[![Build Status](https://secure.travis-ci.org/BlaineSch/li3_leaderboard.png?branch=master)](http://travis-ci.org/BlaineSch/li3_leaderboard)

## Installation

### Composer
~~~ json
{
    "require": {
        ...
        "blainesch/li3_leaderboard": "dev-master"
        ...
    }
}
~~~
~~~ bash
php composer.phar install
~~~

### Submodule
~~~ bash
git submodule add git://github.com/BlaineSch/li3_leaderboard.git libraries/li3_leaderboard
~~~

### Clone Directly
~~~ bash
git clone git://github.com/BlaineSch/li3_leaderboard.git libraries/li3_leaderboard
~~~


## Setting up
In your libraries.php file:

~~~ php
<?php
// ...
if(!lithium\core\Environment::is('production')) {
	Libraries::add('li3_leaderboard', array(
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

## Screenshot
![Leaderboard](http://i49.tinypic.com/2n6ad5s.png)