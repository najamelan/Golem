<?php
/**
 *
 */

namespace Golem\Reference\Codecs;

use

	  Golem\Golem

	, Golem\Reference\Util
	, Golem\Reference\Encoder
	, Golem\Reference\Unicode

	, Golem\Reference\Data\String

;


require_once __DIR__ . '/HTML5Entities.php';


/**
 * Reference implementation of the HTML codec.
 *
 */
class HTML5 extends Codec
{


// HTML5 specification section 2.5.1
//
const HTML5_SPACE_CHARS =
[
	  0x20  // space
	, 0x09  // tab
	, 0x0A  // line feed      \n
	, 0x0C  // form feed
	, 0x0D  // cariage return \r
];


const LONGEST_ENTITY  = 35;




/**
 * Public Constructor.
 *
 * There is no default for options( 'context' ). You have to specify it on construction.
 * Otherwise an exception will be thrown.
 *
 */
public function __construct( Golem $golem, $context, array $options = [] )
{
	parent::__construct( $golem, $golem->options( 'Codec', 'HTML5' ), $options );


	// Parameter Validation
	//
	$this->options[ 'context' ] = $context	= $this->golem->stringRule()

		->encoding( $this->cfgEnc                   )
		->in      ( 'text'  , 'attribute'           )
		->validate( $context, 'parameter: $context' )
	;


	$immune =   $context->raw() === 'text'  ?

		   $this->options( 'immuneText'      )
		:  $this->options( 'immuneAttribute' )
	;


	$this->options[ 'immune' ] =

		array_merge( $this->golem->string( $immune, $this->cfgEnc )->split(), Codec::$ALPHANUMERICS )
	;
}



/**
 * {@inheritdoc}
 *
 * TODO: Attributes shouldn't contain ambigious ampersands...
 *
 * Restrictions (HTML5 specs section: 3.2.4.1.5 Phrasing content, 8.1.2.3 Attributes, 8.1.4 Character references):
 *
 * Right now, we go way beyond the standard, encoding everything but alphanumericals and
 * the immune characters (as defined in the OWASP XSS Cheat sheet). The downside is extra
 * size and unreadable html code for people not using a latin alphabet.
 *
 * A version of this could be written that uses a blacklist approach based on the html specification.
 *
 */
public
function encodeCharacter( String $c )
{
	// Parameter Validation
	//
	$c = $this->golem->stringRule()

		->length  ( 1                  )
		->validate( $c, 'parameter $c' )
	;


	// Get a version of the character in the correct encoding to compare to hardcoded values.
	//
	$charCfgEnc = $c->copy()->convert( $this->cfgEnc )->raw();


	// Check for immune characters.
	//
	if( in_array( $charCfgEnc, $this->options[ 'immune' ], /* strict = */ true ) )

		return $c;


	$codePoint = $c->uniCodePoint()[ 0 ];


	// Check for illegal characters
	//
	if( ! $this->allowedInEntity( $codePoint ) )

		return

			$this->golem->string( $this->options( 'substitute' ), $this->cfgEnc )
		;



	// Check if there's a defined entity
	//
	$named = array_search( $codePoint, HTML5_ENTITY_MAP, /* strict = */ true );

	if( $named !== false )

		return $this->golem->string( '&' . $named , $this->cfgEnc );


	// Else return a hex entity of the unicode code point
	//
	return

		$this->golem->string( '&#x' . dechex( $codePoint ) . ';' , $this->cfgEnc )
	;
}



/**
 * {@inheritdoc}
 *
 * Restrictions (HTML5 specs section: 3.2.4.1.5 Phrasing content, 8.1.2.3 Attributes, 8.1.4 Character references):
 */
public
function allowedInEntity( $codePoint )
{

	// Parameter Validation
	//
	// $codePoint = $this->golem->numberRule()

	// 	->type    ( 'int'                               )
	// 	->validate( $codePoint, 'parameter: $codePoint' )
	// ;


	// Check for illegal characters
	//
	if
	(
		   ! Unicode::isCodePoint  ( $codePoint )

		||   Unicode::isControlChar( $codePoint )
		     && ! in_array( $codePoint, self::HTML5_SPACE_CHARS, /* strict = */ true )

		||   Unicode::isNonChar    ( $codePoint )
		||   $codePoint === 0x0D
	)

		return false;


	return true;

}



/**
 * {@inheritdoc}
 */
public
function decodeCharacter( String $input )
{
	$decoded = $this->decodeNumericEntity( $input, 'hex' );

	if( $decoded !== null )

		return $decoded;


	$decoded = $this->decodeNumericEntity( $input, 'dec' );

	if( $decoded !== null )

		return $decoded;


	$decoded = $this->decodeNamedEntity( $input );

	if( $decoded !== null )

		return $decoded;


	// else it's not a valid entity start, eat a character
	//
	return $input->shift();
}



/**
 * Parse a decimal number, such as those from JavaScript's
 * String.fromCharCode(value).
 *
 * @param string $input The input to test for being a numeric entity
 *
 * @return array Returns an array containing two objects: 'decodedCharacter'
 *               => NULL if input is NULL, the character of input after
 *               decoding 'encodedString' => the string that was decoded or
 *               found to be malformed
 */
private
function decodeNumericEntity( String $input, $type = 'dec' )
{
	// Parameter Validation
	//
	$c = $this->golem->stringRule()

		->encoding( $this->cfgEnc             )
		->in      ( 'hex', 'dec'              )
		->validate( $type, 'parameter: $type' )
	;


	switch( $type )
	{
		case 'hex': $startLength  =  3                     ;
		            $startString  =  '&#x'                 ;
		            $startString2 =  '&#X'                 ;
		            $ditgits      =  &Codec::$HEXDIGITS    ;
		            $convFunction =  'hexdec'              ;
		            break;


		case 'dec': $startLength  =  2                     ;
		            $startString  =  $startString2 =  '&#' ;
		            $ditgits      =  &Codec::$DIGITS       ;
		            $convFunction =  'intval'              ;
	}


	// get a config encoded string to compare to hard coded values
	// Since the number should never be more than 32 bytes, we need at most 12 chars to work with
	//
	$inputCfgEnc = $input      ->substr( 0, 12        )->convert( $this->cfgEnc );
	$start       = $inputCfgEnc->shift ( $startLength )->raw();


	if( $start !== $startString  &&  $start !== $startString2 )

		return null;


	$number = $this->golem->string( '', $this->cfgEnc );


	while( $inputCfgEnc->length() )
	{
		if( in_array( $inputCfgEnc[ 0 ]->raw(), $ditgits, /* strict = */ true ) )

			$number->append( $inputCfgEnc->shift() );

		else

			break;
	}


	$codePoint = $convFunction( $number->raw() );
	$semicolon = $inputCfgEnc[ 0 ]->raw() === ';'  ?  1 : 0;

	// if the next character is not a semicolon and requireEntitySemicolon
	//
	if
	(
		   $this->options( 'requireEntitySemicolon' )  &&  ! $semicolon

		|| $number->length() === 0

		|| ! $this->allowedInEntity( $codePoint )
	)

		return null;



	$input->shift( $startLength + $number->length() + $semicolon );

	return String::fromUniCodePoint( $this->golem, $codePoint, $input->encoding() );
}







/**
 * Returns the decoded version of the character starting at index, or
 * NULL if no decoding is possible.
 *
 * Formats all are legal both with and without semi-colon, upper/lower case:
 * &aa;
 * &aaa;
 * &aaaa;
 * &aaaaa;
 * &aaaaaa;
 * &aaaaaaa;
 * &aaaaaaaa;
 *
 * note: the case of the first letter is important and should be preserved
 * so as to differentiate between, say, &Oacute; and &oacute; .
 *
 * @param string input A UTF-32 string containing a named entity like &quot; and
 *                     may contain trailing characters like &quot;quotlala
 *                     or &quotquotlala .
 *
 * @return array Returns an array containing two objects: 'decodedCharacter' =>
 *               the decoded version of the character starting at index, or NULL
 *               if no decoding is possible. 'encodedString' => the string that
 *               was decoded or found to be malformed
 */
private function decodeNamedEntity( String $input )
{

	// get a config encoded string to compare to hard coded values
	//
	$inputCfgEnc = $input->substr( 0, self::LONGEST_ENTITY + 2 )->convert( $this->cfgEnc );


	if( $inputCfgEnc->shift()->raw() !== '&' )

		return null;


	$name = $this->golem->string( '', $this->cfgEnc );


	while( $inputCfgEnc->length() )
	{
		if( in_array( $inputCfgEnc[ 0 ]->raw(), Codec::$ALPHANUMERICS, /* strict = */ true ) )

			$name->append( $inputCfgEnc->shift() );

		else

			break;


	}


	// Add semicolon to the name
	//
	if( $inputCfgEnc[ 0 ]->raw() === ';' )

		$name->append( $inputCfgEnc[ 0 ] );


	// if the next character is not a semicolon and requireEntitySemicolon
	//
	if
	(
		   $name->length() === 0

		|| ! array_key_exists( $name->raw(), HTML5_ENTITY_MAP )
	)

		return null;



	$input->shift( 1 + $name->length() );

	$result = String::fromUniCodePoint( $this->golem, HTML5_ENTITY_MAP[ $name->raw() ], $this->cfgEnc );

	return $result->convert( $input->encoding() );
}


}
