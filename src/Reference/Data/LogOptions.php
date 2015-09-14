<?php

/**
 * This is the Options object for the Log4php class.
 *
 */
namespace Golem\Reference\Data;

require __DIR__ . '/../../../lib/log4php/src/main/php/LoggerLevel.php';

use

	  Golem\iFace\Logger as iLogger

	, \LoggerLevel
	, \Exception
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

		$defaults = $golem->options()[ 'logger' ];

		parent::__construct( $options, $defaults );
	}



	public
	function name()
	{
		return $this[ 'prefix' ] . '.' . $this[ 'name' ];
	}



	public
	function additivity( $new = null )
	{
		if( $new !== null )

			$this[ 'additivity' ] = (bool) $new;


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
		// Setter part
		//
		if( $value !== null )
		{
			if( $this->sealed() )

				throw new Exception( "Cannot changes sealed options object." );


			else

				$this[ 'level' ] = $value;
		}


		// Getter part
		//
		$value = $this[ 'level' ];

		// Always return as constant, not string
		//
		if( is_string( $value ) )

			$value = self::string2level( $value );


		return $value;
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
	function level2log4php( $level )
	{
		if( is_string( $level ) )

			$level = self::string2level( $level );


		if( !is_int( $level ) )

			throw new Exception( "Invalid logging level Value was: ". print_r( $level, true ) . "." );


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


			default:	throw new Exception( "Invalid logging level Value was: ". print_r( $level, true ) . "." );
		}

	}


	/**
	 * Converts a logging level to a string.
	 *
	 * @param int $level The logging level to convert.
	 *
	 * @throws Exception when the supplied parameter is not an integer.
	 * @throws Exception when the supplied level doesn't match a level currently defined.
	 *
	 * @return string The logging Level as a string.
	 *
	 * @api
	 *
	 */
	public
	static
	function level2string( $level )
	{
		if( !is_int( $level ) )

			throw new Exception( "Invalid logging level Value was: ". print_r( $level, true ) . ". Should be an integer." );


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


			default:	throw new Exception( "Invalid logging level Value was: ". print_r( $level, true ) . "." );
		}

	}



	/**
	 * Converts a logging level from a string to a constant of \Golem\iFace\Logger.
	 *
	 * @param string $level The logging level to convert.
	 *
	 * @throws Exception when the supplied parameter is not a string.
	 * @throws Exception when the supplied level doesn't match a level currently defined.
	 *
	 * @return string The logging Level as a string.
	 *
	 * @api
	 *
	 */
	public
	static
	function string2level( $level )
	{
		if( !is_string( $level ) )

			throw new Exception( "Invalid logging level Value was: ". print_r( $level, true ) . ". Should be a string." );


		switch( $level )
		{
			case 'ALL'        : return iLogger::ALL      ;
			case 'DEBUG'      : return iLogger::DEBUG    ;
			case 'INFO'       : return iLogger::INFO     ;
			case 'NOTICE'     : return iLogger::NOTICE   ;
			case 'WARNING'    : return iLogger::WARNING  ;
			case 'ERROR'      : return iLogger::ERROR    ;
			case 'CRITICAL'   : return iLogger::CRITICAL ;
			case 'ALERT'      : return iLogger::ALERT    ;
			case 'EMERGENCY'  : return iLogger::EMERGENCY;
			case 'OFF'        : return iLogger::OFF      ;


			default:	throw new Exception( "Invalid logging level Value was: ". print_r( $level, true ) . "." );
		}

	}
}
