<?php

namespace WPML\Media\Widgets\Block;

use WPML\Element\API\Languages;
use WPML\LIB\WP\Hooks;
use function WPML\FP\spreadArgs;

class DisplayTranslation implements \IWPML_Frontend_Action, \IWPML_DIC_Action {

	/**
	 * @var \WPML_Media_Translated_Images_Update $translatedImageUpdate
	 */
	private $translatedImageUpdate;

	public function __construct( \WPML_Media_Translated_Images_Update $translatedImageUpdate ) {
		$this->translatedImageUpdate = $translatedImageUpdate;
	}

	public function add_hooks() {
		Hooks::onFilter( 'widget_block_content' )
		     ->then( spreadArgs( function ( $content ) {
		     	return $this->translatedImageUpdate->replace_images_with_translations( $content, Languages::getCurrentCode() );
		     } ) );
	}
}
