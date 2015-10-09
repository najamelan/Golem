<?php

namespace Golem\Reference\Traits;

use

	  Golem\Reference\Util

	, \InvalidArgumentException

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
	 * with userset options.
	 *
	 * @param \Golem $golem The library object which will provide the defaults
	 * @param array  $options The options passed in to the constructor.
	 *
	 * @return $this.
	 *
	 */
	private
	function setupOptions( array $defaults, array $options )
	{
		$this->defaults = $defaults;
		$this->userset  = $options;

		$this->options = Util::joinAssociativeArray( $this->defaults, $this->userset );

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
	public
	function options()
	{
		$pointer = $this->options;


		foreach( func_get_args() as $param )
		{
			if( isset( $pointer[ $param ] ) )

				$pointer = $pointer[ $param ];


			else
			{
				$e = new InvalidArgumentException
				(
						'Called with invalid keys: ' . print_r( func_get_args(), true )
					.  ' from: ' . basename( debug_backtrace()[ 0 ][ 'file' ] ) . ":" . debug_backtrace()[ 0 ][ 'line' ]
				);


				// Golem itself uses this function, so $this->golem doesn't necessarily exist
				// TODO: check that __CLASS__ doesn't return the name of the trait
				//
				if( property_exists( $this, 'golem' ) )

					$this->golem->logger( __CLASS__ )->exception( $e );

				else

					throw $e;
			}
		}


		return $pointer;
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
		return $this->defaults;
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
		return $this->userset;
	}
}
