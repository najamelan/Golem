<?php
/**
 *
 */

namespace Golem;

use

	  Golem\Golem

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions

	, Golem\Util
	, Golem\Codecs\HTML5

;


class Encoder
// implements iEncoder
{
use Seal, HasOptions;


private $golem    ;

private $htmlTextCodec;
private $htmlAttrCodec;

private $base64Codec;
private $cssCodec;
private $javascriptCodec;
private $percentCodec;
private $vbscriptCodec;
private $xmlCodec;

/*
 * Character sets that define characters (in addition to alphanumerics) that are
 * immune from encoding in various formats
 */


private $_codecs = [];
private $_auditor;








/**
 * Encoder constructor.
 *
 * @param array $codecs An array of Codec instances which will be used for
 *                      canonicalization.
 *
 * @throws InvalidArgumentException
 *
 * @return does not return a value.
 */
public function __construct( Golem $golem, array $options = [] )
{
	$this->golem = $golem;

	$this->setupOptions( $golem->options( 'Encoder' ), $options );
}



/**
 * @inheritdoc
 */
public function encodeForCSS($input)
{
	if( $input === null ) {
		return null;
	}

	return $this->_cssCodec->encode( $this->_immune_css, $input );
}

/**
 * @inheritdoc
 */
public function htmlText( $input )
{
	if( ! $this->htmlTextCodec )

		$this->htmlTextCodec = new HTML5( $this->golem, /* $context = */ 'text' );


	return $this->htmlTextCodec->encode( $input );
}

/**
 * @inheritdoc
 */
public function htmlAttr( $input )
{
	if( ! $this->htmlAttrCodec )

		$this->htmlAttrCodec = new HTML5( $this->golem, /* $context = */ 'attribute' );


	return $this->htmlAttrCodec->encode( $input );
}

/**
 * @inheritdoc
 */
public function encodeForJavaScript($input)
{
	if ($input === null) {
		return null;
	}

	return $this->_javascriptCodec->encode($this->_immune_javascript, $input);
}

/**
 * @inheritdoc
 */
public function encodeForVBScript($input)
{
	if ($input === null) {
		return null;
	}

	return $this->_vbscriptCodec->encode($this->_immune_vbscript, $input);
}

/**
 * @inheritdoc
 */
public function encodeForSQL($codec, $input)
{
	if ($input === null) {
		return null;
	}

	return $codec->encode($this->_immune_sql, $input);
}

/**
 * @inheritdoc
 */
public function encodeForOS($codec, $input)
{
	if ($input === null) {
		return null;
	}

	if ($codec instanceof Codec == false) {
		ESAPI::getLogger('Encoder')->error(
			ESAPILogger::SECURITY,
			false,
			'Invalid Argument, expected an instance of an OS Codec.'
		);

		return null;
	}

	return $codec->encode($this->_immune_os, $input);
}

/**
 * @inheritdoc
 */
public function encodeForXPath($input)
{
	if ($input === null) {
		return null;
	}

	return $this->HTML->encode($this->_immune_xpath, $input);
}

/**
 * @inheritdoc
 */
public function encodeForXML($input)
{
	if ($input === null) {
		return null;
	}

	return $this->_xmlCodec->encode($this->_immune_xml, $input);
}

/**
 * @inheritdoc
 */
public function encodeForXMLAttribute($input)
{
	if ($input === null) {
		return null;
	}

	return $this->_xmlCodec->encode($this->_immune_xmlattr, $input);
}

/**
 * @inheritdoc
 */
public function encodeForURL($input)
{
	if ($input === null) {
		return null;
	}
	$encoded = $this->_percentCodec->encode($this->_immune_url, $input);

	$initialEncoding = $this->_percentCodec->detectEncoding($encoded);
	$decodedString = mb_convert_encoding('', $initialEncoding);

	$pcnt = $this->_percentCodec->normalizeEncoding('%');
	$two  = $this->_percentCodec->normalizeEncoding('2');
	$zero = $this->_percentCodec->normalizeEncoding('0');
	$char_plus = mb_convert_encoding('+', $initialEncoding);

	$index = 0;
	$limit = mb_strlen($encoded, $initialEncoding);
	for ($i = 0; $i < $limit; $i++) {
		if ($index > $i) {
			continue; // already dealt with this character
		}
		$c = mb_substr($encoded, $i, 1, $initialEncoding);
		$d = mb_substr($encoded, $i + 1, 1, $initialEncoding);
		$e = mb_substr($encoded, $i + 2, 1, $initialEncoding);
		if ($this->_percentCodec->normalizeEncoding($c) == $pcnt
			&& $this->_percentCodec->normalizeEncoding($d) == $two
			&& $this->_percentCodec->normalizeEncoding($e) == $zero
		) {
			$decodedString .= $char_plus;
			$index += 3;
		} else {
			$decodedString .= $c;
			$index++;
		}
	}

	return $decodedString;
}

/**
 * @inheritdoc
 */
public function decodeFromURL($input)
{
	if ($input === null) {
		return null;
	}
	$canonical = $this->canonicalize($input, true);

	// Replace '+' with ' '
	$initialEncoding = $this->_percentCodec->detectEncoding($canonical);
	$decodedString = mb_convert_encoding('', $initialEncoding);

	$find = $this->_percentCodec->normalizeEncoding('+');
	$char_space = mb_convert_encoding(' ', $initialEncoding);

	$limit = mb_strlen($canonical, $initialEncoding);
	for ($i = 0; $i < $limit; $i++) {
		$c = mb_substr($canonical, $i, 1, $initialEncoding);
		if ($this->_percentCodec->normalizeEncoding($c) == $find) {
			$decodedString .= $char_space;
		} else {
			$decodedString .= $c;
		}
	}

	return $this->_percentCodec->decode($decodedString);
}

/**
 * @inheritdoc
 */
public function encodeForBase64($input, $wrap = true)
{
	if ($input === null) {
		return null;
	}

	return $this->_base64Codec->encode($input, $wrap);
}

/**
 * @inheritdoc
 */
public function decodeFromBase64($input)
{
	if ($input === null) {
		return null;
	}

	return $this->_base64Codec->decode($input);
}
}
