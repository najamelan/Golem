<?php

namespace Golem\Traits;

use

	  Golem\iFace\Data\Options as iOptions

	, \Exception

;


/**
 * All objects in Golem have options, this is the common code shared by all of them.
 *
 *
 *
 */
trait HasOptions
{
	protected $options;


	/**
	 * Provides a read only copy of the options object for this class.
	 *
	 * @return iFace\Data\Options The currently loaded configuration for this object.
	 *
	 */
	public
	function options()
	{
		$give = clone $this->options;
		$give->seal();

		return $give;
	}


	/**
	 * Override certain options.
	 *
	 * @return array|iFace\Data\Options The new options.
	 *
	 * @throws \Exception When trying to override on a sealed object.
	 *
	 * @api
	 *
	 */
	public
	function override( $newOptions )
	{
		$this->options->override( $newOptions );

		return $this;
	}
}
