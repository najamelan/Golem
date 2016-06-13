<?php

/**
 * This file demonstrates how you can use the logger functionality that comes with Golem.
 *
 */


namespace Golem\Examples;

use Golem\Golem;


// This should be the only include you ever need unless you want to override
// implementation classes
//
require_once '../../src/Golem.php';


// We create a library object to interact with Golem.
//
$golem = new Golem();


// We seal the library so options cannot be overridden by code included later on. Note that all public functions
// of the Golem object are documented in the api documentation for the Golem class.
//
$golem->seal();


$s = $golem->text( 'ola' );
$s = $s->split();
var_dump( $s );
