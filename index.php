<?php

/**
 * This is the entry script for PocketPHP server platform.
 * It initializes server's startup configuration and server's
 * route map by requiring configuration (core/config.php) and
 * routing (core/routes.php) scripts.
 * 
 * 
 * @package PocketPHP
 * @version 1.0.0
 * @author  John Theco <john.theco.dev@gmail.com>
 * @license MIT
 * 
 * @link    https://github.com/johntheco/pocketphp
 * @see     https://github.com/johntheco/pocketphp#readme
 */


// Loading vendor
if (file_exists('vendor/autoload.php')) {
    require_once('vendor/autoload.php');
}

// Loading PocketPHP core
if (file_exists('core/autoload.php')) {
    require_once('core/autoload.php');
}
