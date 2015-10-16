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
	 * @param  mixed  A variable for which to find the type.
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


	/**
	 * Joins two or more associative arrays together recursively; key/value pairs of the first
	 * array are replaced with key/value pairs from the subsequent arrays.  Any
	 * key/value pair not present in the first array is added to the final array
	 * Note: nested arrays with integer keys will not be joined, but overwritten
	 *
	 * @param array $defaults   The base array.
	 * @param array ...$options Any arrays to merge into the first.
	 *
	 * @internal
	 *
	 */
	public
	static
	function joinAssociativeArray( array $defaults, array ...$options )
	{

		$arrays   = func_get_args();            // Get array arguments
		$original = array_shift( $arrays );     // Define the original array


		// Loop through arrays
		//
		foreach( $arrays as $array )

			foreach( $array as $key => $value )  // Loop through array key/value pairs


				// Value is an array with numeric keys
				// Traverse the array; replace or add result to original array
				//
				if
				(
					     is_array( $value      )
					&& ! isset   ( $value[ 0 ] )

					&&   isset   ( $original[ $key ] )
					&&   is_array( $original[ $key ] )
					&& ! isset   ( $original[ 0 ]    )
				)

					$original[ $key ] = self::joinAssociativeArray( $original[ $key ], $array[ $key ] );


				// Value is not an array
				// Replace or add current value to original array
				//
				else

					$original[ $key ] = $value;


		return $original;
	}



	/**
	 * rm -rf in php
	 *
	 * @param string $dir The directory to delete.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 *
	 * @internal
	 *
	 */
	public
	static function delTree( $dir )
	{
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );


		foreach( $files as $file )

			( is_dir( "$dir/$file" ) && !is_link( $dir ) ) ? self::delTree( "$dir/$file" ) : unlink( "$dir/$file" );


		return rmdir( $dir );
	}
}
