<?php

namespace Golem\Traits;

use

	  Golem\Util

	, UnexpectedValueException

;


/**
 * All objects in Golem have options, this is the common code shared by all of them.
 *
 */
trait HasOptions
{


/**
 * @var The configuration of this object. Is an associative array.
 *
 */
protected $options   = [];


/**
 * @var The default configuration of this object. Is an associative array.
 *
 */
protected $defaults  = [];


/**
 * @var The configuration options overridden by the client. Is an associative array.
 *
 */
protected $userset   = [];




/**
 * Helper for constructors of classes that have options. Does basic merging of defaults
 * with userset options. For subclasses you should call parent::__construct before calling
 * this method, so your options will override those set by the parent class.
 *
 * @param array  $defaults The default options for this class.
 * @param array  $userset  The options passed in to the constructor.
 *
 * @return $this.
 *
 */
protected
function  setupOptions( array $defaults, array $userset )
{
	$this->defaults = Util::joinAssociativeArray( $this->defaults, $defaults      );
	$this->userset  = Util::joinAssociativeArray( $this->userset , $userset       );
	$this->options  = Util::joinAssociativeArray( $this->defaults, $this->userset );

	return $this;
}


/**
 * Provides a read only copy of the options object for this class.
 *
 * @return array The currently loaded configuration for this object.
 *
 * @api
 *
 */
private
function getOpts( $pointer, $args )
{
	foreach( $args as $param )
	{
		if( isset( $pointer[ $param ] ) )

			$pointer = $pointer[ $param ];


		else

			return null;
	}


	return $pointer;
}



/**
 * Allows setting an option, checking if the object is sealed.
 * Currently only supports one level of depth.
 */
protected
function setOpt( $name, $value )
{
	if( method_exists( $this, 'checkSeal' ) )

		$this->checkSeal();


	$this->options[ $name ] = $value;
	$this->userset[ $name ] = $value;

	return $this;
}



/**
 * Get the options in use for this class.
 *
 * @return array The default options.
 *
 * @api
 *
 */
public function options()
{
	return $this->getOpts( $this->options, func_get_args() );
}



/**
 * Get the set of values that where the defaults for this class.
 *
 * @return array The default options.
 *
 * @api
 *
 */
public function defaults()
{
	return $this->getOpts( $this->defaults, func_get_args() );
}



/**
 * Get those values that have been userset or set by the client.
 *
 * @return array The user set options.
 *
 * @api
 *
 */
public function userset()
{
	return $this->getOpts( $this->userset, func_get_args() );
}


}
