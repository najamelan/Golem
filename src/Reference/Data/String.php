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
;


/**
 * Basic string funcionality.
 *
 * Object oriented strings. Provide encoding safety.
 *
 */
class String
{
	use Seal, HasOptions, HasLog;

	private $golem   ;
	private $content ;
	private $sanitize;



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
	function content( $value = null )
	{
		if( $value === null )

			return $this->content;


		return $this->content = $this->sanitize->string( $value );
	}



	public
	function convert( $toEncoding )
	{
		mb_convert_encoding( $this->content(), $toEncoding, $this->options( 'encoding' ) );

		$this->options[ 'encoding' ] = $toEncoding;

		return $this;
	}



	public
	function __toString()
	{
		return $this->content();
	}



	public
	function length()
	{
		return mb_strlen( $this->content, $this->options( 'encoding' ) );
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

			$result[] = mb_substr( $this->content, $i, $chunksize, $this->options( 'encoding' ) );


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

		$result = mb_substr( $this->content, $this->length() - $amount, $amount, $this->options( 'encoding' ) );

		$this->content( mb_substr( $this->content, 0, $this->length() - $amount, $this->options( 'encoding' ) ) );

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
		$this->content( $this->content() . $new->content() );

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

		$result = mb_substr( $this->content, 0, $amount, $this->options( 'encoding' ) );

		$this->content( mb_substr( $this->content, $amount, $this->length() - $amount, $this->options( 'encoding' ) ) );

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
		$this->content( $new->content() . $this->content() );

		return $this;
	}
}
