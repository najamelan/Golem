<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Codecs\HTML

;


class   HTMLTest
extends \PHPUnit_Framework_TestCase
{
	private static $golem           ;
	private static $encoder         ;
	private static $immuneText      ;
	private static $immuneAttribute ;


	public static function setUpBeforeClass()
   {
   	self::$golem           = new Golem;
   	self::$encoder         = self::$golem->encoder();
   	self::$immuneText      = self::$golem->options( 'Codec', 'HTML', 'immuneText'      );
   	self::$immuneAttribute = self::$golem->options( 'Codec', 'HTML', 'immuneAttribute' );
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


			  // Immunes from configuration file
			  //
			, [
			       self::$immuneText
			     , self::$immuneText
			  ]


			  // Test a named entity
			  //
			, [
			       'ê'
			     , '&ecirc;'
			  ]


			  // Replace control characters by spaces
			  //
			, [
			         'a' . chr(0  ) . 'b' . chr(4  ) . 'c' . chr(127) . 'd' . chr(128)
			       . 'e' . chr(129) . 'f' . chr(150) . 'g' . chr(159) . 'h'

			     , 'a b c d e f g h'
			  ]


			  // Properly encode \t \r \n
			  //
			, [
			       'a' . chr(9) . 'b' . chr(10) . 'c' . chr(13) . 'd'
			     , 'a&#x9;b&#xa;c&#xd;d'
			  ]


			  // Encode script tag
			  //
			, [ '<script>', '&lt;script&gt;' ]


			  // Encoded script tag
			  //
			, [ '&lt;script&gt;', '&amp;lt&#x3b;script&amp;gt&#x3b;' ]


			  // More complete script tag
			  //
			, [
			       '"><script>alert(/XSS/)</script><fooattr="'
			     , '&quot;&gt;&lt;script&gt;alert&#x28;&#x2f;XSS&#x2f;&#x29;&lt;&#x2f;script&gt;&lt;fooattr&#x3d;&quot;'
			  ]


			  // Encode special chars
			  //
			, [ '!@$%()=+{}[]', '&#x21;&#x40;&#x24;&#x25;&#x28;&#x29;&#x3d;&#x2b;&#x7b;&#x7d;&#x5b;&#x5d;' ]


			  // Test ampersand EoS
			  //
			, [ 'dir&', 'dir&amp;' ]


			  // Test ampersand mid string
			  //
			, [ 'one&two', 'one&amp;two' ]
		];
	}
}
