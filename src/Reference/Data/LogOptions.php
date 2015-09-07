<?php

/**
 * This is the Options object for the Log4php class.
 *
 * The data for the Options object should ideally come from
 * a place that is outside the document root, is readable but not
 * writable by the web server process.
 *
 */
namespace Golem\Reference\Data;

require __DIR__ . '/../../../lib/log4php/src/main/php/LoggerLevel.php';

use

	  Golem\iFace\Logger as iLogger

	, \LoggerLevel
;


class      LogOptions
extends    Options
implements \Golem\iFace\Data\LogOptions
{
	private $golem;

	public
	function __construct( $golem, $options = [] )
	{
		$this->golem = $golem;

		$defaults = array_merge_recursive( $golem->options()[ 'log' ], $golem->options()[ 'log4php' ] );

		parent::__construct( $options, $defaults );
	}



	public
	function name()
	{
		return $this[ 'prefix' ] . '.' . $this[ 'name' ];
	}



	public
	function additivity()
	{
		return $this[ 'additivity' ];
	}



	/**
	 * Dynamically get/set the logging severity level. All events of this level and
	 * higher will be logged from this point forward. All events
	 * below this level will be discarded.
	 *
	 * @param int $value (optional) The level to set the logging level to.
	 *
	 * @return int The (new) level.
	 */
	public
	function level( $value = null )
	{
		if( $value !== null )
		{
			if( $this->sealed )

				throw new Exception( "Cannot changes sealed options object." );


			$this->parsed[ 'level' ] = $value;
		}

		return $this[ 'level' ];
	}



	/**
	 * Converts a logging level from Golem to the log4php equivalent.
	 *
	 * Converts the Golem logging level (a number) or level defined in the Golem
	 * properties file (a string) into the levels used by Apache's log4php. Note
	 * that log4php does not define all of the levels we use. Closest equivalents
	 * will be used.
	 *
	 * @param mixed $level The logging level to convert.
	 *
	 * @throws Exception if the supplied level doesn't match a level currently
	 *                   defined.
	 *
	 * @return int The log4php logging Level equivalent.
	 */
	public
	static
	function level2Log4php( $level )
	{
		if( is_string( $level )  &&  defined( 'iLogger::' . $level ) )

			$level = constant( 'iLogger::' . $level );


		if( !is_int( $level ) )

			throw new Exception( "Invalid logging level Value was: {$level}" );


		switch( $level )
		{
			case iLogger::ALL       : /* Same as DEBUG */
			case iLogger::DEBUG     : return LoggerLevel::getLevelDebug();
			case iLogger::INFO      : /* Same as NOTICE */
			case iLogger::NOTICE    : return LoggerLevel::getLevelInfo ();
			case iLogger::WARNING   : return LoggerLevel::getLevelWarn ();
			case iLogger::ERROR     : return LoggerLevel::getLevelError();
			case iLogger::CRITICAL  : /* Same as EMERGENCY */
			case iLogger::ALERT     : /* Same as EMERGENCY */
			case iLogger::EMERGENCY : return LoggerLevel::getLevelFatal();
			case iLogger::OFF       : return LoggerLevel::getLevelOff  ();


			default:	throw new Exception( "Invalid logging level Value was: {$level}" );
		}

	}


	/**
	 * Converts a logging level to a string.
	 *
	 * Converts the Golem logging level (a number) or level defined in the Golem
	 * properties file (a string) into the levels used by Apache's log4php. Note
	 * that log4php does not define all of the levels we use. Closest equivalents
	 * will be used.
	 *
	 * @param int $level The logging level to convert.
	 *
	 * @throws Exception if the supplied level doesn't match a level currently
	 *                   defined.
	 *
	 * @return string The logging Level as a string.
	 */
	public
	static
	function level2string( $level )
	{
		if( !is_int( $level ) )

			throw new Exception( "Invalid logging level Value was: {$level}. Should be an integer." );


		switch( $level )
		{
			case iLogger::ALL       : return 'ALL'       ;
			case iLogger::DEBUG     : return 'DEBUG'     ;
			case iLogger::INFO      : return 'INFO'      ;
			case iLogger::NOTICE    : return 'NOTICE'    ;
			case iLogger::WARNING   : return 'WARNING'   ;
			case iLogger::ERROR     : return 'ERROR'     ;
			case iLogger::CRITICAL  : return 'CRITICAL'  ;
			case iLogger::ALERT     : return 'ALERT'     ;
			case iLogger::EMERGENCY : return 'EMERGENCY' ;
			case iLogger::OFF       : return 'OFF'       ;


			default:	throw new Exception( "Invalid logging level Value was: {$level}" );
		}

	}
}
