<?php

/**
 * This is the Golem library Options object.
 *
 * The data for the Options object should ideally come from
 * a place that is outside the document root, is readable but not
 * writable by the web server process.
 *
 */

namespace Golem\Reference\Data;

use

	Golem\Golem

;



class      GolemOptions
extends    Options
implements \Golem\iFace\Data\Options
{

	public
	function __construct( $options = [], $defaults = [] )
	{
		if( $options instanceof Golem )

			$options = $options->options();

		parent::__construct( $options, $defaults );
	}
}
