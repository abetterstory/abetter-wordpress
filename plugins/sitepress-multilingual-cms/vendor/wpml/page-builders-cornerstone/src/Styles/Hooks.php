<?php

namespace WPML\PB\Cornerstone\Styles;

use WPML\FP\Fns;
use WPML\FP\Logic;
use WPML\FP\Maybe;
use WPML\LIB\WP\Post;

class Hooks implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_DIC_Action {

	const META_KEY = '_cs_generated_styles';

	/** @var callable $shouldInvalidateStyle */
	private $shouldInvalidateStyles;

	/**
	 * Hooks constructor.
	 *
	 * @param \WPML_PB_Last_Translation_Edit_Mode $lastEditMode
	 * @param \WPML_Cornerstone_Data_Settings     $dataSettings
	 */
	public function __construct(
		\WPML_PB_Last_Translation_Edit_Mode $lastEditMode,
		\WPML_Cornerstone_Data_Settings $dataSettings
	) {
		$this->shouldInvalidateStyles = Logic::both( [ $dataSettings, 'is_handling_post' ], [ $lastEditMode, 'is_translation_editor' ] );
	}


	public function add_hooks() {
		add_action( 'save_post', [ $this, 'invalidateStylesInTranslation' ] );
	}

	/**
	 * @param int $postId
	 */
	public function invalidateStylesInTranslation( $postId ) {
		Maybe::of( $postId )
			->filter( $this->shouldInvalidateStyles )
			->map( Post::deleteMeta( Fns::__, self::META_KEY ) );
	}
}