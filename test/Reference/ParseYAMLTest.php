<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Codecs\ParseYAML

;


class ParseYAMLTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }



	public
	function	testDecode()
	{
		$yml = new ParseYAML;
		$this->assertEquals( $yml->decode( "test: { some: 'array' }" ), [ 'test' => [ 'some' => 'array' ] ] );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testDecodeInvalid()
	{
		$yml = new ParseYAML;
		$yml->decode( ":mlkj\n:" );
	}



	public
	function	testEncode()
	{
		$this->markTestIncomplete( 'Not yet implemented' );

		$yml = new ParseYAML;
		$this->assertEquals( $yml->encode( [ 'test' => [ 'some' => 'array' ] ] ), "test: { some: 'array' }" );
	}
}
