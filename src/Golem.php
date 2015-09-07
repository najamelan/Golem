<?php
/**
 * @ignore
 * The main interface to the library.
 *
 */

/**
 * The toplevel namespace for the library.
 *
 */
namespace Golem;

require_once __DIR__ . '/Traits/Autoload.php';

use   Golem\iFace\Data\Options          as iOptions
	 , Golem\iFace\Data\LogOptions       as iLogOptions

    , Golem\Reference\Logger
    , Golem\Reference\Data\GolemOptions
    , Golem\Reference\Data\LogOptions
    , Golem\Reference\Data\File

    , \Exception
;



/**
 * Phpdoc cannot deal with string concatenaton in class constants, so we
 * have to store this value here.
 *
 */
define( 'PHPDOCBUG', __DIR__ . '/GolemDefaults.yml' );


/**
 * The main library object
 *
 *
 *
 */
class Golem
{
	use Traits\Seal;

	const DEFAULT_OPTIONS_FILE = PHPDOCBUG;


	private $defaultOptions;
	private $clientOptions;
	private $options;


	/**
	 * Create a library object.
	 *
	 * You should usuall create one and pass it round in your application.
	 * You can seal this object to prevent changes to security configuration
	 * to made by code included later in your application.
	 *
	 */
	public
	function __construct( $options = null )
	{
		$optionFile = new File( self::DEFAULT_OPTIONS_FILE );

		$this->defaultOptions = new GolemOptions( $optionFile->parse() );
		$this->options        = clone $this->defaultOptions;


		if( $options instanceof Reference\Data\Options )
		{
			$this->userOptions = $options;
			$this->options->override( $this->userOptions );
		}


		elseif( is_string( $options )  &&  file_exists( $options ) )
		{
			$optionFile = new File( $options );
			$this->userOptions = new GolemOptions( $optionFile->parse() );

			$this->options->override( $this->userOptions );
		}


		elseif( !is_null( $options ) )

			throw "Cannot get valid options file from . $options";
	}



	/**
	 * Create a logger object.
	 *
	 * @param array|Golem\iFace\Data\Options $options to override the defaults
	 *
	 * @throws \Exceptions On wrong parameter type
	 *
	 * @return Reference\Logger
	 *
	 */
	public
	function logger( $options = [] )
	{
		if( is_array( $options ) )

			$options = new LogOptions( $this, $options );


		if( ! $options instanceof iLogOptions )

			throw new Exception( "Invalid parameter type given to Golem::logger(). Got: " . get_class( $options ) );


		return new Logger( $options );
	}



	/**
	 * Provides a read only copy of the options object for the library.
	 *
	 * @return iFace\Data\Options The currently loaded library configuration.
	 *
	 */
	public
	function options()
	{
		$value = clone $this->options;
		$value->seal();

		return $value;
	}
}
