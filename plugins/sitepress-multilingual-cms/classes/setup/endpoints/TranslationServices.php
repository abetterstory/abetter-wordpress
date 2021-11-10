<?php

namespace WPML\Setup\Endpoint;


use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\LIB\WP\Http;
use WPML\TM\Geolocalization;
use WPML\TM\Menu\TranslationServices\ActiveServiceRepository;
use WPML\TM\Menu\TranslationServices\ServiceMapper;
use WPML\TM\Menu\TranslationServices\ServicesRetriever;
use function WPML\Container\make;
use function WPML\FP\partialRight;

class TranslationServices implements IHandler {

	public function run( Collection $data ) {
		$tpApi = make( \WPML_TP_Client_Factory::class )->create()->services();

		$services = ServicesRetriever::get(
			$tpApi,
			Geolocalization::getCountryByIp( Http::post() ),
			partialRight(
				[ ServiceMapper::class, 'map' ],
				[ ActiveServiceRepository::class, 'getId' ]
			)
		);

		return Either::of( [
			'services' => $services,
			'logoPlaceholder' => WPML_TM_URL . '/res/img/lsp-logo-placeholder.png',
		] );
	}
}