<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Data\File

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
		$file = new File( 'tester.php' );
		$this->assertEquals( $file->filename(), 'tester.php' );
	}



	public
	function	testReadFile()
	{
		$file = new File( __DIR__ . '/../TestData/someData' );
		$this->assertEquals( $file->readFile(), "12345\n" );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testReadFileNoExist()
	{
		$file = new File( 'doesntexist.php' );
		$contents = $file->readFile();
	}



	public
	function	testParser()
	{
		$file = new File( __DIR__ . '/../TestData/testGolem.yml' );
		$this->assertEquals( $file->parse(), [ 'logger' => [ 'prefix' => 'Olé' ] ] );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testParserWrongExtension()
	{
		$file = new File( __DIR__ . '/../TestData/someData' );
		$file->parse();
	}
}
