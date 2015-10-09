<?php
/**
 *
 */



namespace Golem\Reference\Validation;

use

	  Golem\Golem

	, Golem\iFace\ValidationRule

	, Golem\Reference\Traits\Seal
	, Golem\Reference\Traits\HasOptions
	, Golem\Reference\Traits\HasLog

	, Golem\Reference\Util
;

/**
 * The basic string rule. All string validation classes should inherit this. It will just ensure that
 * the string has valid encoding.
 *
 */
class      StringRule
implements ValidationRule
{
	use HasOptions, Seal, HasLog;

	private $golem;


	public
	function __construct( $golem, array $options = [] )
	{
		$this->golem = $golem;

		$this->setupOptions( $golem->options( 'Validation', 'StringRule' ), $options );
		$this->setupLog();
	}



	public
	function sanitize( $input )
	{
		$substitute = mb_substitute_character();

			mb_substitute_character( $this->golem->options( 'String', 'substitute' ) );
			$sane = mb_convert_encoding( $input, $this->options( 'encoding' ), $this->options( 'encoding' ) );

		mb_substitute_character( $substitute );

		if( ! $this->validate( $sane ) )

			$this->log->exception( 'Could not sanitize string: ' . var_export( $input, true ) );


		return $sane;
	}



	public
	function validate( $input )
	{
		return mb_check_encoding( $input, $this->options( 'encoding' ) );
	}
}
