<?php

namespace Golem\iFace;



/**
 * Describes a logger instance.
 *
 * The message MUST be a string or object implementing __toString().
 *
 */
interface Logger
{
	/**
	 * OFF indicates that no messages should be logged.
	 * This level is initialized to PHP_INT_MAX.
	 */
	const OFF = PHP_INT_MAX;


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
	 * ALL indicates that all messages should be logged.
	 * This level is initialized to 0.
	 */
	const ALL         =    0;



	/**
	 * Seal the current object so it's options cannot be changed anymore.
	 *
	 * Since this is a security library, we want clients to be sure that certain settings don't change
	 * anymore. It's best practice to define your security configuration in one place and then seal
	 * objects so they won't change anymore by php code included later.
	 *
	 * Note: Currently (php 5.6) there is a reflection module in PHP which allows code to write to
	 *       private properties on objects from the outside. One possibility to solve this is to
	 *       disable the "ReflectionClass" in php.ini.
	 *
	 *       In order for this to be useful you also have to store your php code and configuration
	 *       in a place where the php or webserver user does not have write privileges.
	 *
	 * @return \Golem\iFace\Logger $this.
	 *
	 * @api
	 *
	 */
	public function seal();



	/**
	 * Tells you whether the current object is sealed.
	 *
	 * @return bool Whether the object is sealed.
	 *
	 * @api
	 *
	 */
	public function sealed();



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
	 * Get the name of this logger.
	 *
	 * @return string The name.
	 *
	 * @api
	 *
	 */
	public function name();



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
	public function log( $level, $message, array $context = [] );



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
	function exception( $exception, array $context = [] );
}


