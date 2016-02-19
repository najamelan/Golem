<?php

/**
 * This file demonstrates how you can display and clean metadata from a jpg with Golem.
 *
 */


namespace Golem\Examples;

use Golem\Golem;


// This should be the only include you ever need unless you want to override
// implementation classes
//
require_once '../../src/Golem.php';


// We create a library object to interact with Golem.
// In this case we set the Mat class to create backups, so we can reuse the file in the example.
//
$golem = new Golem( [ 'Mat' => [ 'backup' => true ] ] );


// We seal the library so options cannot be overridden by code included later on. Note that all public functions
// of the Golem object are documented in the api documentation for the Golem class.
//
$golem->seal();


$jpg = $golem->file( 'dirty é.jpg' );

echo $jpg->metaData (), PHP_EOL;
echo $jpg->cleanMeta(), PHP_EOL;


