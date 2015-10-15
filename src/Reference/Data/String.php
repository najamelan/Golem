<?php

/**
 * File class of library Golem.
 *
 */

namespace Golem\Reference\Data;

use

	  Golem\Golem

	, Golem\Reference\Traits\Seal
	, Golem\Reference\Traits\HasOptions
	, Golem\Reference\Traits\HasLog

	, Iterator
	, ArrayAccess
	, Countable

;


/**
 * Basic string funcionality.
 *
 * Object oriented strings. Provide encoding safety.
 *
 */
class      String
implements Iterator, ArrayAccess, Countable
{
	use Seal, HasOptions, HasLog;

	private $golem;
	private $raw  ;

	// For the iterator
	//
	private $position = 0;



	public
	function __construct( Golem $golem, $content = '', $options = [] )
	{
		$this->golem = $golem;

		$this->setupOptions( $this->golem->options( 'String' ), $options );
		$this->setupLog();

		$this->ensureValidEncoding( $this->options( 'encoding' ) );
		$this->raw                ( $content                     );
	}



	public
	static
	function fromUniCodePoint( $golem, $codePoint, $encoding = null )
	{
		if( $encoding === null )

			$encoding = $golem->options( 'String', 'encoding' );


		$raw = mb_convert_encoding( pack( "N", $codePoint ), $encoding, 'UTF-32' );

		return new self( $golem, $raw, [ 'encoding' => $encoding ] );
	}



	public
	function copy()
	{
		return clone $this;
	}



	public
	function raw( $value = null )
	{
		if( $value === null )

			return $this->raw;


		if( ! is_string( $value ) )

			$this->log->warning
			(
				  'Passing non string value to String::raw(). Trying implicit cast to string. Got: '
				. var_export( $value, /* return = */ true  )
			)
		;


		$this->raw = $this->sanitizeEncoding( $value );

		return $this;
	}



	public
	function encoding()
	{
		return $this->options( 'encoding' );
	}



	protected
	function sanitizeEncoding( $input )
	{
		$enc = $this->options( 'encoding' );


		$substitute = mb_substitute_character();

			mb_substitute_character( $this->golem->options( 'String', 'substitute' ) );
			$sane = mb_convert_encoding( $input, $enc, $enc );

		mb_substitute_character( $substitute );


		if( mb_check_encoding( $sane, $enc ) === false )

			$this->log->validationException( 'Encoding doesn\'t validate' );


		return $sane;
	}



	public
	static
	function encodingSupported( $encoding )
	{
		return in_array( $encoding, mb_list_encodings(), /* strict = */ true );
	}



	public
	function ensureValidEncoding( $encoding )
	{
		if( ! self::encodingSupported( $encoding ) )

			$this->log->unexpectedValueException
			(
				"Encoding passed in not supported by the mbstring extension: [$encoding]"
			)
		;
	}



	public
	function convert( $toEncoding )
	{
		$this->ensureValidEncoding( $toEncoding );

		$oldEncoding = $this->encoding();
		$this->options[ 'encoding' ] = $toEncoding;

		$this->raw( mb_convert_encoding( $this->raw(), $toEncoding, $oldEncoding ) );

		return $this;
	}



	public
	function __toString()
	{
		return $this->raw();
	}


	public
	function hex( $prettify = false )
	{
		$hex = bin2hex( $this->raw() );


		if( $prettify )

			$hex =  join( ' ', str_split( $hex, 2 ) );


		return $hex;
	}



	public
	function length()
	{
		return mb_strlen( $this->raw, $this->encoding() );
	}



	public
	function count()
	{
		return $this->length();
	}


	/**
	 * Splits a string into an array of characters. See also str_split()
	 *
	 * @param string $string The string to split into characters.
	 *
	 * @return array $result An array containing one element per character in the string.
	 *
	 */
	public
	function split( $chunksize = 1 )
	{
		$stop = $this->length();


		for( $i = 0, $result = []; $i < $stop; $i += $chunksize )

			// $result[] = new self( mb_substr( $this->raw, $i, $chunksize, $this->encoding() ), $this->options() );
			$result[] = mb_substr( $this->raw, $i, $chunksize, $this->encoding() );


		return $result;
	}



	/**
	 * Pops a number of characters off the end of a string.
	 *
	 * @param int $amount The number of characters to pop.
	 *
	 * @return String $result A new String consisting of just the popped characters of the original string.
	 *
	 */
	public
	function pop( $amount = 1 )
	{
		$amount = min( $amount, $this->length() );

		$result = mb_substr( $this->raw, $this->length() - $amount, $amount, $this->encoding() );

		$this->raw( mb_substr( $this->raw, 0, $this->length() - $amount, $this->encoding() ) );

		return new String( $this->golem, $result, $this->options() );
	}



	/**
	 * Shifts a number of characters of the beginning of a string.
	 *
	 * @param int $amount The number of characters to shift.
	 *
	 * @return String $result A new String consisting of just the shifted characters of the original string.
	 *
	 */
	public
	function shift( $amount = 1 )
	{
		$amount = min( $amount, $this->length() );

		$result = mb_substr( $this->raw, 0, $amount, $this->encoding() );

		$this->raw( mb_substr( $this->raw, $amount, $this->length() - $amount, $this->encoding() ) );

		return new String( $this->golem, $result, $this->options() );
	}



	/**
	 * Pushes a character onto the end of a string.
	 *
	 * @param String $new The string to append to the string.
	 *
	 * @return String $this
	 *
	 */
	public
	function append( String $new )
	{
		$this->raw( $this->raw() . $new->copy()->convert( $this->encoding() )->raw() );

		return $this;
	}



	/**
	 * Pushes a character onto the end of a string.
	 *
	 * @param String $new The string to append to the string.
	 *
	 * @return String $this
	 *
	 */
	public
	function prepend( String $new )
	{
		$this->raw( $new->copy()->convert( $this->encoding() )->raw() . $this->raw() );

		return $this;
	}



	function uniCodePoint()
	{
		$utf32  = $this->copy()->convert( 'UTF-32' );
		$result = [];


		foreach( $utf32 as $char )

			$result[] = hexdec( bin2hex( $char->raw() ) );


		return $result;
	}



	public
	function equals( String $input )
	{
		if( $input->encoding() !== $this->encoding() )

			$input = $input->copy()->convert( $this->encoding() );


		return $this->raw() === $input->raw();
	}



	/**
	 * Iterator implementation
	 *
	 */
	public
	function current()
	{
		return $this->offsetGet( $this->position );
	}



	public
	function key()
	{
		return $this->position;
	}



	public
	function next()
	{
		++$this->position;
	}



	public
	function rewind()
	{
		$this->position = 0;
	}



	public
	function valid()
	{
		return $this->position < $this->length();
	}



	/*
	 * ArrayAccess Implementation
	 */

	/**
	 * @ignore
	 *
	 */
	public
	function offsetExists( $i )
	{
		if( ! is_int( $i ) )

			$this->log->invalidArgumentException( 'Index should be of type int' );


		return $i >= 0  &&  $i < $this->length();
	}



	/**
	 * @ignore
	 *
	 */
	public
	function offsetGet( $i )
	{
		// if( ! $this->offsetExists( $i ) )

		// 	return '';


		$raw = mb_substr( $this->raw(), $i, 1, $this->encoding() );

		return new self( $this->golem, $raw, [ 'encoding' => $this->encoding() ] );
	}



	/**
	 * @ignore
	 *
	 */
	public
	function offsetSet( $i , $value )
	{
		// TODO: Input validation (of $value)

		if( $i < 0 || $i > $this->length() )

			$this->log->OutOfRangeException( "Can only set characters up to the length of the string" );


		if( is_null( $i ) || $i === $this->length() )

			$this->push( $value );


		else

			$this->splice[ $i ] = $value;
	}



	/**
	 * @ignore
	 *
	 */
	public function offsetUnset( $i )
	{
		if( $this->sealed() )

			$this->log->logicException( "Cannot change sealed options object." );


		unset( $this->parsed[ $i ] );
	}



	/**
	 * @ignore
	 *
	 */
	public
	function splice( $offset, $amount, String $replacement = null )
	{
		// TODO: Input validation

		$first  = $this->substr( 0                , $offset  );
		$splice = $this->substr( $offset          , $amount  );
		$last   = $this->substr( $offset + $amount           );


		if( $replacement )

			$first->append( $replacement );


		return $first->append( $last );
	}



	/**
	 * @ignore
	 *
	 */
	public
	function substr( $offset, $length = null )
	{
		// TODO: Input validation

		return

			$this

				-> copy()

				-> raw
				   (
				      mb_substr( $this->raw(), $offset, $length, $this->encoding() )
				   )
		;
	}
}
