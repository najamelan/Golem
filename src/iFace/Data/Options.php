<?php


namespace Golem\iFace\Data;
use \ArrayAccess, mixed;



/**
 * Common functionality of all option objects in Golem.
 *
 * The data for the option objects should ideally come from
 * a place that is outside the document root, is readable but not
 * writable by the web server process.
 *
 * @package Configuration
 *
 */
interface Options
{

	/**
	 * Constructor.
	 *
	 * You should not call this directly (it's abstract anyways),
	 * usually you want to instantiate a subclass. Golem also provides
	 * factory method on the main classes such as Golem\Golem.
	 *
	 * @param  array|Golem\iFace\Data\Option options
	 *         The options to store in this object.
	 *         In practice an array will be a hierarchy of associative arrays.
	 *
	 * @param  array|Golem\iFace\Data\Option defaults
	 *         The defaults for this object. This shall be passed down from subclasses so it's later
	 *         possible to consult what the defaults where, or to make comparison between defaults and
	 *         active configuration.
	 *
	 * @throws Exception This calls the override method which throws.
	 * @see    Golem\iFace\Data\Options::override() For Exceptions.
	 *
	 * @internal
	 *
	 */
	public function __construct( $options, $defaults = [] );



	/**
	 * Override options stored in the options object.
	 *
	 * @param  array|Option $options The new options.
	 *
	 * @throws \Exception When trying to change a sealed object.
	 * @throws \Exception If parameter is not of the right type.
	 *
	 * @return Options $this.
	 *
	 * @api
	 *
	 */
	public function override( $options );



	/**
	 * Get a readonly array representation of the currently active options.
	 *
	 * @return array The parsed options.
	 *
	 * @api
	 *
	 */
	public function toArray();



	/**
	 * Get the set of values that where the defaults for this class.
	 *
	 * @return array The default options.
	 *
	 * @api
	 *
	 */
	public function defaults();



	/**
	 * Get those values that have been overridden or set by the client.
	 *
	 * @return array The user set options.
	 *
	 * @api
	 *
	 */
	public function userset();



	/**
	 * Seal the current options object so it cannot be changed anymore.
	 *
	 * Since this is a security library, we want clients to be sure that certain settings don't change
	 * anymore. It's best practice to define your security configuration in one place and then seal
	 * objects so they won't change anymore by php code included later.
	 *
	 * Unfortunately this will only really be solid if you recompile PHP with --disable-reflection. See the
	 * unit tests for Trait\Seal in order to understand.
	 *
	 * @return Options $this.
	 *
	 * @api
	 *
	 */
	public function seal();



	/**
	 * Tells you whether the current object or it's options are sealed.
	 *
	 * @return bool Whether the object is sealed.
	 *
	 * @api
	 *
	 */
	public function sealed();
}
