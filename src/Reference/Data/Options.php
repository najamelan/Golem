<?php


namespace Golem\Reference\Data;
use \ArrayAccess, \Exception;


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
class      Options
implements \Golem\iFace\Data\Options
{
	use \Golem\Traits\Seal;
	use \Golem\Traits\ArrayAccess;


	/** @var bool Whether the object is sealed.
	 */
	private   $sealed   = false ;

	protected $parsed   = []    ;
	protected $defaults = []    ;



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
		$this->override( $defaults );

		$this->defaults = $this->parsed;

		$this->override( $options  );
	}



	/**
	 * Override options stored in the options object.
	 *
	 * @param  mixed options The new options. Can either be a Golem\iFace\Data\Option or an array.
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
	function override( $Options )
	{
		if( $this->sealed )

			throw new Exception( "Cannot change sealed options object." );


		if( $Options instanceof \Golem\iFace\Data\Options )

			$this->overrideArray( $Options->parsed );


		elseif( is_array( $Options ) )

			$this->overrideArray( $Options );


		else

			throw new Exception( 'Options::override only accepts parameter of type array and Golem\Interface\Options. Got: ' . gettype( $Options ) . ' Dump: ' . print_r( $Options, true ) );


		return $this;
	}



	private
	function overrideArray( $override )
	{
		$this->parsed = array_replace_recursive( $this->parsed, $override );
	}
}
