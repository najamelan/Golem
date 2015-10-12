<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Codecs\HTML5

;


class   HTMLTest
extends \PHPUnit_Framework_TestCase
{
	private static $golem           ;
	private static $encoder         ;
	private static $immuneText      ;
	private static $immuneAttribute ;
	private static $encSubstitute   ;
	private static $htmlSubstitute  ;
	private static $initialized = false;


	public static function setUpBeforeClass()
   {
   	if( self::$initialized )

   		return;

   	self::$initialized     = true;
   	self::$golem           = new Golem;
   	self::$encoder         = self::$golem->encoder();
   	self::$immuneText      = self::$golem->options( 'Codec' , 'HTML5', 'immuneText'      );
   	self::$immuneAttribute = self::$golem->options( 'Codec' , 'HTML5', 'immuneAttribute' );
   	self::$htmlSubstitute  = self::$golem->options( 'Codec' , 'HTML5', 'substitute'      );

   	self::$encSubstitute   = '&#x' . dechex( self::$golem->options( 'String', 'substitute' ) ) . ';';
   }



   /**
    * @dataProvider encodeData
    *
    */
	public
	function	testEncodeCharacterText( $input, $expected )
	{
		$this->assertEquals( $expected, self::$encoder->htmlText( $input ) );
	}



   /**
    * @dataProvider encodeData
    *
    */
	public
	function	testEncodeCharacterAttr( $input, $expected )
	{
		$this->assertEquals( $expected, self::$encoder->htmlAttr( $input ) );
	}



   /**
    *
    *
    */
	public
	function	testEncodeImmune()
	{
		$this->assertEquals( self::$immuneText     , self::$encoder->htmlText( self::$immuneText      ) );
		$this->assertEquals( self::$immuneAttribute, self::$encoder->htmlAttr( self::$immuneAttribute ) );


		if( self::$immuneText !== self::$immuneAttribute )

			$this->assertNotEquals
			(
				  self::$encoder->htmlText( self::$immuneText ) . self::$encoder->htmlText( self::$immuneAttribute )
				, self::$encoder->htmlAttr( self::$immuneText ) . self::$encoder->htmlAttr( self::$immuneAttribute )
			)
		;
	}





	public
	function encodeData()
	{

		// Thanks phpunit for calling data providers before setupBeforeClass
		//
		self::setupBeforeClass();

		return
		[
			  [ null, null ]
			, [ ''  , ''   ]


			  // Alfphanumerics should be immune
			  //
			, [
			       'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
			     , 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
			  ]


			  // Test a named entity
			  //
			, [
			       'ê'
			     , '&ecirc;'
			  ]


			  // Make sure control characters are encoded
			  //
			, [
			         'a' . chr(0  ) . 'b' . chr(4  ) . 'c' . chr(127) . 'd' . chr(128)
			       . 'e' . chr(129) . 'f' . chr(150) . 'g' . chr(159) . 'h'

			     ,   'a' . self::$htmlSubstitute
			       . 'b' . self::$htmlSubstitute
			       . 'c' . self::$htmlSubstitute
			       . 'd' . self::$encSubstitute
			       . 'e' . self::$encSubstitute
			       . 'f' . self::$encSubstitute
			       . 'g' . self::$encSubstitute
			       . 'h'
			  ]


			  // Properly encode \t \r \n and form feed
			  //
			, [
			       'a' . chr(9) . 'b' . chr(10) . 'c' . chr(12) . 'd' . chr(13) . 'e'
			     , 'a&Tab;b&NewLine;c&#xc;d'. self::$htmlSubstitute . 'e'
			  ]


			  // Encode script tag
			  //
			, [ '<script>', '&lt;script&gt;' ]


			  // Encoded script tag
			  //
			, [ '&lt;script&gt;', '&amp;lt&semi;script&amp;gt&semi;' ]


			  // More complete script tag
			  //
			, [
			       '"><script>alert(/XSS/)</script><fooattr="'
			     , '&quot;&gt;&lt;script&gt;alert&lpar;&sol;XSS&sol;&rpar;&lt;&sol;script&gt;&lt;fooattr&bne;&quot;'
			  ]


			  // Encode special chars
			  //
			, [ '!@$%()=+{}[]', '&excl;&commat;&dollar;&percnt;&lpar;&rpar;&bne;&plus;&lbrace;&rbrace;&lbrack;&rbrack;' ]


			  // Test ampersand at the beginning
			  //
			, [ '&dir', '&amp;dir' ]


			  // Test ampersand EoS
			  //
			, [ 'dir&', 'dir&amp;' ]


			  // Test ampersand mid string
			  //
			, [ 'one&two', 'one&amp;two' ]


			  // Test some kana
			  //
			, [ 'ｳﾞｶｷｸ', '&#xff73;&#xff9e;&#xff76;&#xff77;&#xff78;' ]
		];
	}
}
