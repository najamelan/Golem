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

	, UnexpectedValueException
	, LengthException

;




/**
 * Reference implementation of the HTML codec.
 *
 */
class HTML extends Codec
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


	private static $charToEntityMap = []     ;
	private static $entityToCharacterMap = []     ;
	private static $longestEntity        = 0      ;
	private static $mapIsInitialized     = false  ;




	/**
	 * Public Constructor.
	 *
	 * There is no default for options( 'context' ). You have to specify it on construction.
	 * Otherwise an exception will be thrown.
	 *
	 */
	public function __construct( Golem $golem, array $options = [] )
	{
		parent::__construct( $golem, $golem->options( 'Codec', 'HTML' ), $options );


		if( ! self::$mapIsInitialized )

			self::initialize();


		if( $this->options( 'context' ) === 'text' )

			$immune = $this->options( 'immuneText' );


		elseif( $this->options( 'context' ) === 'attribute' )

			$immune = $this->options( 'immuneAttribute' );


		else

			$this->log->exception( new UnexpectedValueException( 'wrong context option: ' . $this->options( 'context' ) ) );


		$this->options[ 'immune' ] = array_merge( $this->golem->string( $immune, 'UTF-8' )->split(), Codec::$ALPHANUMERICS );
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
	 * We encode, not strip special classes like control characters and unicode noncharacters since
	 * in principle the standard only specifies that they shouldn't occur literally, but in entity
	 * form they are legal.
	 *
	 */
	public function encodeCharacter( String $c )
	{
		// Make sure we only have one character
		//
		if( $c->length() !== 1 )

			$this->log->exception
			(
				new LengthException
				(
					'Only one character should be passed to this function, got: ' . var_export( $c->content(), true )
				)
			)
		;


		// Get a version of the character in the correct encoding to compare to hardcoded values.
		//
		$charCfgEnc = $c->klone()->convert( $this->cfgEnc )->content();


		// Check for immune characters.
		//
		if( in_array( $charCfgEnc, $this->options[ 'immune' ], /* strict = */ true ) )

			return $c;


		$codePoint = $c->uniCodePoint()[ 0 ];


		// Check for illegal characters
		//
		if
		(
			   Unicode::isNonChar    ( $codePoint )
			|| Unicode::isControlChar( $codePoint )  && ! in_array( $codePoint, self::HTML5_SPACE_CHARS )
			|| $codePoint === 0x0D
		)
		{
			return

				$this->golem->string( $this->options( 'substitute' ), $this->cfgEnc )
			;
		}


		// Check if there's a defined entity
		//
		if( isset( self::$charToEntityMap[ $charCfgEnc ] ) )

			return

				$this->golem->string( '&' . self::$charToEntityMap[ $charCfgEnc ] . ';' , $this->cfgEnc )
			;


		// Else return a hex entity of the unicode code point
		//
		return

			$this->golem->string( '&#x' . dechex( $codePoint ) . ';' , $this->cfgEnc )
		;
	}



	/**
	 * {@inheritdoc}
	 */
	public function decodeCharacter($input)
	{
		//TODO: add comments

		$decodeResult = null;
		if (mb_substr($input, 0, 1, Codec::$referenceEncoding ) == null) {
			// first character is null, so eat the 1st character off the string
			//and return null

			//todo: this isn't necessary, can simply return null...no need to eat
			//1st character off input string
			$input = mb_substr($input, 1, mb_strlen($input, Codec::$referenceEncoding ), Codec::$referenceEncoding );

			return array(
				'decodedCharacter' => null,
				'encodedString' => null
			);
		}

		// if this is not an encoded character, return null
		if (mb_substr($input, 0, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('&')) {
			// 1st character is not part of encoding pattern, so return null
			return array(
				'decodedCharacter' => null,
				'encodedString' => null
			);
		}
		// 1st character is part of encoding pattern...

		// test for numeric encodings
		if (mb_substr($input, 1, 1, Codec::$referenceEncoding ) == null) {
			// 2nd character is null, so return decodedCharacter=null and
			//encodedString=(1st character, malformed encoding)
			return array(
				'decodedCharacter' => null,
				'encodedString' => mb_substr($input, 0, 1, Codec::$referenceEncoding )
			); //could potentially speed this up simply using
			   //'encodedString'=>$this->normalizeEncoding('&')
		}

		if (mb_substr($input, 1, 1, Codec::$referenceEncoding ) == $this->normalizeEncoding('#')) {
			// 2nd character is hash, so handle numbers...

			// handle numbers
			$decodeResult     = $this->getNumericEntity($input);
			$decodedCharacter = $decodeResult['decodedCharacter'];
			if ($decodedCharacter != null) {
				return $decodeResult;
			}
		} else {
			// Get the ordinal value of the 2nd character.
			list(, $ordinalValue) = unpack("N", mb_substr($input, 1, 1, Codec::$referenceEncoding ));

			if ( in_array( chr( $ordinalValue ), Encoder::CHAR_ALPHANUMERICS, true ) ) {
				// 2nd character is an alphabetical char, so handle entities...

				// handle entities
				$decodeResult     = $this->getNamedEntity($input);
				$decodedCharacter = $decodeResult['decodedCharacter'];
				if ($decodedCharacter != null) {
					return $decodeResult;
				}
			} else {
				// 2nd character does not form a known entity, so re
				return array(
					'decodedCharacter' => null,
					'encodedString' => null
				);
			}
		}

		//perhaps, if decodedCharacter is not null then add it back to start of
		//input string and see if it is part of a greater encoding pattern (i.e.
		//double-encoding)

		//at this stage: decodedCharacter could only be null, encodedString could
		//only be anything between 1st character (i.e. '&') and all remaining
		//characters
		return $decodeResult;
	}

	/**
	 * getNumericEntry checks input to see if it is a numeric entity.
	 *
	 * @param string $input The input to test for being a numeric entity, may
	 *                      contain trailing characters like &
	 *
	 * @return array Returns an array containing two objects: 'decodedCharacter'
	 *               => NULL if input is NULL, the character of input after
	 *               decoding 'encodedString' => the string that was decoded or
	 *               found to be malformed
	 */
	private function getNumericEntity($input)
	{
		// decodeCharacter should've already established that the 1st 2 characters
		//are '&#', but check again incase this method is being called from
		//elsewhere
		if (mb_substr($input, 0, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('&')
			|| mb_substr($input, 1, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('#')
		) {
			// input did not satisfy initial pattern requirements for
			//getNumericEntity, so return null
			return array(
				'decodedCharacter' => null,
				'encodedString' => null
			);
		}


		if
		(
			   mb_substr($input, 2, 1, Codec::$referenceEncoding ) == $this->normalizeEncoding('x')
			|| mb_substr($input, 2, 1, Codec::$referenceEncoding ) == $this->normalizeEncoding('X')
		)

			return $this->parseHex($input);


		return $this->parseNumber($input);
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
	private function parseNumber($input)
	{
		// decodeCharacter and getNumericEntity should've already established that
		// the 1st 2x characters are '&#', but check again incase this method is
		// being called from elsewhere
		if (mb_substr($input, 0, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('&')
			|| mb_substr($input, 1, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('#')
		) {
			// input did not satisfy initial pattern requirements for parseNumber,
			// so return null
			return array(
				'decodedCharacter' => null,
				'encodedString' => null
			);
		}

		//get numeric characters up until first occurance of ';', return null if
		//format doesn't conform
		//Note: mb_strstr requires PHP 5.2 or greater...therefore shouldnt use here
		$integerStringAscii = "";
		$integerString      = mb_substr($input, 0, 2, Codec::$referenceEncoding );
		$inputLength        = mb_strlen($input, Codec::$referenceEncoding );
		for ($i = 2; $i < $inputLength; $i++) {
			// Get the ordinal value of the character.
			list(, $ordinalValue) = unpack("N", mb_substr($input, $i, 1, Codec::$referenceEncoding ));

			// if character is a digit, add it and keep on going
			if (preg_match("/^[0-9]/", chr($ordinalValue))) {
				$integerString .= mb_substr($input, $i, 1, Codec::$referenceEncoding );
				$integerStringAscii .= chr($ordinalValue);
			} else {
				if (mb_substr($input, $i, 1, Codec::$referenceEncoding ) == $this->normalizeEncoding(';')) {
					// if character is a semicolon, then eat it and quit
					$integerString .= mb_substr($input, $i, 1, Codec::$referenceEncoding );
					break;
				} else {
					// otherwise just quit
					break;
				}
			}
		}
		try {
			$parsedInteger   = (int) $integerStringAscii;
			$parsedCharacter = $this->normalizeEncoding(chr($parsedInteger));

			return array(
				'decodedCharacter' => $parsedCharacter,
				'encodedString' => $integerString
			);
		} catch (Exception $e) {
			//TODO: throw an exception for malformed entity?
			return array(
				'decodedCharacter' => null,
				'encodedString' => mb_substr($input, 0, $i + 1, Codec::$referenceEncoding )
			);
		}
	}

	/**
	 * Parse a hex encoded entity.
	 *
	 * @param string $input Hex encoded input (such as 437ae;)
	 *
	 * @return array Returns an array containing two objects: 'decodedCharacter' =>
	 *               NULL if input is NULL, the character of input after decoding
	 *               'encodedString' => the string that was decoded or found to be
	 *               malformed
	 */
	private function parseHex($input)
	{
		// decodeCharacter and getNumericEntity should've already established that
		//the 1st 3x characters are '&#x' or '&#X', but check again incase this
		//method is being called from elsewhere
		if (mb_substr($input, 0, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('&')
			|| mb_substr($input, 1, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('#')
			|| (mb_substr($input, 2, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('x')
			&& mb_substr($input, 2, 1, Codec::$referenceEncoding ) != $this->normalizeEncoding('X'))
		) {
			// input did not satisfy initial pattern requirements for parseHex,
			//so return null
			return array(
				'decodedCharacter' => null,
				'encodedString' => null
			);
		}
		//todo: encoding should be UTF-32, so why detect it?
		$hexString         = mb_convert_encoding("", mb_detect_encoding($input));
		//todo: encoding should be UTF-32, so why detect it?;
		$trailingSemicolon = mb_convert_encoding("", mb_detect_encoding($input));
		$inputLength       = mb_strlen($input, Codec::$referenceEncoding );
		for ($i = 3; $i < $inputLength; $i++) {
			// Get the ordinal value of the character.
			list(, $ordinalValue) = unpack("N", mb_substr($input, $i, 1, Codec::$referenceEncoding ));

			// if character is a hex digit, add it and keep on going
			if (preg_match("/^[0-9a-fA-F]/", chr($ordinalValue))) {
				// hex digit found, add it and continue...
				$hexString .= mb_substr($input, $i, 1, Codec::$referenceEncoding );
			} else {
				if (mb_substr($input, $i, 1, Codec::$referenceEncoding ) == $this->normalizeEncoding(';')) {
					// if character is a semicolon, then eat it and quit
					$trailingSemicolon = $this->normalizeEncoding(';');
					break;
				} else {
					// otherwise just quit
					break;
				}
			}
		}
		try {
			// try to convert hexString to integer...
			$parsedInteger = (int) hexdec($hexString);
			if ($parsedInteger <= 0xFF) {
				$parsedCharacter = chr($parsedInteger);
			} else {
				$parsedCharacter = mb_convert_encoding('&#' . $parsedInteger . ';', 'UTF-8', 'HTML-ENTITIES');
			}
			$parsedCharacter = $this->normalizeEncoding($parsedCharacter);

			return array(
				'decodedCharacter' => $parsedCharacter,
				'encodedString' => mb_substr($input, 0, 3, Codec::$referenceEncoding ) .
					$hexString . $trailingSemicolon
			);
		} catch (Exception $e) {
			//TODO: throw an exception for malformed entity?
			return array(
				'decodedCharacter' => null,
				'encodedString' => mb_substr($input, 0, $i + 1, Codec::$referenceEncoding )
			);
		}
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
	private function getNamedEntity( $input )
	{

		// Get the first 2 characters of the string.
		//
		$ampersand          = mb_substr( $input, 0, 1, Codec::referenceEncoding );
		$inputCaseUnchanged = mb_substr( $input, 1, 1, Codec::referenceEncoding );


		// - make sure that the 1st character is '&'
		// - there is a second character
		// - it's alphanumeric
		//
		if
		(
			     $ampersand          !== $this->normalizeEncoding( '&' )
			||   $inputCaseUnchanged === ''
			|| ! in_array( $inputCaseUnchanged, Encoder::CHAR_ALPHANUMERICS, /* strict = */ true )
		)

			return [ 'decodedCharacter' => null, 'encodedString' => null ];


		$asciiCaseUnchanged = chr( hexdec( bin2hex( $inputCaseUnchanged ) ) );


		// Preserving the case of the first character
		//
		$inputCaseLowerPreserveFirst = $inputCaseUnchanged;
		$asciiCaseLowerPreserveFirst = $asciiCaseUnchanged;


		// The first character as lower case.
		//
		$inputCaseLower = mb_strtolower( $inputCaseUnchanged );
		$asciiCaseLower = chr( hexdec( bin2hex( $inputCaseLower ) ) );

		$entityValue   = null; // the most recently found entity name
		$originalInput = null; // the corresponding original input


		// If first char is lowercase CaseLowerPreserveFirst can be discarded.
		//
		if( $asciiCaseUnchanged === $asciiCaseLower )

			$inputCaseLowerPreserveFirst = $asciiCaseLowerPreserveFirst = null;


		// Loop through remaining characters.
		//
		$limit = min( mb_strlen( $input, Codec::referenceEncoding ), self::$longestEntity );

		for( $i = 2; $i < $limit; ++$i )
		{
			$c = mb_substr( $input, $i, 1, Codec::referenceEncoding );
			$a = chr( hexdec( bin2hex( $c ) ) );


			if( $a == ';' && $entityValue !== null )
			{
				$originalInput .= $c;
				break;
			}


			if( in_array( $c, Encoder::CHAR_ALPHANUMERICS, /* strict = */ true ) !== true )

				break;


			// we have an alphanum!
			//
			$inputCaseUnchanged .= $c;
			$asciiCaseUnchanged .= $a;


			$cLower = strtolower( $c );
			$cAscii = chr( hexdec( bin2hex( $cLower ) ) );


			if( $inputCaseLowerPreserveFirst !== null )
			{
				$inputCaseLowerPreserveFirst .= $cLower;
				$asciiCaseLowerPreserveFirst .= $cAscii;
			}


			$asciiCaseLower .= $cAscii;
			$inputCaseLower .= $cLower;


			if
			(
				   $asciiCaseLower !== $asciiCaseUnchanged
				&& array_key_exists( $asciiCaseLower, self::$entityToCharacterMap )
			)
			{
				$entityValue   = self::$entityToCharacterMap[ $asciiCaseLower ];
				$originalInput = $inputCaseLower;
			}


			if
			(
				   $asciiCaseLowerPreserveFirst !== null
				&& $asciiCaseLowerPreserveFirst !== $asciiCaseLower
				&& array_key_exists( $asciiCaseLowerPreserveFirst, self::$entityToCharacterMap )
			)
			{
				$entityValue   = self::$entityToCharacterMap[ $asciiCaseLowerPreserveFirst ];
				$originalInput = $inputCaseLowerPreserveFirst;
			}


			if( array_key_exists( $asciiCaseUnchanged, self::$entityToCharacterMap ) )
			{
				$entityValue   = self::$entityToCharacterMap[ $asciiCaseUnchanged ];
				$originalInput = $inputCaseUnchanged;
			}

		}


		if( $originalInput !== null )

			$originalInput = $this->normalizeEncoding( '&' ) . $originalInput;


		return
		[
			  'decodedCharacter' => $entityValue
			, 'encodedString'    => $originalInput
		];
	}



	/**
	 * Initialize the entityNames array with all possible named entities.
	 *
	 * @return does not return a value.
	 */
	protected
	static
	function initialize()
	{
		$entityNames  =
		[
			  "quot"          /* 34 : quotation mark */
			, "amp"           /* 38 : ampersand */
			, "lt"            /* 60 : less-than sign */
			, "gt"            /* 62 : greater-than sign */
			, "nbsp"          /* 160 : no-break space */
			, "iexcl"         /* 161 : inverted exclamation mark */
			, "cent"          /* 162 : cent sign */
			, "pound"         /* 163 : pound sign */
			, "curren"        /* 164 : currency sign */
			, "yen"           /* 165 : yen sign */
			, "brvbar"        /* 166 : broken bar */
			, "sect"          /* 167 : section sign */
			, "uml"           /* 168 : diaeresis */
			, "copy"          /* 169 : copyright sign */
			, "ordf"          /* 170 : feminine ordinal indicator */
			, "laquo"         /* 171 : left-pointing double angle quotation mark */
			, "not"           /* 172 : not sign */
			, "shy"           /* 173 : soft hyphen */
			, "reg"           /* 174 : registered sign */
			, "macr"          /* 175 : macron */
			, "deg"           /* 176 : degree sign */
			, "plusmn"        /* 177 : plus-minus sign */
			, "sup2"          /* 178 : superscript two */
			, "sup3"          /* 179 : superscript three */
			, "acute"         /* 180 : acute accent */
			, "micro"         /* 181 : micro sign */
			, "para"          /* 182 : pilcrow sign */
			, "middot"        /* 183 : middle dot */
			, "cedil"         /* 184 : cedilla */
			, "sup1"          /* 185 : superscript one */
			, "ordm"          /* 186 : masculine ordinal indicator */
			, "raquo"         /* 187 : right-pointing double angle quotation mark */
			, "frac14"        /* 188 : vulgar fraction one quarter */
			, "frac12"        /* 189 : vulgar fraction one half */
			, "frac34"        /* 190 : vulgar fraction three quarters */
			, "iquest"        /* 191 : inverted question mark */
			, "Agrave"        /* 192 : Latin capital letter a with grave */
			, "Aacute"        /* 193 : Latin capital letter a with acute */
			, "Acirc"         /* 194 : Latin capital letter a with circumflex */
			, "Atilde"        /* 195 : Latin capital letter a with tilde */
			, "Auml"          /* 196 : Latin capital letter a with diaeresis */
			, "Aring"         /* 197 : Latin capital letter a with ring above */
			, "AElig"         /* 198 : Latin capital letter ae */
			, "Ccedil"        /* 199 : Latin capital letter c with cedilla */
			, "Egrave"        /* 200 : Latin capital letter e with grave */
			, "Eacute"        /* 201 : Latin capital letter e with acute */
			, "Ecirc"         /* 202 : Latin capital letter e with circumflex */
			, "Euml"          /* 203 : Latin capital letter e with diaeresis */
			, "Igrave"        /* 204 : Latin capital letter i with grave */
			, "Iacute"        /* 205 : Latin capital letter i with acute */
			, "Icirc"         /* 206 : Latin capital letter i with circumflex */
			, "Iuml"          /* 207 : Latin capital letter i with diaeresis */
			, "ETH"           /* 208 : Latin capital letter eth */
			, "Ntilde"        /* 209 : Latin capital letter n with tilde */
			, "Ograve"        /* 210 : Latin capital letter o with grave */
			, "Oacute"        /* 211 : Latin capital letter o with acute */
			, "Ocirc"         /* 212 : Latin capital letter o with circumflex */
			, "Otilde"        /* 213 : Latin capital letter o with tilde */
			, "Ouml"          /* 214 : Latin capital letter o with diaeresis */
			, "times"         /* 215 : multiplication sign */
			, "Oslash"        /* 216 : Latin capital letter o with stroke */
			, "Ugrave"        /* 217 : Latin capital letter u with grave */
			, "Uacute"        /* 218 : Latin capital letter u with acute */
			, "Ucirc"         /* 219 : Latin capital letter u with circumflex */
			, "Uuml"          /* 220 : Latin capital letter u with diaeresis */
			, "Yacute"        /* 221 : Latin capital letter y with acute */
			, "THORN"         /* 222 : Latin capital letter thorn */
			, "szlig"         /* 223 : Latin small letter sharp s - German Eszett */
			, "agrave"        /* 224 : Latin small letter a with grave */
			, "aacute"        /* 225 : Latin small letter a with acute */
			, "acirc"         /* 226 : Latin small letter a with circumflex */
			, "atilde"        /* 227 : Latin small letter a with tilde */
			, "auml"          /* 228 : Latin small letter a with diaeresis */
			, "aring"         /* 229 : Latin small letter a with ring above */
			, "aelig"         /* 230 : Latin lowercase ligature ae */
			, "ccedil"        /* 231 : Latin small letter c with cedilla */
			, "egrave"        /* 232 : Latin small letter e with grave */
			, "eacute"        /* 233 : Latin small letter e with acute */
			, "ecirc"         /* 234 : Latin small letter e with circumflex */
			, "euml"          /* 235 : Latin small letter e with diaeresis */
			, "igrave"        /* 236 : Latin small letter i with grave */
			, "iacute"        /* 237 : Latin small letter i with acute */
			, "icirc"         /* 238 : Latin small letter i with circumflex */
			, "iuml"          /* 239 : Latin small letter i with diaeresis */
			, "eth"           /* 240 : Latin small letter eth */
			, "ntilde"        /* 241 : Latin small letter n with tilde */
			, "ograve"        /* 242 : Latin small letter o with grave */
			, "oacute"        /* 243 : Latin small letter o with acute */
			, "ocirc"         /* 244 : Latin small letter o with circumflex */
			, "otilde"        /* 245 : Latin small letter o with tilde */
			, "ouml"          /* 246 : Latin small letter o with diaeresis */
			, "divide"        /* 247 : division sign */
			, "oslash"        /* 248 : Latin small letter o with stroke */
			, "ugrave"        /* 249 : Latin small letter u with grave */
			, "uacute"        /* 250 : Latin small letter u with acute */
			, "ucirc"         /* 251 : Latin small letter u with circumflex */
			, "uuml"          /* 252 : Latin small letter u with diaeresis */
			, "yacute"        /* 253 : Latin small letter y with acute */
			, "thorn"         /* 254 : Latin small letter thorn */
			, "yuml"          /* 255 : Latin small letter y with diaeresis */
			, "OElig"         /* 338 : Latin capital ligature oe */
			, "oelig"         /* 339 : Latin small ligature oe */
			, "Scaron"        /* 352 : Latin capital letter s with caron */
			, "scaron"        /* 353 : Latin small letter s with caron */
			, "Yuml"          /* 376 : Latin capital letter y with diaeresis */
			, "fnof"          /* 402 : Latin small letter f with hook */
			, "circ"          /* 710 : modifier letter circumflex accent */
			, "tilde"         /* 732 : small tilde */
			, "Alpha"         /* 913 : Greek capital letter alpha */
			, "Beta"          /* 914 : Greek capital letter beta */
			, "Gamma"         /* 915 : Greek capital letter gamma */
			, "Delta"         /* 916 : Greek capital letter delta */
			, "Epsilon"       /* 917 : Greek capital letter epsilon */
			, "Zeta"          /* 918 : Greek capital letter zeta */
			, "Eta"           /* 919 : Greek capital letter eta */
			, "Theta"         /* 920 : Greek capital letter theta */
			, "Iota"          /* 921 : Greek capital letter iota */
			, "Kappa"         /* 922 : Greek capital letter kappa */
			, "Lambda"        /* 923 : Greek capital letter lambda */
			, "Mu"            /* 924 : Greek capital letter mu */
			, "Nu"            /* 925 : Greek capital letter nu */
			, "Xi"            /* 926 : Greek capital letter xi */
			, "Omicron"       /* 927 : Greek capital letter omicron */
			, "Pi"            /* 928 : Greek capital letter pi */
			, "Rho"           /* 929 : Greek capital letter rho */
			, "Sigma"         /* 931 : Greek capital letter sigma */
			, "Tau"           /* 932 : Greek capital letter tau */
			, "Upsilon"       /* 933 : Greek capital letter upsilon */
			, "Phi"           /* 934 : Greek capital letter phi */
			, "Chi"           /* 935 : Greek capital letter chi */
			, "Psi"           /* 936 : Greek capital letter psi */
			, "Omega"         /* 937 : Greek capital letter omega */
			, "alpha"         /* 945 : Greek small letter alpha */
			, "beta"          /* 946 : Greek small letter beta */
			, "gamma"         /* 947 : Greek small letter gamma */
			, "delta"         /* 948 : Greek small letter delta */
			, "epsilon"       /* 949 : Greek small letter epsilon */
			, "zeta"          /* 950 : Greek small letter zeta */
			, "eta"           /* 951 : Greek small letter eta */
			, "theta"         /* 952 : Greek small letter theta */
			, "iota"          /* 953 : Greek small letter iota */
			, "kappa"         /* 954 : Greek small letter kappa */
			, "lambda"        /* 955 : Greek small letter lambda */
			, "mu"            /* 956 : Greek small letter mu */
			, "nu"            /* 957 : Greek small letter nu */
			, "xi"            /* 958 : Greek small letter xi */
			, "omicron"       /* 959 : Greek small letter omicron */
			, "pi"            /* 960 : Greek small letter pi */
			, "rho"           /* 961 : Greek small letter rho */
			, "sigmaf"        /* 962 : Greek small letter final sigma */
			, "sigma"         /* 963 : Greek small letter sigma */
			, "tau"           /* 964 : Greek small letter tau */
			, "upsilon"       /* 965 : Greek small letter upsilon */
			, "phi"           /* 966 : Greek small letter phi */
			, "chi"           /* 967 : Greek small letter chi */
			, "psi"           /* 968 : Greek small letter psi */
			, "omega"         /* 969 : Greek small letter omega */
			, "thetasym"      /* 977 : Greek theta symbol */
			, "upsih"         /* 978 : Greek upsilon with hook symbol */
			, "piv"           /* 982 : Greek pi symbol */
			, "ensp"          /* 8194 : en space */
			, "emsp"          /* 8195 : em space */
			, "thinsp"        /* 8201 : thin space */
			, "zwnj"          /* 8204 : zero width non-joiner */
			, "zwj"           /* 8205 : zero width joiner */
			, "lrm"           /* 8206 : left-to-right mark */
			, "rlm"           /* 8207 : right-to-left mark */
			, "ndash"         /* 8211 : en dash */
			, "mdash"         /* 8212 : em dash */
			, "lsquo"         /* 8216 : left single quotation mark */
			, "rsquo"         /* 8217 : right single quotation mark */
			, "sbquo"         /* 8218 : single low-9 quotation mark */
			, "ldquo"         /* 8220 : left double quotation mark */
			, "rdquo"         /* 8221 : right double quotation mark */
			, "bdquo"         /* 8222 : double low-9 quotation mark */
			, "dagger"        /* 8224 : dagger */
			, "Dagger"        /* 8225 : double dagger */
			, "bull"          /* 8226 : bullet */
			, "hellip"        /* 8230 : horizontal ellipsis */
			, "permil"        /* 8240 : per mille sign */
			, "prime"         /* 8242 : prime */
			, "Prime"         /* 8243 : double prime */
			, "lsaquo"        /* 8249 : single left-pointing angle quotation mark */
			, "rsaquo"        /* 8250 : single right-pointing angle quotation mark */
			, "oline"         /* 8254 : overline */
			, "frasl"         /* 8260 : fraction slash */
			, "euro"          /* 8364 : euro sign */
			, "image"         /* 8465 : black-letter capital i */
			, "weierp"        /* 8472 : script capital p - Weierstrass p */
			, "real"          /* 8476 : black-letter capital r */
			, "trade"         /* 8482 : trademark sign */
			, "alefsym"       /* 8501 : alef symbol */
			, "larr"          /* 8592 : leftwards arrow */
			, "uarr"          /* 8593 : upwards arrow */
			, "rarr"          /* 8594 : rightwards arrow */
			, "darr"          /* 8595 : downwards arrow */
			, "harr"          /* 8596 : left right arrow */
			, "crarr"         /* 8629 : downwards arrow with corner leftwards */
			, "lArr"          /* 8656 : leftwards double arrow */
			, "uArr"          /* 8657 : upwards double arrow */
			, "rArr"          /* 8658 : rightwards double arrow */
			, "dArr"          /* 8659 : downwards double arrow */
			, "hArr"          /* 8660 : left right double arrow */
			, "forall"        /* 8704 : for all */
			, "part"          /* 8706 : partial differential */
			, "exist"         /* 8707 : there exists */
			, "empty"         /* 8709 : empty set */
			, "nabla"         /* 8711 : nabla */
			, "isin"          /* 8712 : element of */
			, "notin"         /* 8713 : not an element of */
			, "ni"            /* 8715 : contains as member */
			, "prod"          /* 8719 : n-ary product */
			, "sum"           /* 8721 : n-ary summation */
			, "minus"         /* 8722 : minus sign */
			, "lowast"        /* 8727 : asterisk operator */
			, "radic"         /* 8730 : square root */
			, "prop"          /* 8733 : proportional to */
			, "infin"         /* 8734 : infinity */
			, "ang"           /* 8736 : angle */
			, "and"           /* 8743 : logical and */
			, "or"            /* 8744 : logical or */
			, "cap"           /* 8745 : intersection */
			, "cup"           /* 8746 : union */
			, "int"           /* 8747 : integral */
			, "there4"        /* 8756 : therefore */
			, "sim"           /* 8764 : tilde operator */
			, "cong"          /* 8773 : congruent to */
			, "asymp"         /* 8776 : almost equal to */
			, "ne"            /* 8800 : not equal to */
			, "equiv"         /* 8801 : identical to - equivalent to */
			, "le"            /* 8804 : less-than or equal to */
			, "ge"            /* 8805 : greater-than or equal to */
			, "sub"           /* 8834 : subset of */
			, "sup"           /* 8835 : superset of */
			, "nsub"          /* 8836 : not a subset of */
			, "sube"          /* 8838 : subset of or equal to */
			, "supe"          /* 8839 : superset of or equal to */
			, "oplus"         /* 8853 : circled plus */
			, "otimes"        /* 8855 : circled times */
			, "perp"          /* 8869 : up tack */
			, "sdot"          /* 8901 : dot operator */
			, "lceil"         /* 8968 : left ceiling */
			, "rceil"         /* 8969 : right ceiling */
			, "lfloor"        /* 8970 : left floor */
			, "rfloor"        /* 8971 : right floor */
			, "lang"          /* 9001 : left-pointing angle bracket */
			, "rang"          /* 9002 : right-pointing angle bracket */
			, "loz"           /* 9674 : lozenge */
			, "spades"        /* 9824 : black spade suit */
			, "clubs"         /* 9827 : black club suit */
			, "hearts"        /* 9829 : black heart suit */
			, "diams"         /* 9830 : black diamond suit */
		];


		for( $i = 0; $i < count( $entityNames ); $i++ )
		{
			$character = html_entity_decode( '&' . $entityNames[ $i ] . ';', ENT_QUOTES, 'UTF-8' );


			// Normalize encoding to UTF-32
			//
			self::$characterToEntityMap[ $character         ] = $entityNames[ $i ] ;
			self::$entityToCharacterMap[ $entityNames[ $i ] ] = $character         ;


			// get the length of the longest entity name
			//
			self::$longestEntity = max( self::$longestEntity, mb_strlen( $entityNames[ $i ], 'UTF-8' ) );

		}

		self::$longestEntity    += 2   ; // for & and ;
		self::$mapIsInitialized  = true;
	}
}
