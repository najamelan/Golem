<?php

// Automatically load everything when needed
//
spl_autoload_register
(
	function( $pClassName )
	{
		// If other autoloaders are registered by other libraries, we don't want
		// to break here because of require when they need to autoload their classes
		//
		if( strpos( $pClassName, 'Golem\\' ) !== 0 )

			return false;


		// remove the Golem namespace
		//
		$pClassName = str_replace( 'Golem\\', '', $pClassName );

		// Turn the backslashes from namespaces into forward slashes for linux
		//
		include_once __DIR__ . '/../' . str_replace( "\\", "/", $pClassName ) . '.php';
	}
);
