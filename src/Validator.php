<?php
/**
 *
 */

namespace Golem;

use

	  Golem\Golem

	, Golem\Validation\StringRule
	, Golem\Validation\NumberRule
	, Golem\Validation\BooleanRule

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions

	, Golem\Util

;


class Validator
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

		$this->setupOptions( (array) $golem->options( 'Validation', 'Validator' ), $options );
	}



	public function string ( $options = [] ){ return new StringRule ( $this->golem, $options ); }
	public function number ( $options = [] ){ return new NumberRule ( $this->golem, $options ); }
	public function boolean( $options = [] ){ return new BooleanRule( $this->golem, $options ); }
}
