<?php

namespace Golem;


/**
 * General Utility Functions.
 *
 */
class Util
{

	/**
	 * Get the type of a php variable (or it's class in case it's an object).
	 *
	 * @return string The class if this is an object, otherwise the type.
	 *
	 * @api
	 *
	 */
	public
	static
	function getType( $var )
	{
		if( is_object( $var ) )

			return get_class( $var );


		return gettype( $var );
	}
}
