<?php

namespace Golem\Traits;

use

	Exception

;


/**
 * Common functionality for sealing objects.
 *
 * The configuration of a sealed object can no longer be changed.
 * Eg. A sealed logger cannot be disabled, have it's loglevel changed,
 * be redirected to another logfile, ...
 *
 */
trait Seal
{

/**
 * @var bool Whether the object is sealed.
 */
private   $sealed = false ;


/**
 * Seal the current object so it's options cannot be changed anymore.
 *
 * Since this is a security library, we want clients to be sure that certain settings don't change
 * anymore. It's best practice to define your security configuration in one place and then seal
 * objects so they won't change anymore by php code included later.
 *
 * Note: Currently (php 5.6) there is a reflection module in PHP which allows code to write to
 *       private properties on objects from the outside. One possibility to solve this is to
 *       disable the "ReflectionClass" in php.ini.
 *
 *       In order for this to be useful you also have to store your php code and configuration
 *       in a place where the php or webserver user does not have write privileges.
 *
 * @return mixed $this.
 *
 * @api
 *
 */
public
function seal()
{
	$this->sealed = true;

	return $this;
}



/**
 * Tells you whether the current object is sealed.
 *
 * @return bool Whether the object is sealed.
 *
 * @api
 *
 */
public
function sealed()
{
	return $this->sealed;
}



protected
function checkSeal()
{
	if( $this->sealed )

		$this->log->runTimeException( "Cannot change option on sealed object." );
}
}
