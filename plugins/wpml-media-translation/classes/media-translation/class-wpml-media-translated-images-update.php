<?php

/**
 * Class WPML_Media_Translated_Images_Update
 * Translates images in a given text
 */
class WPML_Media_Translated_Images_Update {

	/**
	 * @var WPML_Media_Img_Parse
	 */
	private $img_parser;

	/**
	 * @var WPML_Media_Image_Translate
	 */
	private $image_translator;
	/**
	 * @var WPML_Media_Sizes
	 */
	private $media_sizes;

	/**
	 * WPML_Media_Translated_Images_Update constructor.
	 *
	 * @param WPML_Media_Img_Parse       $img_parser
	 * @param WPML_Media_Image_Translate $image_translator
	 * @param WPML_Media_Sizes           $media_sizes
	 */
	public function __construct( WPML_Media_Img_Parse $img_parser, WPML_Media_Image_Translate $image_translator, WPML_Media_Sizes $media_sizes ) {
		$this->img_parser       = $img_parser;
		$this->image_translator = $image_translator;
		$this->media_sizes      = $media_sizes;
	}

	/**
	 * @param string $text
	 * @param string $source_language
	 * @param string $target_language
	 *
	 * @return string
	 */
	public function replace_images_with_translations( $text, $target_language, $source_language = null ) {

		$imgs = $this->img_parser->get_imgs( $text );

		foreach ( $imgs as $img ) {
			$attachment_id = $translated_id = false;
			if ( isset( $img['attachment_id'] ) && $img['attachment_id'] ) {
				$attachment_id  = $img['attachment_id'];
				$translated_id  = apply_filters(
					'wpml_object_id',
					$attachment_id,
					'attachment',
					true,
					$target_language
				);
				$size           = $this->media_sizes->get_attachment_size( $img );
				$translated_src = $this->image_translator->get_translated_image( $attachment_id, $target_language, $size );
			} else {
				$translated_src = $this->get_translated_image_by_url( $target_language, $source_language, $img );
			}
			if ( $translated_src && $translated_src !== $img['attributes']['src'] ) {
				$text = $this->replace_image_src( $text, $img['attributes']['src'], $translated_src );
			}
			if ( $attachment_id && $attachment_id !== $translated_id ) {
				$text = $this->replace_att_class( $text, $attachment_id, $translated_id );
				$text = $this->replace_att_in_block( $text, $attachment_id, $translated_id );
			}
		}

		return $text;
	}

	/**
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	private function replace_image_src( $text, $from, $to ) {
		return str_replace( $from, $to, $text );
	}

	/**
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	private function replace_att_class( $text, $from, $to ) {
		$pattern     = '/\bwp-image-' . $from . '\b/u';
		$replacement = 'wp-image-' . $to;
		return preg_replace( $pattern, $replacement, $text );
	}

	/**
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	private function replace_att_in_block( $text, $from, $to ) {
		$pattern     = '/<!-- wp:image {.*?"id":(' . $from . '),.*?-->/u';
		$replacement = function ( $matches ) use ( $to ) {
			return str_replace( '"id":' . $matches[1], '"id":' . $to, $matches[0] );
		};

		return preg_replace_callback( $pattern, $replacement, $text );
	}

	/**
	 * @param $target_language
	 * @param $source_language
	 * @param $img
	 *
	 * @return bool|string
	 */
	private function get_translated_image_by_url( $target_language, $source_language, $img ) {
		if ( null === $source_language ) {
			$source_language = wpml_get_current_language();
		}
		$translated_src = $this->image_translator->get_translated_image_by_url(
			$img['attributes']['src'],
			$source_language,
			$target_language
		);

		return $translated_src;
	}

}
