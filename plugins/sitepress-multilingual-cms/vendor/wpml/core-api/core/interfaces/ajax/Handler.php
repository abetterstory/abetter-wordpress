<?php

namespace WPML\Ajax;

use WPML\Collect\Support\Collection;
use WPML\FP\Either;

interface IHandler {
	/**
	 * @param \WPML\Collect\Support\Collection<mixed> $data
	 *
	 * @return \WPML\FP\Either
	 */
	public function run( Collection $data );
}
