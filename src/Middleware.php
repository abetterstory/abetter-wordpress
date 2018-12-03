<?php

namespace ABetter\Wordpress;

use Closure;

class Middleware {

	public function handle($request, Closure $next) {

		$response = $next($request);

		$data = (isset($response->original) && method_exists($response->original,'getData')) ? $response->original->getData() : NULL;
		$post = (isset($data['post'])) ? $data['post'] : NULL;
		$error = (isset($data['error'])) ? $data['error'] : 0;
		$expire = (isset($data['expire'])) ? $data['expire'] : '1 hour';
		$redirect = (isset($data['redirect'])) ? $data['redirect'] : NULL;

		if ($error > 400) $response->setStatusCode($error);

		if ($redirect) return \Redirect::to($redirect);

		// ---

		$expire = (is_numeric($expire)) ? $expire : strtotime($expire,0);

		if (method_exists($response,'header')) {
			$response->header('Pragma', 'public');
			$response->header('Cache-Control', 'public, max-age='.$expire);
			//$response->header('Last-Modified', gmdate('D, d M Y H:i:s \G\M\T', strtotime($post->post_date_gmt)));
			$response->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $expire));
			$response->header('Etag', md5($response->content()));
			//@header('Access-Control-Allow-Origin: *');
			//@header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
			//@header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With');
		}

		return $response;

	}

}
