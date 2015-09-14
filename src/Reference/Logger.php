<?php

namespace Golem\Reference;


use

	  Golem\iFace\Logger as iLogger

	, Golem\Reference\Data\LogOptions

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions

	, \Logger as AppacheLogger
	, \Exception
	, \SplObserver
	, \SplSubject
;


// Include apache-log4php
//
require_once __DIR__ . '/../../lib/log4php/src/main/php/Logger.php';



class      Logger
implements iLogger, \SplObserver
{
	use Seal, HasOptions;


	protected $activeAppenders = [];



	public function __construct( LogOptions $options )
	{
		$this->options = $options;


		$this->backend = AppacheLogger::getLogger( $this->name() );


		// Additivity allows the logger to inherit appenders from the root logger
		// or others in the hierarchy. Since this can be a security risk, we disable it.
		//
		$this->backend->setAdditivity( $this->options->additivity() );


		// Add all appenders from the configuration to the backend
		//
		$this->updateAppenders( $options );


		// Observe changes to the options in order to update the backend
		//
		$this->options->attach( $this );
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
	 * higher will be logged from this point forward. All events below this level will be discarded.
	 *
	 * @param int|string $value (optional) The level to set the logging level to.
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
			  LogOptions::level2log4php( $level )
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



	protected
	function appender( $appConfig )
	{
		$appenders     = $this->options[ 'log4php' ][ 'appenders' ];

		$backendConfig = $appenders[ $appConfig[ 'name' ] ];
		$app           = new $backendConfig[ 'class' ];


		$layout        = new $backendConfig[ 'layout' ];
		$layout->setConversionPattern( $appConfig[ 'conversionPattern' ] );
		$layout->activateOptions();

		$app->setLayout( $layout );


		switch( $appConfig[ 'name' ] )
		{
			case 'defaultEcho': break;


			case 'defaultFile':

				$app->setAppend( true    );

				$app->setFile  ( $appConfig[ 'logfile' ] );

				break;


			default:

				throw new Exception( "Unknown appender with name: " . $appConfig[ 'name' ] );

		}


		$app->activateOptions();

		// TODO: we should call $app->close() to release file handles either when it gets
		// detached, or in the destructor of this logger...

		return $app;
	}



	/**
	 * Since this class uses a backend (log4php), when settings get overridden, we need to forward
	 * certain settings to the backend.
	 */
	public
	function override( $options )
	{
		$this->options->override( $options );


		$this->backend->setAdditivity( $this->options[ 'additivity' ] );


		$this->updateAppenders( $this->options );
	}



	/**
	 * Update appenders to match new options.
	 *
	 */
	protected
	function updateAppenders( $options )
	{
		if( !isset( $options[ 'appenders' ] ) )

			return;


		$this->backend->removeAllAppenders();

		$appenders = $options[ 'appenders' ];



		// Create all appenders and add them to the backend
		//
		foreach( $appenders as $appConfig )

			$this->backend->addAppender( $this->appender( $appConfig ) );
	}


	public
	function update( SplSubject $subject, $eventName = null )
	{

		if( $eventName === 'additivity changed' )

			$this->backend->setAdditivity( $subject->additivity() );
	}

}
