<?php

namespace Golem\Traits;

use

	\Exception

;


/**
 * Implemetation of the ArrayAccess interface. This assumes that classes using this trait
 * also implement SplSubject, since changing values will trigger $this->notify.
 *
 */
trait ArrayAccess
{
	/**
	 * @ignore
	 *
	 */
	public
	function offsetExists( $i )
	{
		return isset( $this->options[ $i ] );
	}



	/**
	 * @ignore
	 *
	 */
	public
	function offsetGet( $i )
	{
		return isset( $this->options[ $i ] ) ? $this->options[ $i ] : null;
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

			$this->options[]     = $value;

		else

			$this->options[ $i ] = $value;


		$this->notify( $i . ' changed' );
	}



	/**
	 * @ignore
	 *
	 */
	public function offsetUnset( $i )
	{
		if( $this->sealed() )

			throw new Exception( "Cannot change sealed options object." );


		unset( $this->options[ $i ] );


		$this->notify( $i . ' changed' );
	}
}
