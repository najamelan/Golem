<?php

/**
 * This file demonstrates how you can use the logger functionality that comes with Golem, in order
 * to log to a file.
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
$golem = new Golem;


// We seal the library so options cannot be overridden by code included later on. Note that all public functions
// of the Golem object are documented in the api documentation for the Golem class.
//
$golem->seal();


// We get a logger. The available options are documented in iFace\LogOptions.
//
// name  : The default prefix from Golem will apply, so our logger will be called 'Golem.Examples'
// output: Where to log to. Possible values are 'echo' and 'file'. Future features might be adding
//         email or rss etc...
//
// We could also have instantiated Golem\Data\LogOptions and pass that to $golem->logger( $options ).
// This allows amongst other things to set the options up using a yaml file instead of a PHP array. Note that
// Golem::logger() does not accept filenames directly.
//
//
$file = __DIR__ . "/useLoggerFile.log";


// Let's empty if first for this example
//
if( file_exists( $file ) )

	file_put_contents( $file, '' );


$log = $golem->logger( 'Examples.useLoggerFile', [ 'logfile' => [ $file ] ] )->seal();


// Now we can pass this object around in our code to log events.
//
$log->notice   ( 'Server load reached 80%'       );
$log->warning  ( 'Function DB::mysql deprecated' );
$log->error    ( 'Unhandled exception'           );
$log->exception( 'Some situation needs to be handled.'   );
