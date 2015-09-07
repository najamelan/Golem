<?php

namespace Golem\iFace;



/**
 * Describes a logger instance.
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data. The assumptions that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception" and
 * placeholders in the context array must have a __toString().
 *
 * The Logger interface defines 4 event types: SECURITY, USABILITY,
 * PERFORMANCE and FUNCTIONALITY.  The reference implementation of Golem
 * submits events for logging of the type SECURITY ( exlusively ). These are
 * to be passed to the log functions in the context array as 'type'.
 *
 * The Logger interface defines 8 logging levels: EMERGENCY, ALERT, CRITICAL,
 * ERROR, WARNING, NOTICE, INFO, DEBUG. It also supports ALL, an alias of DEBUG
 * which logs all events, and OFF, which disables all logging. Your
 * implementation can extend or change this list if desired.
 *
 * This interface is inspired on the PSR-3 standard:
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * Github for the full interface specification.
 *
 */
interface Logger
{
	/**
	 * The SECURITY type of log event.
	 */
	const SECURITY      = 'SECURITY';

	/**
	 * The USABILITY type of log event.
	 */
	const USABILITY     = 'USABILITY';

	/**
	 * The PERFORMANCE type of log event.
	 */
	const PERFORMANCE   = 'PERFORMANCE';

	/**
	 * The FUNCTIONALITY type of log event. This is the type of event that
	 * non-security focused loggers typically log. If you are going to log your
	 * existing non-security events in the same log with your security events,
	 * you probably want to use this type of log event.
	 */
	const FUNCTIONALITY = 'FUNCTIONALITY';


	/**
	 * OFF indicates that no messages should be logged.
	 * This level is initialized to PHP_INT_MAX.
	 */
	const OFF = PHP_INT_MAX;

	/**
	 * EMERGENCY indicates that only EMERGENCY messages should be logged.
	 * This level is initialized to 1600.
	 */
	const EMERGENCY   = 1600;

	/**
	 * ALERT indicates that only ALERT messages should be logged.
	 * This level is initialized to 1400.
	 */
	const ALERT       = 1400;

	/**
	 * CRITICAL indicates that only CRITICAL messages should be logged.
	 * This level is initialized to 1200.
	 */
	const CRITICAL    = 1200;

	/**
	 * ERROR indicates that ERROR messages and above should be logged.
	 * This level is initialized to 1000.
	 */
	const ERROR       = 1000;

	/**
	 * WARNING indicates that WARNING messages and above should be logged.
	 * This level is initialized to 800.
	 */
	const WARNING     =  800;

	/**
	 * NOTICE indicates that NOTICE messages and above should be logged.
	 * This level is initialized to 600.
	 */
	const NOTICE      =  600;

	/**
	 * INFO indicates that INFO messages and above should be logged.
	 * This level is initialized to 400.
	 */
	const INFO        =  400;

	/**
	 * DEBUG indicates that DEBUG messages and above should be logged.
	 * This level is initialized to 200.
	 */
	const DEBUG       =  200;

	/**
	 * ALL indicates that all messages should be logged.
	 * This level is initialized to 0.
	 */
	const ALL         =    0;


	/**
	 * Seal the current options object so it cannot be changed anymore.
	 *
	 * Since this is a security library, we want clients to be sure that certain settings don't change
	 * anymore. It's best practice to define your security configuration in one place and then seal
	 * objects so they won't change anymore by php code included later.
	 *
	 * @return Logger $this.
	 *
	 * @api
	 *
	 */
	public function seal();


	/**
	 * Get the name of this logger.
	 *
	 * @return string The name.
	 *
	 * @api
	 *
	 */
	public function name();


	/**
	 * Dynamically get/set the logging severity level.
	 *
	 * All events of this level and higher will be logged from this point forward. All events
	 * below this level will be discarded.
	 *
	 * See the constants in this class for explanation on the levels.
	 *
	 * @param int $value (optional) The level to set the logging level to.
	 *
	 * @return int The (new) level.
	 *
	 * @api
	 *
	 */
	public function level( $value = null );



	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public function emergency( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if emergency level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function emergencyOn();


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
	 *
	 * @api
	 *
	 */
	public function alert( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if alert level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function alertOn();


	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unhandled exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public function critical( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if critical level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function criticalOn();


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
	public function error( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if error level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function errorOn();


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
	public function warning( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if warning level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function warningOn();


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
	public function notice( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if notice level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function noticeOn();


	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public function info( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if info level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function infoOn();


	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 *
	 * @api
	 *
	 */
	public function debug( $message, array $context = [] );


	/**
	 * Allows the caller to determine if messages logged at this level will be
	 * discarded, to avoid performing expensive processing.
	 *
	 * @return bool TRUE if debug level messages will be output to the log.
	 *
	 * @api
	 *
	 */
	public function debugOn();


	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function log( $level, $message, array $context = [] );

}


