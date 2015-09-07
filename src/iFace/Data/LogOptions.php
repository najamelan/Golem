<?php



namespace Golem\iFace\Data;


/**
 * Options specific to logging.
 *
 * Every logger will have an options object that implements this interface.
 *
 */
interface LogOptions
extends   Options
{
	/**
	 * Getter for the full name of the logger (eg. Golem.General)
	 *
	 * @return string The full name.
	 *
	 * @api
	 *
	 */
	public function name();



	/**
	 * Getter for the loglevel for the logger.
	 *
	 * @see \Golem\iFace\Logger iFace\Logger for the log levels
	 *
	 * @return int The loglevel value as specified in \Golem\iFace\Logger.
	 *
	 *
	 * @api
	 *
	 */
	public function level();
}
