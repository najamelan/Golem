<?php

namespace Golem\Traits;

use

	\Exception

;

trait ArrayAccess
{
	/**
	 * @ignore
	 *
	 */
	public
	function offsetExists( $i )
	{
		return isset( $this->parsed[ $i ] );
	}



	/**
	 * @ignore
	 *
	 */
	public
	function offsetGet( $i )
	{
		return isset( $this->parsed[ $i ] ) ? $this->parsed[ $i ] : null;
	}



	/**
	 * @ignore
	 *
	 */
	public
	function offsetSet( $i , $value )
	{
		if( $this->sealed() )

			throw new Exception( "Cannot change sealed options object." );


		if( is_null( $i ) )

			$this->parsed[]     = $value;

		else

			$this->parsed[ $i ] = $value;
	}



	/**
	 * @ignore
	 *
	 */
	public function offsetUnset( $i )
	{
		if( $this->sealed() )

			throw new Exception( "Cannot change sealed options object." );


		unset( $this->parsed[ $i ] );
	}
}
