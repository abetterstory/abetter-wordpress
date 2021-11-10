<?php

namespace WPML\TM\Menu\TranslationServices\Endpoints;

use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\TM\TranslationProxy\Services\AuthorizationFactory;

class Activate implements IHandler {

	public function run( Collection $data ) {
		$serviceId = $data->get( 'service_id' );
		$apiToken  = $data->get( 'api_token' );

		$authorize = function ( $serviceId ) use ( $apiToken ) {
			$authorization = ( new AuthorizationFactory )->create();
			try {
				$authorization->authorize( (object) [ 'api_token' => $apiToken ] );

				return Either::of( $serviceId );
			} catch ( \Exception $e ) {
				$authorization->deauthorize();

				return Either::left( $e->getMessage() );
			}
		};

		return Either::of( $serviceId )
		             ->chain( [ Select::class, 'select' ] )
		             ->chain( $authorize );
	}
}