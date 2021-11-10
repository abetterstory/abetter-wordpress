<?php

namespace WPML\TM\ATE;

use WPML\API\PostTypes;
use WPML\Collect\Support\Collection;
use WPML\Element\API\Languages;
use WPML\FP\Fns;
use WPML\FP\Left;
use WPML\FP\Logic;
use WPML\FP\Lst;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\FP\Relation;
use WPML\FP\Right;
use WPML\FP\Wrapper;
use WPML\LIB\WP\Post;
use WPML\Media\Option as MediaOption;
use WPML\Records\Translations;
use WPML\Setup\Option;
use WPML\TM\API\ATE\CachedLanguageMappings;
use WPML\TM\API\ATE\LanguageMappings;
use WPML\TM\AutomaticTranslation\Actions\Actions;
use WPML\Utilities\KeyedLock;
use function WPML\Container\make;
use function WPML\FP\invoke;
use function WPML\FP\pipe;

class TranslateEverything {

	const LOCK_RELEASE_TIMEOUT = 2 * MINUTE_IN_SECONDS;
	const QUEUE_SIZE = 15;

	public function run(
		Collection $data,
		Actions $actions
	) {
		if ( ! MediaOption::isSetupFinished() ) {
			return Left::of( [ 'key' => 'waiting' ] );
		}

		$lock = make( KeyedLock::class, [ ':name' => self::class ] );
		$key  = $lock->create( $data->get( 'key' ), self::LOCK_RELEASE_TIMEOUT );

		if ( $key ) {
			$createdJobs = [];
			if ( Option::shouldTranslateEverything() ) {
				$createdJobs = $this->translateEverything( $actions );
			}

			if ( self::isEverythingProcessed() || ! Option::shouldTranslateEverything() ) {
				$lock->release();
				$key = false;
			}

			return Right::of( [ 'key' => $key, 'createdJobs' => $createdJobs ] );
		} else {
			return Left::of( [ 'key' => 'in-use', ] );
		}
	}

	/**
	 * @param Actions $actions
	 */
	private function translateEverything( Actions $actions ) {
		$defaultLang        = Languages::getDefaultCode();
		$secondaryLanguages = LanguageMappings::geCodesEligibleForAutomaticTranslations();
		$postType           = self::getPostTypeToProcess( $secondaryLanguages );

		$elements = self::getPostsOfTypeToTranslation(
			$defaultLang,
			$secondaryLanguages,
			$postType
		);

		$queueSize = $postType == 'attachment' ? self::QUEUE_SIZE * 2 : self::QUEUE_SIZE;
		if ( count( $elements ) <= $queueSize ) {
			Option::markPostTypeAsCompleted( $postType, $secondaryLanguages );
		}

		return count( $elements ) ?
			$actions->createNewTranslationJobs( $defaultLang, $elements->slice( 0, $queueSize )->toArray() ) :
			[];
	}

	/**
	 * @param $defaultLang
	 * @param array $secondaryLanguages
	 * @param $postType
	 *
	 * @return Collection
	 */
	private static function getPostsOfTypeToTranslation(
		$defaultLang,
		array $secondaryLanguages,
		$postType
	) {
		$source            = Obj::lensProp( 'source' );
		$translations      = Obj::lensProp( 'translations' );
		$hasSource         = pipe( Obj::view( $source ), Lst::length(), Relation::equals( 1 ) );
		$hasUntranslated   = pipe( Obj::view( $translations ), Lst::length() );
		$getPostId         = Obj::over( $source, pipe( Lst::nth( 0 ), Obj::prop( 'element_id' ) ) );
		$isSourcePublished = pipe(
			Obj::view( $source ),
			Lst::nth( 0 ),
			Obj::prop( 'element_id' ),
			Post::getStatus(),
			Relation::equals( 'publish' )
		);

		// it builds an array [ [post_id, lang_1], [post_id, lang_2], [post_id, lang_3] ]
		$buildPostIdTargetLangPairs = Fns::converge( Lst::xprod(), [ pipe( Obj::view( $source ), Lst::make() ), Obj::prop( 'translations' ) ] );

		$getOnlyUntranslatedLanguages = Obj::over( $translations, self::getLanguagesWhichDoNotHaveTranslation( $postType, $secondaryLanguages ) );

		$findPostTargetLangs = pipe(
			Maybe::of(),
			self::branchToSourceAndTranslations( $defaultLang ),
			Fns::filter( $hasSource ),
			Fns::filter( $isSourcePublished ),
			Fns::map( $getPostId ),
			Fns::map( $getOnlyUntranslatedLanguages ),
			Fns::filter( $hasUntranslated ),
			Fns::map( $buildPostIdTargetLangPairs ),
			invoke( 'getOrElse' )->with( [] )
		);

		$orderBy        = [ 'page' => Translations::OLDEST_FIRST, 'post' => Translations::NEWEST_FIRST ];
		$getForPostType = Translations::getForPostType( $orderBy );

		return $getForPostType( $postType )
			->groupBy( 'trid' )
			->map( $findPostTargetLangs )
			->flatten( 1 );
	}

