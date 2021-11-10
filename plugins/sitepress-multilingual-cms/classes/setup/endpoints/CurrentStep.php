<?php

namespace WPML\Setup\Endpoint;

use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\Setup\Option;

class CurrentStep implements IHandler {

	const STEPS = [ 'languages', 'address', 'license', 'translation', 'support', 'plugins', 'finished' ];

	public function run( Collection $data ) {
		$isValid = Lst::includes( Fns::__, self::STEPS );

		return Either::fromNullable( Obj::prop( 'currentStep', $data ) )
		             ->filter( $isValid )
		             ->map( [ Option::class, 'saveCurrentStep' ] );
	}

}
