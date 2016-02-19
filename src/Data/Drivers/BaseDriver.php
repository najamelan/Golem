<?php

/**
 * BaseDriver class of library Golem.
 *
 */

namespace Golem\Data\Drivers;

use

	  Golem\Golem

	, Golem\iFace\Data\FileDriver as iFileDriver

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions
	, Golem\Traits\HasLog

	, Golem\Util

;


/**
 * Basic file driver.
 *
 * File drivers add functionality specific to file types such as reading and writing meta data.
 *
 */
abstract
class      BaseDriver
implements iFileDriver
{

use HasOptions, Seal, HasLog;

protected $golem ;


public
function __construct( Golem $golem, array $options = [] )
{
	$this->golem = $golem;

	$this->setupOptions( $golem->options( 'Driver', 'BaseDriver' ), $options );
	$this->setupLog();
}


}
