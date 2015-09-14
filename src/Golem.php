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
	use

		  Traits\Seal
		, Traits\HasOptions

	;


	const DEFAULT_OPTIONS_FILE = PHPDOCBUG;


	protected $loggers = [];


	/**
	 * Create a library object.
	 *
	 * You should usuall create one and pass it round in your application.
	 * You can seal this object to prevent changes to security configuration
	 * to made by code included later in your application.
	 *
	 * @param array|string|Golem\iFace\Data\Options $options Filename for an options
	 *        file or array or Golem\iFace\Data\Options with values to override defaults.
	 *
	 * @throws \Exception When the input parameter is of wrong type.
	 * @throws \Exception When the filename passed is no existing file.
	 * @throws \Exception When the file passed does not parse correctly.
	 *
	 */
	public
	function __construct( $options = [] )
	{
		// TODO: $defaultOptions should be moved to options class, which already supports it btw.

		$defaultsFile = new File( self::DEFAULT_OPTIONS_FILE );
		$defaults     = $defaultsFile->parse();

		switch( Util::getType( $options ) )
		{
			case 'string'                           : $options = ( new File( $options ) )->parse();
			case 'array'                            : // fallthrough
			case 'Golem\Golem'                      : // fallthrough
			case 'Golem\Reference\Data\Options'     : // fallthrough
			case 'Golem\Reference\Data\GolemOptions': $this->options = new GolemOptions( $options, $defaults );
			                                          return;


			default: throw new Exception( "Cannot get valid options from a: " . Util::getType( $options ) );
		}
	}



	/**
	 * Get a logger object. If a logger with this name does not already exist it will be created. All loggers
	 * managed by Golem will have "Golem." prefixed to their name. All loggers made outside of Golem (instantiate
	 * Golem\Reference\Logger directly) should be in a different namespace. Note that if you pass options for an existing
	 * logger, these will override prior options, which might be unexpected. It is good practice to name loggers in a
	 * fine-grained fashion (eg. use __CLASS__ as name) in order not to step on other code's toes.
	 *
	 * @param string|array|Golem\iFace\Data\LogOptions $options to override the defaults. If a string is given
	 *        it will be assumed to be the name. It will still be prefixed with the default prefix (eg. when calling
	 *        logger( 'somename' ) you will get a logger named 'Golem.somename')
	 *
	 * @throws \Exception On wrong parameter type.
	 * @throws \Exception When trying to override options on a sealed logger.
	 *
	 * @return Reference\Logger
	 *
	 */
	public
	function logger( $options = [] )
	{
		if( is_string( $options ) )

			$options = [ 'name' => $options ];


		if( is_array( $options ) )

			$options = new LogOptions( $this, $options );


		if( ! $options instanceof iLogOptions )

			throw new Exception( "Invalid parameter type given to Golem::logger(). Got: " . Util::getType( $options ) );


		if( !isset( $this->loggers[ $options->name() ] ) )

			$this->loggers[ $options->name() ] = new Logger( $options );


		else

			$this->loggers[ $options->name() ]->override( $options );


		return $this->loggers[ $options->name() ];
	}
}
