<?php
/**
 *
 */



namespace Golem\Reference\Codecs;

use

	  Golem\Golem

	, Golem\Reference\Traits\Seal
	, Golem\Reference\Traits\HasOptions
	, Golem\Reference\Traits\HasLog

	, Golem\Reference\Data\String

	, Golem\Reference\Util
;

/**
 * The Codec interface defines a set of methods for encoding and decoding
 * application level encoding schemes, such as HTML entity encoding and percent
 * encoding (aka URL encoding). Codecs are used in output encoding and
 * canonicalization.  The design of these codecs allows for
 * character-by-character decoding, which is necessary to detect double-encoding
 * and the use of multiple encoding schemes, both of which are techniques used
 * by attackers to bypass validation and bury encoded attacks in data.
 *
 */
abstract
class Codec
{
	use Seal, HasOptions, HasLog;


	private static $initialized = false;


	/*
	 * Standard character sets.
	 */
	static protected $LOWERS        ;
	static protected $UPPERS        ;
	static protected $DIGITS        ;
	static protected $SPECIALS      ;
	static protected $LETTERS       ;
	static protected $ALPHANUMERICS ;

	/*
	 * Password character sets.
	 */
	/**
	 * Lower case alphabet, for passwords, which excludes 'l', 'i' and 'o'.
	 */
	static protected $PASSWORD_LOWERS;

	/**
	 * Upper case alphabet, for passwords, which excludes 'I' and 'O'.
	 */
	static protected $PASSWORD_UPPERS;

	/**
	 * Numerical digits, for passwords, which excludes '0'.
	 */
	static protected $PASSWORD_DIGITS;

	/**
	 * Special characters, for passwords, excluding '|' which resembles
	 * alphanumeric characters 'i' and '1' and excluding '+' used in URL
	 * encoding.
	 */
	static protected $PASSWORD_SPECIALS;

	/**
	 * Union of Encoder::CHAR_PASSWORD_LOWERS and Encoder::CHAR_PASSWORD_UPPERS.
	 */
	static protected $PASSWORD_LETTERS;


	protected $golem;


	protected
	function __construct( Golem $golem, array $defaults = [], array $options = [] )
	{
		$this->golem = $golem;

		$this->setupOptions( $defaults, $options );
		$this->setupLog();


		self::initialize();
	}


