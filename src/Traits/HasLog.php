<?php

namespace Golem\Traits;

use

	  Golem\Util

;


/**
 * All objects in Golem have options, this is the common code shared by all of them.
 *
 */
trait HasLog
{


/**
 * @var The logger object this class uses.
 *
 */
protected $log;



/**
 * Helper for constructors of classes that have a logger.
 *
 * @param array  $options The options passed in to the constructor of the logger.
 *
 * @return $this.
 *
 */
private
function setupLog( $name = null, array $options = [], $golem = null )
{
	if( $name === null )

		$name = __CLASS__ ;


	if( ! $golem )

		$golem = $this->g;


	$this->log = $golem->logger( $name, $options );

	return $this;
}
}
