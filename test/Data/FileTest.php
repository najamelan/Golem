<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Data\File

;




class FileTest extends \PHPUnit_Framework_TestCase
{

private static $golem;
private static $testDir;


public
static
function setUpBeforeClass()
{
	self::$golem   = new Golem;
	self::$testDir = self::$golem->file( __DIR__ . '/../TestData/FileTests' );

	self::$testDir->rm();
	self::$testDir->mkdir();
}



public
static
function tearDownAfterClass()
{
	self::$testDir->rm();
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



/**
 * Test File creation
 *
 */
public
function	testCreateFile()
{
	$file = self::$golem->file( self::$testDir . '/createFile' );
	$this->assertFileNotExists( $file->path()->raw() );

		$file->touch();

	$this->assertFileExists( $file->path  ()->raw() );
	$this->assertFalse     ( $file->isDir ()        );
	$this->assertFalse     ( $file->isLink()        );

	return $file;
}



public
function	testCreateDirectory()
{
	$dir = self::$golem->file( self::$testDir . '/createDirectory' );
	$this->assertFileNotExists( $dir->path()->raw() );

		$dir->mkdir();

	$this->assertFileExists( $dir->path  ()->raw() );
	$this->assertTrue      ( $dir->isDir ()        );
	$this->assertFalse     ( $dir->isLink()        );

	return $dir;
}



/**
 * Test file deletion
 *
 * @depends testCreateFile
 *
 */
public
function	testDeleteFile( File $file )
{
	$file->rm();

	$this->assertFileNotExists( $file->path()->raw() );
}



/**
 * Test empty directory deletion
 *
 * @depends testCreateDirectory
 *
 */
public
function	testDeleteDirectory( File $dir )
{
	$dir->rm();

	$this->assertFileNotExists( $dir->path()->raw() );
}



/**
 * Test empty directory deletion
 *
 */
public
function	testDeleteFullDirectory()
{
	$dir      = self::$golem->file( self::$testDir . '/deleteFullDirectory' );
	$file1    = self::$golem->file( $dir           . '/childFile'           );

	$childDir = self::$golem->file( $dir           . '/childDirectory'      );
	$file2    = self::$golem->file( $childDir      . '/nestedFile'          );

	$dir     ->mkdir();
	$childDir->mkdir();

	$file1   ->touch();
	$file2   ->touch();

	$this->assertFileExists( $dir     ->path  ()->raw() );
	$this->assertTrue      ( $dir     ->isDir ()        );
	$this->assertFalse     ( $dir     ->isLink()        );

	$this->assertFileExists( $childDir->path  ()->raw() );
	$this->assertTrue      ( $childDir->isDir ()        );
	$this->assertFalse     ( $childDir->isLink()        );

	$this->assertFileExists( $file1   ->path  ()->raw() );
	$this->assertFalse     ( $file1   ->isDir ()        );
	$this->assertFalse     ( $file1   ->isLink()        );

	$this->assertFileExists( $file2   ->path  ()->raw() );
	$this->assertFalse     ( $file2   ->isDir ()        );
	$this->assertFalse     ( $file2   ->isLink()        );


	$dir->rm();

	$this->assertFileNotExists( $dir->path()->raw() );
}



}
