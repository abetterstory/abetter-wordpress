<?php

namespace WPML\Element\API;

use WPML\Collect\Support\Traits\Macroable;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\LIB\WP\Post;
use function WPML\FP\curry;
use function WPML\FP\curryN;
use function WPML\FP\pipe;

/**
 * Class PostTranslations
 * @package WPML\Element\API
 * @method static callable|int setAsSource( ...$el_id, ...$language_code ) - Curried :: int → string → void
 * @method static callable|int setAsTranslationOf( ...$el_id, ...$translated_id, ...$language_code )
 * @method static callable|array get( ...$el_id ) - Curried :: int → [object]
 * @method static callable|array getIfOriginal( ...$el_id ) - Curried :: int → [object]
 * @method static callable|array getOriginal( ...$element_id ) - Curried :: int → object|null
 * @method static callable|array getOriginalId( ...$element_id ) - Curried :: int → int
 */
class PostTranslations {

	use Macroable;

	/**
	 * @return void
	 */
	public static function init() {

		self::macro( 'setAsSource', curryN( 2, self::withPostType( Translations::setAsSource() ) ) );

		self::macro( 'setAsTranslationOf', curryN( 3, self::withPostType( Translations::setAsTranslationOf() ) ) );

		self::macro( 'get', curryN( 1, self::withPostType( Translations::get() ) ) );

		self::macro( 'getIfOriginal', curryN( 1, self::withPostType( Translations::getIfOriginal() ) ) );

		self::macro( 'getOriginal', curryN( 1, self::withPostType( Translations::getOriginal() ) ) );

		self::macro( 'getOriginalId', curryN( 1, self::withPostType( Translations::getOriginalId() ) ) );
	}

	/**
	 * @param callable $fn
	 *
	 * @return \Closure
	 */
	public static function withPostType( $fn ) {
		return function () use ( $fn ) {
			$args = func_get_args();

			return call_user_func_array( $fn, Lst::insert( 1, 'post_' . Post::getType( $args[0] ), $args ) );
		};
	}
}

PostTranslations::init();
