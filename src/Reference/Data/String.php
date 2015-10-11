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
;


/**
 * Basic string funcionality.
 *
 * Object oriented strings. Provide encoding safety.
 *
 */
class      String
implements Iterator
{
	use Seal, HasOptions, HasLog;

	private $golem   ;
	private $content ;
	private $sanitize;

	// For the iterator
	//
	private $position = 0;



	public
	function __construct( Golem $golem, $content = '', $options = [] )
	{
		$this->golem = $golem;

		$this->setupOptions( $this->golem->options( 'String' ), $options );
		$this->setupLog();

		$this->sanitize = $this->golem->sanitizer();
		$this->content( $content );
	}



	public
	function klone()
	{
		return clone $this;
	}



	public
	function content( $value = null )
	{
		if( $value === null )

			return $this->content;


		$this->content = $this->sanitize->string( $value, $this->encoding() );

		return $this;
	}



	public
	function convert( $toEncoding )
	{
		$oldEncoding = $this->encoding();
		$this->options[ 'encoding' ] = $toEncoding;

		$this->content( mb_convert_encoding( $this->content(), $toEncoding, $oldEncoding ) );

		return $this;
	}



	public
	function __toString()
	{
		return $this->content();
	}


	public
	function hex()
	{
		$hex = bin2hex( $this->content() );
		$arr = str_split( $hex, 2 );
		return join( ' ', $arr );
	}



	public
	function length()
	{
		return mb_strlen( $this->content, $this->encoding() );
	}



	public
	function encoding()
	{
		return $this->options( 'encoding' );
	}



	/**
	 * Splits a string into an array of characters. Supports multibyte strings. See also str_split()
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

			$result[] = mb_substr( $this->content, $i, $chunksize, $this->encoding() );


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

		$result = mb_substr( $this->content, $this->length() - $amount, $amount, $this->encoding() );

		$this->content( mb_substr( $this->content, 0, $this->length() - $amount, $this->encoding() ) );

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
	function push( String $new )
	{
		$this->content( $this->content() . $new->convert( $this->encoding() )->content() );

		return $this;
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

		$result = mb_substr( $this->content, 0, $amount, $this->encoding() );

		$this->content( mb_substr( $this->content, $amount, $this->length() - $amount, $this->encoding() ) );

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
	function unshift( String $new )
	{
		$this->content( $new->convert( $this->encoding() )->content() . $this->content() );

		return $this;
	}



	function uniCodePoint()
	{
		$utf32  = $this->klone()->convert( 'UTF-32' );
		$result = [];


		foreach( $utf32 as $char )

			$result[] = hexdec( bin2hex( $char->content() ) );


		return $result;
	}



	/**
	 * Iterator implementation
	 *
	 */
	public
	function current()
	{
		$raw = mb_substr( $this->content(), $this->position, 1, $this->encoding() );

		return new self( $this->golem, $raw, [ 'encoding' => $this->encoding() ] );
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
}
