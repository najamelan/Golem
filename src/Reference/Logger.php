<?php

namespace Golem\Reference;


use

	  Golem\iFace\Logger as iLogger

	, Golem\Golem

	, Golem\Reference\Traits\Seal
	, Golem\Reference\Traits\HasOptions

	, \Exception
	, \InvalidArgumentException
;



class      Logger
implements iLogger
{
	use Seal, HasOptions;


	public
	function __construct( Golem $golem, array $options = [] )
	{
		$this->setupOptions( $golem->options()[ 'Logger' ], $options );
	}


	/**
	 * Throw an exception depending on the configuration, which can specify 'nothrow' to disable
	 * throwing exceptions (In which case it will still be logged).
	 *
	 * @param \Exception|string $exception The exception to throw. If a string message is passed,
	 *                                     a standard Exception object will be created.
	 *
	 * @param array             $context   Context information about the event.
	 *
	 * @return $this
	 *
	 * @api
	 *
	 */
	public
	function exception( $exception, array $context = [] )
	{
		$this->log( iLogger::ERROR, $exception, $context );


		// throw if appropriate
		//
		if( $this->throwingOn() )
		{
			if( ! $exception instanceof Exception )

				$exception = new Exception( $exception );


			throw $exception;
		}


		return $this;
	}



	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level   The loglevel for this event (NOTICE, WARNING, ERROR)
	 * @param string $message
	 * @param array  $context
	 *
	 * @return \Golem\iFace\Logger $this
	 *
	 * @api
	 *
	 */
	public
	function log( $level, $message, array $context = [] )
	{
		if( $level < $this->level()  ||  !$this->options[ 'loggingOn' ] )

			return;


		$message = $this->format( $message, $level );

		foreach( (array) $this->options[ 'logfile' ] as $output )
		{
			switch( $output )
			{
				case 'echo':

					echo $message, "\n";
					break;


				case 'phplog':

					$output = ini_get( 'error_log' );

					// fallthrough


				// Try to parse it as a filename
				//
				default:

					// Create the target folder if needed
					//
					if( ! is_dir( dirname( $output ) ) )

						if( mkdir( dirname( $output ), 0755, true ) === false )

							$this->exception( "Failed creating target directory [$dir] for logfile [$output]." );


					if( file_put_contents( $output, $message, FILE_APPEND | LOCK_EX ) === false )

						$this->exception( "Failed writing to logfile [$output]." );
			}
		}


		return $this;
	}



	/**
	 * Decorates the log message.
	 *
	 * @param mixed $message The original message to log (must implement __toString()).
	 *
	 * @param int   $level   The log level of this event in order to be able to include
	 *                       it in the formatted string
	 *
	 * @internal
	 *
	 */
	private
	function format( $message, $level )
	{
		$level = str_pad( $this->level2string( $level ), 7 );


		return

			date( 'Y-m-d H:i:s' ) . " [{$this->name()}] SECURITY:{$level} | $message\n---\n";
	}



	public
	function throwingOn( $value = null )
	{
		if( $value !== null  &&  ! $this->sealed() )

			$this->options[ 'throwingOn' ] = (bool) $value;


		return (bool) $this->options[ 'throwingOn' ];
	}



	public
	function loggingOn( $value = null )
	{
		if( $value !== null  &&  ! $this->sealed() )

			$this->options[ 'loggingOn' ] = (bool) $value;


		return (bool) $this->options[ 'loggingOn' ];
	}



	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public
	function error( $message, array $context = [] )
	{
		$this->log( iLogger::ERROR, $message, $context );
	}



	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if error level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public
	function errorOn()
	{
		return $this->level() <= iLogger::ERROR;
	}



	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public
	function warning( $message, array $context = [] )
	{
		$this->log( iLogger::WARNING, $message, $context );
	}



	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if warning level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public
	function warningOn()
	{
		return $this->level() <= iLogger::WARNING;
	}



	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public
	function notice( $message, array $context = [] )
	{
		$this->log( iLogger::NOTICE, $message, $context );
	}



	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if notice level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public
	function noticeOn()
	{
		return $this->level() <= iLogger::NOTICE;
	}



	/**
	 * Get the name of this logger.
	 *
	 * @return string The name.
	 *
	 * @api
	 *
	 */
	public
	function name()
	{
		return $this->options[ 'name' ];
	}



	/**
	 * Dynamically get/set the logging severity level.
	 *
	 * All events of this level and higher will be logged from this point forward. All events
	 * below this level will be discarded.
	 *
	 * See the constants in \Golem\iFace\Logger for explanation on the levels.
	 *
	 * @param int $value (optional) The level to set the logging level to.
	 *
	 * @return int The (new) level.
	 *
	 * @api
	 *
	 */
	public
	function level( $value = null )
	{
		// Setter part
		//
		if( $value !== null )
		{
			if( $this->sealed() )

				$this->exception( "Cannot changes sealed options object." );


			else

				$this->options[ 'level' ] = $this->userset[ 'level' ] = $value;

		}


		// Getter part
		//
		$value = $this->options[ 'level' ];

		// Always return as constant, not string
		//
		if( is_string( $value ) )

			$value = $this->string2level( $value );


		return $value;
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
	function string2level( $level )
	{

		switch( $level )
		{
			case 'ALL'        : return iLogger::ALL      ;
			case 'NOTICE'     : return iLogger::NOTICE   ;
			case 'WARNING'    : return iLogger::WARNING  ;
			case 'ERROR'      : return iLogger::ERROR    ;
			case 'OFF'        : return iLogger::OFF      ;

			case true : // fallthrough (switch does loose comparison)
			default   :

				$this->exception( new InvalidArgumentException( "Invalid logging level Value was: ". print_r( $level, true ) . "." ) );
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
	function level2string( $level )
	{
		switch( $level )
		{
			case iLogger::ALL       : return 'ALL'       ;
			case iLogger::NOTICE    : return 'NOTICE'    ;
			case iLogger::WARNING   : return 'WARNING'   ;
			case iLogger::ERROR     : return 'ERROR'     ;
			case iLogger::OFF       : return 'OFF'       ;


			default:

				$this->exception( new InvalidArgumentException( "Invalid logging level Value was: ". print_r( $level, true ) . "." ) );
		}

	}
}
