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


// We get a logger. The available options are documented in iFace\LogOptions.
//
// The first parameter is a mandatory name for this logger.
// The second parameter are options to override defaults in case of making a new logger. Here we choose to
// echo the messages rather than to log them to the php log file which would be the default. You can
// also log to another file if desired. See the default configuration file and the api documentation for more.
//
// When getting an existing logger, no options can be passed and an exception will be thrown if done so.
//
// We seal the logger, protecting it from changes by later code.
//
$log = $golem->logger( 'Examples.useLoggerPHP', [ 'logfile' => 'phplog' ] )->seal();


// Now we can pass this object around in our code to log events.
//
$log->notice   ( 'Server load reached 80%'               );
$log->warning  ( 'Function DB::mysql deprecated'         );
$log->error    ( 'Unhandled exception'                   );
$log->exception( 'Some situation needs to be handled.'   );
