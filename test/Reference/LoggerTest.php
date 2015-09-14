<?php
namespace Golem\Test;

use

	  Golem\iFace\Logger          as iLogger
	, Golem\iFace\Data\LogOptions as iLogOptions

	, Golem\Golem
	, Golem\Reference\Data\Options
	, Golem\Reference\Data\GolemOptions
	, Golem\Reference\Data\LogOptions

	, \stdClass
 	, \Logger as AppacheLogger

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
		$logger = self::$golem->logger( __CLASS__ );

		$new  = self::$golem->logger( [ 'level' => iLogger::WARNING, 'name' => 'testLogger' ] );
		$same = self::$golem->logger( [ 'name' => 'testLogger' ]                              );
		$logger->level( iLogger::ERROR );

		$this->assertEquals( $new->options()->toArray(), $same->options()->toArray() );

		$this->assertEquals( $new   ->level(), iLogger::WARNING );
		$this->assertEquals( $logger->level(), iLogger::ERROR   );
	}



	public
	function	testLevel()
	{
		$logger = self::$golem->logger( __CLASS__ );


		// Check setting level to OFF
		//
		$logger->level( iLogger::OFF );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );
		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );
		$this->assertFalse( $logger->errorOn    () );
		$this->assertFalse( $logger->criticalOn () );
		$this->assertFalse( $logger->alertOn    () );
		$this->assertFalse( $logger->emergencyOn() );


		// Check setting level to EMERGENCY
		//
		$logger->level( iLogger::EMERGENCY );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );
		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );
		$this->assertFalse( $logger->errorOn    () );
		$this->assertFalse( $logger->criticalOn () );
		$this->assertFalse( $logger->alertOn    () );

		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to ALERT
		//
		$logger->level( iLogger::ALERT );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );
		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );
		$this->assertFalse( $logger->errorOn    () );
		$this->assertFalse( $logger->criticalOn () );

		$this->assertTrue ( $logger->alertOn    () );
		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to CRITICAL
		//
		$logger->level( iLogger::CRITICAL );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );
		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );
		$this->assertFalse( $logger->errorOn    () );

		$this->assertTrue ( $logger->criticalOn () );
		$this->assertTrue ( $logger->alertOn    () );
		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to ERROR
		//
		$logger->level( iLogger::ERROR );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );
		$this->assertFalse( $logger->noticeOn   () );
		$this->assertFalse( $logger->warningOn  () );

		$this->assertTrue ( $logger->errorOn    () );
		$this->assertTrue ( $logger->criticalOn () );
		$this->assertTrue ( $logger->alertOn    () );
		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to WARNING
		//
		$logger->level( iLogger::WARNING );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );
		$this->assertFalse( $logger->noticeOn   () );

		$this->assertTrue ( $logger->warningOn  () );
		$this->assertTrue ( $logger->errorOn    () );
		$this->assertTrue ( $logger->criticalOn () );
		$this->assertTrue ( $logger->alertOn    () );
		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to NOTICE
		//
		$logger->level( iLogger::NOTICE );

		$this->assertFalse( $logger->debugOn    () );
		$this->assertFalse( $logger->infoOn     () );

		$this->assertTrue ( $logger->noticeOn   () );
		$this->assertTrue ( $logger->warningOn  () );
		$this->assertTrue ( $logger->errorOn    () );
		$this->assertTrue ( $logger->criticalOn () );
		$this->assertTrue ( $logger->alertOn    () );
		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to INFO
		//
		$logger->level( iLogger::INFO );

		$this->assertFalse( $logger->debugOn    () );

		$this->assertTrue ( $logger->infoOn     () );
		$this->assertTrue ( $logger->noticeOn   () );
		$this->assertTrue ( $logger->warningOn  () );
		$this->assertTrue ( $logger->errorOn    () );
		$this->assertTrue ( $logger->criticalOn () );
		$this->assertTrue ( $logger->alertOn    () );
		$this->assertTrue ( $logger->emergencyOn() );


		// Check setting level to DEBUG
		//
		$logger->level( iLogger::DEBUG );

		$this->assertTrue( $logger->debugOn    () );
		$this->assertTrue( $logger->infoOn     () );
		$this->assertTrue( $logger->noticeOn   () );
		$this->assertTrue( $logger->warningOn  () );
		$this->assertTrue( $logger->errorOn    () );
		$this->assertTrue( $logger->criticalOn () );
		$this->assertTrue( $logger->alertOn    () );
		$this->assertTrue( $logger->emergencyOn() );
	}


	public
	function	testLogEcho()
	{
		$logger = self::$golem->logger
		([
			  'name'      => __CLASS__
			, 'appenders' => [ 'default' => [ 'name' => 'defaultEcho', 'conversionPattern' => '[%logger] %message' ] ]
		]);


		ob_start();

			$logger->info( 'This is an info message.' );

		$output = ob_get_clean();

		$this->assertEquals( $output, '[Golem.Golem\Test\LoggerTest] SECURITY:INFO - This is an info message.' );
	}
}
