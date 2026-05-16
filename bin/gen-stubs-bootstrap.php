<?php
/**
 * Bootstrap for composer gen-stubs.
 *
 * php-stubs/generator calls class_exists() on every parsed class. Composer's
 * PSR-4 autoloader (mapped Matriq\\MSA\\ -> plugin root) would then include
 * the source file, which hits `defined( 'ABSPATH' ) || exit;` and kills the
 * process silently. Define ABSPATH and drop our PSR-4 prefix here so the
 * generator can parse without ever loading our classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->setPsr4( 'Matriq\\MSA\\', array() );

require __DIR__ . '/../vendor/php-stubs/generator/bin/generate-stubs';
