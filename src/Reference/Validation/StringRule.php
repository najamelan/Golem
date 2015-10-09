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

	, RuntimeException
	, UnexpectedValueException
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
	function sanitize( $input, $encoding )
	{
		if( ! in_array( $encoding, mb_list_encodings() ) )

			$this->log->exception( new UnexpectedValueException( 'Encoding passed in not supported by the mbstring extension: ' . $encoding ) );


		$substitute = mb_substitute_character();

			mb_substitute_character( $this->golem->options( 'String', 'substitute' ) );
			$sane = mb_convert_encoding( $input, $encoding, $encoding );

		mb_substitute_character( $substitute );


		if( ! $this->validate( $sane, $encoding ) )

			$this->log->exception( new RuntimeException( 'Could not sanitize string: ' . var_export( $input, true ) ) );


		return $sane;
	}



	public
	function validate( $input, $encoding )
	{
		if( ! in_array( $encoding, mb_list_encodings() ) )

			$this->log->exception( new UnexpectedValueException( 'Encoding passed in not supported by the mbstring extension: ' . $encoding ) );


		return mb_check_encoding( $input, $encoding );
	}
}
