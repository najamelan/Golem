<?php

namespace Golem\Traits;

use

	  \SplObserver

;


/**
 * Implementation of SplSubject
 *
 */
trait Subject
{
	private $observers = [] ;
	private $content        ;



	// add observer
	//
	public
	function attach( SplObserver $observer )
	{
		$this->observers[] = $observer;
	}



	// remove observer
	//
	public
	function detach( SplObserver $observer )
	{

		$key = array_search( $observer, $this->observers, true );


		if( $key )

			unset( $this->observers[ $key ] );

	}



	// notify observers
	//
	public
	function notify( $eventName = null )
	{
		foreach( $this->observers as $observer )

			$observer->update( $this, $eventName );
	}
}
