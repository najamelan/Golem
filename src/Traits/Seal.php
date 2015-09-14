<?php

namespace Golem\Traits;

use

	  Golem\iFace\Data\Options as iOptions

	, \Exception

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
	 * Seal the current object so it's options cannot be changed anymore.
	 *
	 * Since this is a security library, we want clients to be sure that certain settings don't change
	 * anymore. It's best practice to define your security configuration in one place and then seal
	 * objects so they won't change anymore by php code included later.
	 *
	 * Note: Currently (php 5.6) there is a reflection module in PHP which allows code to write to
	 *       private properties on objects from the outside. The module can only be turned off by
	 *       recompiling PHP. If you don't use a specially compiled version of PHP, this will not
	 *       protect agains malicious attacks.
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
		if( $this instanceof iOptions )

			$this->sealed = true;


		else

			$this->options->seal();


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
		if( $this instanceof iOptions )

			return $this->sealed;


		else

			return $this->options->sealed();
	}
}
