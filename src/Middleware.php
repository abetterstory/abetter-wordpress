<?php

namespace ABetter\Wordpress;

use Closure;

class Middleware {

	public function handle($request, Closure $next) {

		$response = $next($request);

		$data = (isset($response->original)) ? $response->original->getData() : NULL;
		$error = (isset($data['error'])) ? $data['error'] : 0;

		if ($error > 400) $response->setStatusCode($error);

		return $response;

	}

}
