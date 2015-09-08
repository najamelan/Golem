<?php

namespace Golem\Traits;

use

	\Exception

;


/**
 * Common functionality for sealing objects and their options.
 *
 * The configuration of a sealed object can no longer be changed.
 * Eg. A sealed logger cannot be disabled, have it's loglevel changed,
 * be redirected to another logfile, ...
 *
 */
trait Seal
{
	/**
	 * Seal the current options object so it cannot be changed anymore.
	 *
	 * Since this is a security library, we want clients to be sure that certain settings don't change
	 * anymore. It's best practice to define your security configuration in one place and then seal
	 * objects so they won't change anymore by php code included later.
	 *
	 * @return mixed $this.
	 *
	 * @api
	 *
	 */
	public
	function seal()
	{
		if( isset( $this->options ) )

			$this->options->seal();


		$this->sealed = true;

		return $this;
	}



	/**
	 * Tells you whether the current object or it's options are sealed.
	 *
	 * @return bool Whether the object is sealed.
	 *
	 * @api
	 *
	 */
	public
	function sealed()
	{
		if( isset( $this->options ) )

			return $this->options->sealed();


		return $this->sealed;
	}
}
