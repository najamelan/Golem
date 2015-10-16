<?php
/**
 *
 */

namespace Golem;

use

	  Golem\Golem

	, Golem\Validation\StringRule

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions

	, Golem\Util

;


class Sanitizer
// implements Encoder
{
	use Seal, HasOptions;

	private $rules = [];
	private $golem;


	/**
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return does not return a value.
	 */
	public function __construct( Golem $golem, array $options = [] )
	{
		$this->golem = $golem;

		$this->setupOptions( (array) $golem->options( 'Validation' ), $options );
	}



	public
	function string( $input, $encoding )
	{
		if( ! isset( $this->rules[ 'StringRule' ] ) )

			$this->rules[ 'StringRule' ] = new StringRule( $this->golem, $this->options( 'StringRule' ) );


		return $this->rules[ 'StringRule' ]->sanitize( $input, $encoding );
	}
}