	/**
	 * @param string[] $secondaryLanguages
	 *
	 * @return string
	 */
	private static function getPostTypeToProcess( array $secondaryLanguages ) {
		$postTypes = self::getPostTypesToTranslate( PostTypes::getAutomaticTranslatable(), $secondaryLanguages );

		return wpml_collect( $postTypes )
			->prioritize( Relation::equals( 'post' ) )
			->prioritize( Relation::equals( 'page' ) )
			->first();
	}

	/**
	 * Splits array of [element_id, language_code, source_language_code, trid, status, needs_update] (lets call it translation) into
	 *  [
	 *    source => translation // a source element
	 *    target => translation[] // completed translations
	 *  ]
	 *
	 * @param string $defaultLang
	 *
	 * @return callable
	 */
	private static function branchToSourceAndTranslations( $defaultLang ) {
		return pipe(
			Fns::map( Fns::converge( Lst::makePair(), [ Translations::getSourceInLanguage( $defaultLang ), self::filterCancelledOrNeedsUpdate() ] ) ),
			Fns::map( Lst::zipObj( [ 'source', 'translations' ] ) )
		);
	}

	/**
	 * Filters array of such elements: [element_id, language_code, source_language_code, trid, status, needs_update]
	 *
	 * @return callable
	 */
	private static function filterCancelledOrNeedsUpdate() {
		return Fns::reject( Logic::anyPass( [
			pipe(
				Obj::prop( 'status' ),
				Fns::unary( 'intval' ),
				Lst::includes( Fns::__, [ ICL_TM_NOT_TRANSLATED, ICL_TM_ATE_CANCELLED ] )
			),
			Obj::prop( 'needs_update' )
		] ) );
	}

	/**
	 * @param string $postType
	 * @param string[] $secondaryLanguages
	 *
	 * @return callable :: [[element_id, language_code, source_language_code, trid, status, needs_update], ...] -> [lang1, lang2]
	 */
	private static function getLanguagesWhichDoNotHaveTranslation( $postType, $secondaryLanguages ) {
		return pipe(
			invoke( 'toArray' ),
			Lst::pluck( 'language_code' ),
			Lst::diff( self::getLanguagesToTranslate( $postType, $secondaryLanguages ) ),
			Obj::values()
		);
	}

	/**
	 * @param array $postTypes
	 * @param array $targetLanguages
	 *
	 * @return string[] E.g. ['post', 'page']
	 */
	public static function getPostTypesToTranslate( array $postTypes, array $targetLanguages ) {
		$completed = Option::getTranslateEverythingCompleted();
		$postTypesNotCompletedForTargets = pipe( Obj::propOr( [], Fns::__, $completed ), Lst::diff( $targetLanguages ), Lst::length() );

		return Fns::filter( $postTypesNotCompletedForTargets, $postTypes );
	}

	/**
	 * @param string $postType
	 * @param array $targetLanguages
	 *
	 * @return string[] Eg. ['fr', 'de', 'es']
	 */
	public static function getLanguagesToTranslate( $postType, array $targetLanguages ) {
		$completed = Option::getTranslateEverythingCompleted();

		return Lst::diff( $targetLanguages, Obj::propOr( [], $postType, $completed ) );
	}

	/**
	 * Checks if Translate Everything is processed for a given Post Type and Language.
	 *
	 * @param string $postType
	 * @param string $language
	 *
	 * @return bool
	 */
	public static function isEverythingProcessedForPostTypeAndLanguage( $postType, $language ) {
		$completed = Option::getTranslateEverythingCompleted();
		return isset( $completed[ $postType ] ) && in_array(  $language, $completed[ $postType ] );
	}

	/**
	 * @param bool $cached
	 *
	 * @return bool
	 */
	public static function isEverythingProcessed( $cached = false ) {
		$postTypes       = PostTypes::getAutomaticTranslatable();
		$getTargetLanguages = [ $cached ? CachedLanguageMappings::class : LanguageMappings::class, 'geCodesEligibleForAutomaticTranslations'];

		return count( self::getPostTypesToTranslate( $postTypes, $getTargetLanguages() ) ) === 0;
	}
}
