<?php
namespace Golem\Test;

use

	  Golem\iFace\Logger          as iLogger

	, Golem\Golem
	, Golem\Reference\Logger
	, Golem\Reference\Util

	, \Exception

;


class LoggerTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }



	public
	function	testConstructor()
	{
		// Check different loggers have independent configuration and that loggers with the same
		// name share configuration.
		//
		$logger = self::$golem->logger( 'testConstructor' );
		$logger->level( iLogger::ERROR );

		$new  = self::$golem->logger( 'testLogger', [ 'level' => iLogger::WARNING ] );
		$same = self::$golem->logger( 'testLogger'                                  );

		$this->assertEquals( $new->options(), $same->options() );

		$this->assertEquals( $new   ->level(), iLogger::WARNING );
		$this->assertEquals( $logger->level(), iLogger::ERROR   );
	}



	public
	function	testLevel()
	{
		$logger = self::$golem->logger( __CLASS__ );


		// Check that default level is ALL
		//
		$this->assertEquals( iLogger::ALL, $logger->level() );


		// Test reading from a sealed object
		//
		$logger->seal();
		$this->assertEquals( iLogger::ALL, $logger->level() );


		// Test changing values with string
		//
		$logger = self::$golem->logger( 'levelTest' );
		$this->assertEquals( iLogger::NOTICE, $logger->level( 'NOTICE' ) );


		// Check setting level to OFF
		//
		$logger->level( iLogger::OFF );

		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );
		$this->assertFalse( $logger->errorOn    () );


		// Check setting level to ERROR
		//
		$logger->level( iLogger::ERROR );

		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );

		$this->assertTrue ( $logger->errorOn    () );


		// Check setting level to WARNING
		//
		$logger->level( iLogger::WARNING );

		$this->assertFalse( $logger->noticeOn   () );

		$this->assertTrue ( $logger->warningOn  () );
		$this->assertTrue ( $logger->errorOn    () );


		// Check setting level to NOTICE
		//
		$logger->level( iLogger::NOTICE );

		$this->assertTrue ( $logger->noticeOn   () );
		$this->assertTrue ( $logger->warningOn  () );
		$this->assertTrue ( $logger->errorOn    () );


		// Check setting level to ALL
		//
		$logger->level( iLogger::ALL );

		$this->assertTrue ( $logger->noticeOn   () );
		$this->assertTrue ( $logger->warningOn  () );
		$this->assertTrue ( $logger->errorOn    () );
	}



	public
	function	testName()
	{
		// Test defaults
		//
		$logger = self::$golem->logger();
		$this->assertEquals( 'Golem', $logger->name() );


		// Make sure name of an existing logger cannot be changed
		//
		$logger = self::$golem->logger();
		$this->assertEquals( 'Golem', $logger->name( 'Olé' ) );

	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevelSealed()
	{
		// Try to change a sealed object
		//
		$logger = self::$golem->logger( 'logSealTest' );
		$logger->seal();
		$logger->level( iLogger::NOTICE );
	}


	public
	function	testString2Level()
	{
		// Verify mappings
		//
		$this->assertEquals( self::$golem->logger()->string2level( 'ALL'       ), iLogger::ALL       );
		$this->assertEquals( self::$golem->logger()->string2level( 'NOTICE'    ), iLogger::NOTICE    );
		$this->assertEquals( self::$golem->logger()->string2level( 'WARNING'   ), iLogger::WARNING   );
		$this->assertEquals( self::$golem->logger()->string2level( 'ERROR'     ), iLogger::ERROR     );
		$this->assertEquals( self::$golem->logger()->string2level( 'OFF'       ), iLogger::OFF       );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testString2LevelInvalid()
	{
		// Send wrong parameter type.
		//
		self::$golem->logger()->string2level( 5 );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testString2LevelWrongLevel()
	{
		// Send wrong value.
		//
		self::$golem->logger()->string2level( 'Bazinga' );
	}


	public
	function	testLevel2String()
	{
		// Verify mappings
		//
		$this->assertEquals( self::$golem->logger()->level2string( iLogger::ALL     ), 'ALL'       );
		$this->assertEquals( self::$golem->logger()->level2string( iLogger::NOTICE  ), 'NOTICE'    );
		$this->assertEquals( self::$golem->logger()->level2string( iLogger::WARNING ), 'WARNING'   );
		$this->assertEquals( self::$golem->logger()->level2string( iLogger::ERROR   ), 'ERROR'     );
		$this->assertEquals( self::$golem->logger()->level2string( iLogger::OFF     ), 'OFF'       );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevel2StringInvalid()
	{
		// Send wrong parameter type.
		//
		self::$golem->logger()->level2string( '5' );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevel2StringWrongLevel()
	{
		// Send wrong value.
		//
		self::$golem->logger()->level2string( 987654 );
	}



	private
	function	logEcho( $logger, $method, $level, $test )
	{
		// Verify a logged string is present
		//
		$msg = self::$golem->randomizer()->randomBytes( 32, 'hex' );


		ob_start();

			call_user_func( [ $logger, $method ], $msg );

		$output = ob_get_clean();


		call_user_func( [ $this, $test ], $msg  , $output );
		call_user_func( [ $this, $test ], $level, $output );
	}



	public
	function	testLogEcho()
	{
		// Test basic logging
		//
		$logger = self::$golem->logger( 'echoTest' , [ 'logfile' => 'echo' ] );

		$this->logEcho( $logger, 'notice'  , 'NOTICE' , 'assertContains' );
		$this->logEcho( $logger, 'warning' , 'WARNING', 'assertContains' );
		$this->logEcho( $logger, 'error'   , 'ERROR'  , 'assertContains' );


		// Verify that loglevel is respected
		//
		$logger = self::$golem->logger( 'echoTestLevel' , [ 'logfile' => 'echo', 'level' => 'ERROR' ] );

		$this->logEcho( $logger, 'notice'  , 'NOTICE' , 'assertNotContains' );
		$this->logEcho( $logger, 'warning' , 'WARNING', 'assertNotContains' );

		$this->logEcho( $logger, 'error'   , 'ERROR'  , 'assertContains'    );


		$logger = self::$golem->logger( 'echoTestLevel2' , [ 'logfile' => 'echo', 'level' => 'OFF' ] );

		$this->logEcho( $logger, 'notice'  , 'NOTICE' , 'assertNotContains' );
		$this->logEcho( $logger, 'warning' , 'WARNING', 'assertNotContains' );
		$this->logEcho( $logger, 'error'   , 'ERROR'  , 'assertNotContains' );


		// Test exception without throwing
		//
		$logger = self::$golem->logger( 'echoTestExcept' , [ 'logfile' => 'echo', 'errorHandling' => 'nothrow' ] );

		$this->logEcho( $logger, 'exception', 'ERROR', 'assertContains'   );


		// Test exception without throwing
		//
		$logger = self::$golem->logger( 'echoTestExceptNone' , [ 'logfile' => 'echo', 'errorHandling' => 'none' ] );

		$this->logEcho( $logger, 'exception', 'ERROR', 'assertNotContains' );
	}



	private
	function	logFile( $logger, $file, $method, $level, $test )
	{
		// Verify a logged string is present
		//
		$msg = self::$golem->randomizer()->randomBytes( 32, 'hex' );


		if( file_exists( $file ) )

			file_put_contents( $file, '' );


		call_user_func( [ $logger, $method ], $msg );

		$output = file_get_contents( $file );


		call_user_func( [ $this, $test ], $msg  , $output );
		call_user_func( [ $this, $test ], $level, $output );
	}



	public
	function	testLogFile()
	{
		// Test basic logging
		//
		$file   = __DIR__ . '/../output/test.log';


		if( is_dir( dirname( $file ) ) )

			Util::delTree( dirname( $file ) );


		$logger = self::$golem->logger( 'fileTest' , [ 'logfile' => $file ] );

		$this->logFile( $logger, $file, 'notice'  , 'NOTICE' , 'assertContains' );
		$this->logFile( $logger, $file, 'warning' , 'WARNING', 'assertContains' );
		$this->logFile( $logger, $file, 'error'   , 'ERROR'  , 'assertContains' );


		// Test exception without throwing
		//
		$logger = self::$golem->logger( 'fileTestExcept' , [ 'logfile' => $file, 'errorHandling' => 'nothrow' ] );

		$this->logFile( $logger, $file, 'exception', 'ERROR', 'assertContains'   );


		// Test exception without throwing
		//
		$logger = self::$golem->logger( 'fileTestExceptNone' , [ 'logfile' => $file, 'errorHandling' => 'none' ] );

		$this->logFile( $logger, $file, 'exception', 'ERROR', 'assertNotContains' );


		if( is_dir( dirname( $file ) ) )

			Util::delTree( dirname( $file ) );
	}



	private
	function	logPHP( $logger, $file, $method, $level, $test )
	{
		// Verify a logged string is present
		//
		$msg = self::$golem->randomizer()->randomBytes( 32, 'hex' );


		if( file_exists( $file ) )

			file_put_contents( $file, '' );


		call_user_func( [ $logger, $method ], $msg );

		$output = file_get_contents( $file );


		call_user_func( [ $this, $test ], $msg  , $output );
		call_user_func( [ $this, $test ], $level, $output );
	}



	public
	function	testLogPHP()
	{
		// Test basic logging
		//
		$file   = ini_get( 'error_log' );

		$logger = self::$golem->logger( 'phpTest' , [ 'logfile' => 'phplog' ] );

		$this->logPHP( $logger, $file, 'notice'  , 'NOTICE' , 'assertContains' );
		$this->logPHP( $logger, $file, 'warning' , 'WARNING', 'assertContains' );
		$this->logPHP( $logger, $file, 'error'   , 'ERROR'  , 'assertContains' );


		// Test exception without throwing
		//
		$logger = self::$golem->logger( 'phpTestExcept' , [ 'logfile' => 'phplog', 'errorHandling' => 'nothrow' ] );

		$this->logPHP( $logger, $file, 'exception', 'ERROR', 'assertContains'   );


		// Test exception without throwing
		//
		$logger = self::$golem->logger( 'phpTestExceptNone' , [ 'logfile' => 'phplog', 'errorHandling' => 'none' ] );

		$this->logPHP( $logger, $file, 'exception', 'ERROR', 'assertNotContains' );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testException()
	{
		$logger = self::$golem->logger( 'testException' );

		$logger->exception( new Exception( 'Some exception' ) );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testStringException()
	{
		$logger = self::$golem->logger( 'testException' );

		$logger->exception( 'Some exception' );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testWrongErrorHandling()
	{
		$logger = self::$golem->logger( 'testExceptionParam', [ 'errorHandling' => 'mlkj' ] );

		$logger->exception( 'Some exception' );
	}
}