	static
	private
	function initialize()
	{
		if( self::$initialized )

			return;


		self::$initialized = true;

		self::$LOWERS        = str_split( 'abcdefghijklmnopqrstuvwxyz' );
		self::$UPPERS        = str_split( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' );
		self::$DIGITS        = str_split( '0123456789'                 );
		self::$SPECIALS      = str_split( '.-_!@$^*=~|+?'              );
		self::$LETTERS       = array_merge( self::$LOWERS , self::$UPPERS );
		self::$ALPHANUMERICS = array_merge( self::$LETTERS, self::$DIGITS );

		/*
		 * Password character sets.
		 */
		/**
		 * Lower case alphabet, for passwords, which excludes 'l', 'i' and 'o'.
		 */
		self::$PASSWORD_LOWERS = str_split( 'abcdefghjkmnpqrstuvwxyz' );

		/**
		 * Upper case alphabet, for passwords, which excludes 'I' and 'O'.
		 */
		self::$PASSWORD_UPPERS = str_split( 'ABCDEFGHJKLMNPQRSTUVWXYZ' );

		/**
		 * Numerical digits, for passwords, which excludes '0'.
		 */
		self::$PASSWORD_DIGITS = str_split( '123456789' );

		/**
		 * Special characters, for passwords, excluding '|' which resembles
		 * alphanumeric characters 'i' and '1'.
		 */
		self::$PASSWORD_SPECIALS = str_split( '.-_!@$*=?+' );


		self::$PASSWORD_LETTERS = array_merge( self::$PASSWORD_LOWERS, self::$PASSWORD_UPPERS );
	}



	/**
	 * Encode a String with a Codec.
	 *
	 * @param string $input  the String to encode.
	 *
	 * @return string the encoded string.
	 */
	public function encode( $input )
	{
		if( $input === null )

			return null;


		// Make sure character encoding is valid
		//
		$input  = $this->golem->string( $input );
		$output = $this->golem->string( ''     );


		while( $input->length() )

			$output->push( $this->encodeCharacter( $input->shift() ) );


		return $output->content();
	}



	/**
	 * Decode a String that was encoded using the encode method in this Class.
	 *
	 * @param string $input The String to decode
	 *
	 * @return string returns the decoded string, otherwise NULL
	 */
	public function decode($input)
	{

		// Normalize string to UTF-32
		$_4ByteString = self::normalizeEncoding($input);

		// debug
		CodecDebug::getInstance()->addEncodedString($_4ByteString);

		// Start with an empty string.
		$decodedString           = '';
		$targetCharacterEncoding = 'ASCII';

		//logic to iterate through the string's characters, while (input has
		//characters remaining){} feed whole sequence into decoder, which then
		//determines the first decoded character from the input and "pushes back"
		//the encodedPortion of seuquence and the resultant decodedCharacter to here
		while (mb_strlen($_4ByteString, "UTF-32") > 0) {
			// get the first decodedCharacter, allowing decodeCharacter to eat
			//away at the string

			//decodeCharacter() returns an array containing 'decodedCharacter' and
			//'encodedString' so as to provide PushbackString-(from-ESAPI-JAVA)-like
			//behaviour
			$decodeResult = $this->decodeCharacter($_4ByteString);

			//note: decodedCharacter should be UTF-32 encoded already
			$decodedCharacter = $decodeResult['decodedCharacter'];

			$encodedString = $decodeResult['encodedString'];

			if ($decodedCharacter !== null) {
				// Append the decoded character to the output string and remove
				// the sequence of characters that formed an entity or numeric
				// encoding of that character from the start of the input string.
				if ($decodedCharacter != '') {
					$resultOfAppend = $this->_appendCharacterToOuput(
						$decodedCharacter,
						$decodedString,
						$targetCharacterEncoding
					);

					if ($resultOfAppend != true) {
						// Decoded character has an Invalid codepoint so remove
						// the first character from the encoded string
						// $_4ByteString and append it to the decoded string.
						$charToAppend   = mb_substr($_4ByteString, 0, 1, 'UTF-32');
						$resultOfAppend = $this->_appendCharacterToOuput(
							$charToAppend,
							$decodedString,
							$targetCharacterEncoding
						);
						if ($resultOfAppend != true) {
							// We can do two things here, throw EncodingException
							// or ignore the dodgy character.  This situation is
							// an exceptional one and shouldn't happen often...
							throw new EncodingException(
								'Error encountered whilst decoding Input.',
								'A sequence of characters was recognised as using ' .
								'a valid encoding scheme, but the character it ' .
								'encodes is not a valid Unicode CodePoint. ' .
								'The first character in the sequence is also not' .
								'a valid Unicode CodePoint so decoding was aborted'
							);
						}
						// remove the first character from the input string.
						$encStringLen = mb_strlen($_4ByteString, 'UTF-32');
						$_4ByteString = mb_substr($_4ByteString, 1, $encStringLen, 'UTF-32');
						continue;
					}
				}

				// remove the encodedString portion off the start of the input
				// string.
				$entityLen    = mb_strlen($encodedString, 'UTF-32');
				$encStringLen = mb_strlen($_4ByteString, 'UTF-32');
				$_4ByteString = mb_substr($_4ByteString, $entityLen, $encStringLen, 'UTF-32');
			} else {
				// decodedCharacter is null, so add the single, unencoded
				// character to the decodedString and remove the 1st character
				// from the start of the input string.
				$charToAppend   = mb_substr($_4ByteString, 0, 1, 'UTF-32');
				$resultOfAppend = $this->_appendCharacterToOuput(
					$charToAppend,
					$decodedString,
					$targetCharacterEncoding
				);
				if ($resultOfAppend !== true) {
					// The first character in the remaining string of input
					// characters is not a Valid Unicode CodePoint.  We could
					// throw EncodingException here, but instead we'll forget
					// about it and log a warning.
					ESAPI::getLogger('Codec')->warn(
						DefaultLogger::SECURITY,
						false,
						'Input contained a character with an invalid Unicode ' .
						'CodePoint. We destroyed it!'
					);
				}

				// eat the single, unencoded character portion off the start of the
				// UTF-32 converted input string
				$encStringLen = mb_strlen($_4ByteString, 'UTF-32');
				$_4ByteString = mb_substr($_4ByteString, 1, $encStringLen, 'UTF-32');
			}
		}

		// debug
		CodecDebug::getInstance()->output($decodedString);

		return $decodedString;
	}

	/**
	 * Helper method which handles appending a UTF-32 character to the output
	 * string of decode methods such that the output string does not contain
	 * mixed character encodings. The method adjusts the character encoding of
	 * the output string so that the character to append can exist in the set
	 * of characters allowed in a given character encoding. Usually this means
	 * converting the output string and character to UTF-8.
	 *
	 * @param string &$character_UTF32 String character to append (UTF-32).
	 * @param string &$targetString    String target.
	 * @param string &$targetCharEnc   String target character encoding name.
	 *
	 * @return bool returns TRUE if the character was successfully appended to the
	 *              target FALSE otherwise.
	 */
	private function _appendCharacterToOuput(&$character_UTF32, &$targetString, &$targetCharEnc)
	{
		list(, $ordinalValue) = unpack('N', $character_UTF32);

		if ($ordinalValue > 0x110000) {
			return false; // Invalid code point.
		}

		if ($ordinalValue >= 0x00 && $ordinalValue <= 0x7F) {
			// An ASCII character can be appended to a string of any character
			// encoding
			$targetString .= mb_convert_encoding(
				$character_UTF32,
				'ASCII',
				"UTF-32"
			);
		} elseif ($ordinalValue <= 0x10FFFF) {
			// convert the decoded character to UTF-8
			$character_UTF8 = mb_convert_encoding(
				$character_UTF32,
				'UTF-8',
				'UTF-32'
			);

			// convert decodedString to UTF-8 if necessary
			if ($targetString !== '' && $targetCharEnc != 'UTF-8') {
				$targetString = mb_convert_encoding(
					$targetString,
					'UTF-8',
					$targetCharEnc
				);
			}

			// now append the character to the string
			$targetString .= $character_UTF8;

			// see if decodedString can exist in
			// targetCharacterEncoding and if so, convert back to
			// it. Otherwise the target character encoding is
			// changed to 'UTF-8'
			if ($targetCharEnc != 'UTF-8'
				&& $targetCharEnc
				=== mb_detect_encoding($targetString, $targetCharEnc, true)
			) {
				// we can convert back to target encoding
				$targetString = mb_convert_encoding(
					$targetString,
					$targetCharEnc,
					'UTF-8'
				);
			} else {
				// decoded String now contains characters that are
				// UTF-8
				$targetCharEnc = 'UTF-8';
			}
		}

		return true;
	}




	/**
	 * Utility to get first (potentially multibyte) character from a (potentially
	 * multicharacter) multibyte string.
	 *
	 * @param string $string String to convert
	 *
	 * @return string converted string
	 */
	public
	static
	function firstCharacter( $string )
	{
		return mb_substr( $string, 0, 1 );
	}
}
