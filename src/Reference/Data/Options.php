<?php


namespace Golem\Reference\Data;

use

	  \Golem\iFace\Data\Options as iOptions

	, \Golem\Traits\Seal
	, \Golem\Traits\ArrayAccess as ArrAccess
	, \Golem\Traits\Subject

	, \ArrayAccess
	, \Exception
	, \SplSubject

;


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
class        Options
implements   iOptions, SplSubject, ArrayAccess
{

	use Seal, ArrAccess, Subject;


	/** @var bool Whether the object is sealed.
	 */
	private   $sealed    = false ;

	protected $options   = []    ;
	protected $defaults  = []    ;
	protected $userset   = []    ;



	/**
	 * Constructor.
	 *
	 * You should not call this directly, usually you want to instantiate a subclass.
	 * Golem also provides factory methods on the main classes such as Golem\Golem.
	 *
	 * @param  array|\Golem\iFace\Data\Option $options
	 *         The options to store in this object. Can either be a Golem\iFace\Data\Option or an array.
	 *         In practice an array will be a hierarchy of associative arrays.
	 *
	 * @param  array|\Golem\iFace\Data\Option $defaults
	 *         The defaults for this object. This shall be passed down from subclasses so it's later
	 *         possible to consult what the defaults where, or to make comparison between defaults and
	 *         active configuration.
	 *
	 * @throws \Exception This calls the override method which throws.
	 * @see    \Golem\iFace\Data\Options::override() For Exceptions.
	 *
	 * @return $this.
	 *
	 * @internal
	 *
	 */
	public function __construct( $options = [], $defaults = [] )
	{
		// This will set $this->options and $this->userset only if it's an array
		//
		$this->override( $defaults );


		// If it's an array, override() will not consider it as defaults,
		// so make up for that. If it was an options object override will
		// correctly keep user userset and defaults separate.
		//
		if( is_array( $defaults ) )
		{
			// Copy into defaults
			//
			$this->defaults = $this->options;

			// We don't want to have the defaults in here, so unset
			//
			$this->userset = [];
		}


		// Now set the user set values
		//
		$this->override( $options  );
	}



	/**
	 * Override options stored in the options object.
	 *
	 * @param  array|\Golem\iFace\Data\Options $new The new options.
	 *
	 * @throws Exception when trying to change a sealed object.
	 * @throws If parameter is not of the right type.
	 *
	 * @return $this.
	 *
	 * @api
	 *
	 */
	public
	function override( $new )
	{
		if( $this->sealed )

			throw new Exception( "Cannot change sealed options object." );


		// Keep the distinction between what the client set and what are default values
		//
		if( $new instanceof \Golem\iFace\Data\Options )
		{
			$this->defaults = array_replace_recursive( $this->defaults, $new->defaults );
			$this->userset  = array_replace_recursive( $this->userset , $new->userset  );

			$this->options  = array_replace_recursive( $this->options , $this->defaults, $this->userset );
		}


		// If it's an array, assume there are no defaults
		//
		elseif( is_array( $new ) )
		{
			$this->options = array_replace_recursive( $this->options, $new );
			$this->userset = array_replace_recursive( $this->userset, $new );
		}


		else

			throw new Exception( 'Options::override only accepts parameter of type array and Golem\Interface\Options. Got: ' . gettype( $new ) . ' Dump: ' . print_r( $new, true ) );


		$this->notify( 'Options changed' );
		return $this;
	}



	/**
	 * Get a readonly array representation of the currently active options.
	 *
	 * @return array The parsed options.
	 *
	 * @api
	 *
	 */
	public function toArray()
	{
		return $this->options;
	}



	/**
	 * Get the set of values that where the defaults for this class.
	 *
	 * @return array The default options.
	 *
	 * @api
	 *
	 */
	public function defaults()
	{
		return $this->defaults;
	}



	/**
	 * Get those values that have been userset or set by the client.
	 *
	 * @return array The user set options.
	 *
	 * @api
	 *
	 */
	public function userset()
	{
		return $this->userset;
	}
}
