<?php

namespace Golem\Reference;


use

	  Golem\iFace\Logger as iLogger

	, Golem\Reference\Data\LogOptions

	, Golem\Traits\Seal

	, \Logger as AppacheLogger
;


// Include apache-log4php
//
require_once __DIR__ . '/../../lib/log4php/src/main/php/Logger.php';



class      Logger
implements iLogger
{
	use Seal;


	private $options;


	public function __construct( LogOptions $options )
	{
		$this->options = $options;

		// Golem does not allow getting unnamed loggers, to avoid polluting the rootLogger
		//
		$this->backend = AppacheLogger::getLogger( $this->name() );

		// Additivity allows the logger to inherit appenders from the root logger
		// or others in the hierarchy. Since this can be a security risk, we disable it.
		//
		$this->backend->setAdditivity( $this->options->additivity() );


		foreach( (array) $this->options[ 'output' ] as $output )

			$this->backend->addAppender( $this->appender( $output ) );
	}


	/**
	 * Get the name of this logger.
	 *
	 * @return string The name.
	 *
	 */
	public
	function name()
	{
		return $this->options->name();
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
		return $this->options->level( $value );
	}




	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public
	function emergency( $message, array $context = [] )
	{
		$this->log( iLogger::EMERGENCY, $message, $context );
	}


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if emergency level messages will be output to the log.
	 */
	public
	function emergencyOn()
	{
		return $this->options->level() <= iLogger::EMERGENCY;
	}


	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public
	function alert( $message, array $context = [] )
	{
		$this->log( iLogger::ALERT, $message, $context );
	}


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if alert level messages will be output to the log.
	 */
	public
	function alertOn()
	{
		return $this->options->level() <= iLogger::ALERT;
	}


	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unhandled exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public
	function critical( $message, array $context = [] )
	{
		$this->log( iLogger::CRITICAL, $message, $context );
	}


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if critical level messages will be output to the log.
	 */
	public
	function criticalOn()
	{
		return $this->options->level() <= iLogger::CRITICAL;
	}


	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
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
	 */
	public
	function errorOn()
	{
		return $this->options->level() <= iLogger::ERROR;
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
	 */
	public
	function warningOn()
	{
		return $this->options->level() <= iLogger::WARNING;
	}


	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
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
	 */
	public
	function noticeOn()
	{
		return $this->options->level() <= iLogger::NOTICE;
	}


	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public
	function info( $message, array $context = [] )
	{
		$this->log( iLogger::INFO, $message, $context );
	}


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if info level messages will be output to the log.
	 */
	public
	function infoOn()
	{
		return $this->options->level() <= iLogger::INFO;
	}


	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public
	function debug( $message, array $context = [] )
	{
		$this->log( iLogger::DEBUG, $message, $context );
	}


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if debug level messages will be output to the log.
	 */
	public
	function debugOn()
	{
		return $this->options->level() <= iLogger::DEBUG;
	}


	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public
	function log( $level, $message, array $context = [] )
	{
		$message = $this->options[ 'msgPrefix' ] . $message;


		if( ! isset( $context[ 'type' ] ) )

			$context[ 'type' ] = $this->options[ 'type' ];


		$context[ 'level' ] = $this->options->level2string( $level );


		$this->backend->log
		(
			  LogOptions::level2Log4php( $level )
			, $this->messageTemplate( $message, $context )
			, isset( $context[ 'exception' ] )  ?  $context[ 'exception' ] : null
		);
	}



	protected
	function messageTemplate( $message, $context )
	{
		foreach( $context as $key => $value )
		{
			error_log( print_r( $key, true ) );
			$message = preg_replace( '/\{' . preg_quote( $key ) . '\}/u', $value, $message );
			error_log( print_r( $message, true ) );
		}


		return $message;
	}



	/**
	 * Create a layout from given options.
	 *
	 */
	protected
	function layout( $options )
	{
		$layout = new $options[ 'class' ];
		$layout->setConversionPattern( $options[ 'conversionPattern' ] );
		$layout->activateOptions();
		return $layout;
	}



	protected
	function appender( $type )
	{
		switch( $type )
		{
			case 'echo':

				$appConfig = $this->options[ 'availableAppenders' ][ $type ];

				$app    = new $appConfig[ 'class' ];
				$layout = $this->layout( $appConfig[ 'layout' ] );

				$app->setLayout( $layout );
				$app->activateOptions();

				return $app;


			case 'file':


			default:

				throw new Exception( "Unknown output type: " . $type );

		}
	}

}
