<?php

namespace WPML\Media\Translate\Endpoint;

use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use function WPML\Container\make;
use WPML\FP\Right;

class DuplicateFeaturedImages implements IHandler {
	public function run( Collection $data ) {
		$numberLeft = $data->get( 'remaining', null );
		return Right::of(
			make( \WPML_Media_Attachments_Duplication::class )->batch_duplicate_featured_images( false, $numberLeft )
		);
	}
}
