<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Data\File

;


class FileTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }



	public
	function	testConstructor()
	{
		$file = new File( self::$golem, 'tester.php' );
		$this->assertEquals( $file->name(), 'tester.php' );
	}



	public
	function	testContent()
	{
		$file = new File( self::$golem, __DIR__ . '/../TestData/someData' );
		$this->assertEquals( $file->content(), "12345\n" );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testContentNoExist()
	{
		$file = new File( self::$golem, 'doesntexist.php' );
		$contents = $file->content();
	}



	public
	function	testParser()
	{
		$file = new File( self::$golem, __DIR__ . '/../TestData/testGolem.yml' );
		$this->assertEquals( $file->parse(), [ 'Logger' => [ 'name' => 'Olé' ] ] );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testParserWrongExtension()
	{
		$file = new File( self::$golem, __DIR__ . '/../TestData/someData' );
		$file->parse();
	}
}
