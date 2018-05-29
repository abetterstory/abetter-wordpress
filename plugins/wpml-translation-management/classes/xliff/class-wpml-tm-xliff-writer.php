<?php
/**
 * @package wpml-core
 */

class WPML_TM_Xliff_Writer extends WPML_TM_Job_Factory_User {
	private $xliff_version;
	const TAB = "\t";

	/**
	 * WPML_TM_xliff constructor.
	 *
	 * @param WPML_Translation_Job_Factory $job_factory
	 * @param string                       $xliff_version
	 */
	public function __construct( &$job_factory, $xliff_version = TRANSLATION_PROXY_XLIFF_VERSION ) {
		parent::__construct( $job_factory );
		$this->xliff_version = $xliff_version;
	}

	/**
	 * Generate a XLIFF file for a given job.
	 *
	 * @param int $job_id
	 *
	 * @return resource XLIFF representation of the job
	 */
	public function get_job_xliff_file( $job_id ) {

		return $this->generate_xliff_file( $this->generate_job_xliff( $job_id ) );
	}

	/**
	 * Generate a XLIFF string for a given post or external type (e.g. package) job.
	 *
	 * @param int $job_id
	 *
	 * @return string XLIFF representation of the job
	 */
	public function generate_job_xliff( $job_id ) {
		/** @var TranslationManagement $iclTranslationManagement */
		global $iclTranslationManagement;

		// don't include not-translatable and don't auto-assign
		$job               = $iclTranslationManagement->get_translation_job( (int) $job_id, false, false, 1 );
		$translation_units = $this->get_job_translation_units_data( $job );
		$original          = $job_id . '-' . md5( $job_id . $job->original_doc_id );

		$external_file_url = $this->get_external_url( $job );

		$xliff = $this->generate_xliff( $original,
			$job->source_language_code,
			$job->language_code,
			$translation_units, $external_file_url );

		return $xliff;
	}

	/**
	 * Generate a XLIFF file for a given set of strings.
	 *
	 * @param array  $strings
	 * @param string $source_language
	 * @param string $target_language
	 *
	 * @return resource XLIFF file
	 */
	public function get_strings_xliff_file( $strings, $source_language, $target_language ) {

		return $this->generate_xliff_file(
			$this->generate_xliff(
				uniqid('string-', true),
				$source_language,
				$target_language,
				$this->generate_strings_translation_units_data( $strings ) )
		);
	}

	private function generate_xliff(
		$original_id,
		$source_language,
		$target_language,
		array $translation_units = array(),
		$external_file_url = null
	) {
		$xliff = new WPML_TM_XLIFF( $this->get_xliff_version(), '1.0', 'utf-8' );

		$string = $xliff->setFileAttributes( array(
			                                     'original'        => $original_id,
			                                     'source-language' => $source_language,
			                                     'target-language' => $target_language,
			                                     'datatype'        => 'plaintext',
		                                     ) )
		                ->setReferences( array(
			                                 'external-file' => $external_file_url,
		                                 ) )->setPhaseGroup( $this->get_shortcodes_data() )
		                ->setTranslationUnits( $translation_units )
		                ->toString();

		return $string;
	}

	private function get_xliff_version() {
		switch ( $this->xliff_version ) {
			case '10':
				return '1.0';
			case '11':
				return '1.1';
			case '12':
			default:
				return '1.2';
		}
	}

	/**
	 * Generate translation units for a given set of strings.
	 *
	 * The units are the actual content to be translated
	 * Represented as a source and a target
	 *
	 * @param array $strings
	 *
	 * @return array The translation units representation
	 */
	private function generate_strings_translation_units_data( $strings ) {
		$translation_units = array();
		foreach ( $strings as $string ) {
			$id                  = 'string-' . $string->id;
			$translation_units[] = $this->get_translation_unit_data( $id, 'string', $string->value, $string->value );
		}

		return $translation_units;
	}

	/**
	 * Generate translation units.
	 *
	 * The units are the actual content to be translated
	 * Represented as a source and a target
	 *
	 * @param stdClass $job
	 *
	 * @return array The translation units data
	 */
	private function get_job_translation_units_data( $job ) {
		$translation_units = array();
		/** @var array $elements */
		$elements = $job->elements;
		if ( $elements ) {
			foreach ( $elements as $element ) {
				if ( 1 === (int) $element->field_translate ) {
					$field_data_translated = base64_decode( $element->field_data_translated );
					$field_data            = base64_decode( $element->field_data );
					if ( 0 === strpos( $element->field_type, 'field-' ) ) {
						$field_data_translated = apply_filters( 'wpml_tm_xliff_export_translated_cf',
						                                        $field_data_translated,
						                                        $element );
						$field_data            = apply_filters( 'wpml_tm_xliff_export_original_cf',
						                                        $field_data,
						                                        $element );
					}
					// check for untranslated fields and copy the original if required.
					if ( ! null === $field_data_translated || '' === $field_data_translated ) {
						$field_data_translated = $this->remove_etx_char( $field_data );
					}
					if ( $this->is_valid_unit_content( $field_data ) ) {
						$translation_units[] = $this->get_translation_unit_data( $element->field_type,
						                                                         $element->field_type,
						                                                         $field_data,
						                                                         $field_data_translated );
					}
				}
			}
		}
		return $translation_units;
	}

	private function get_translation_unit_data( $field_id, $field_name, $field_data, $field_data_translated ) {
		global $sitepress;

		$field_data = $this->remove_etx_char( $field_data );

		$translation_unit = array();
		if ( $sitepress->get_setting( 'xliff_newlines' ) === WPML_XLIFF_TM_NEWLINES_REPLACE ) {
			$field_data            = str_replace( "\n", '<br class="xliff-newline" />', $field_data );
			$field_data_translated = str_replace( "\n", '<br class="xliff-newline" />', $field_data_translated );
		}

		$translation_unit['attributes']['resname']  = $field_name;
		$translation_unit['attributes']['restype']  = 'string';
		$translation_unit['attributes']['datatype'] = 'html';
		$translation_unit['attributes']['id']       = $field_id;
		$translation_unit['source']   = $field_data;
		$translation_unit['target']   = $field_data_translated;


		return $translation_unit;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	private function remove_etx_char( $string ) {
		return preg_replace('/\x03/', '', $string);
	}

	/**
	 * Save a xliff string to a temporary file and return the file ressource
	 * handle
	 *
	 * @param string $xliff_content
	 *
	 * @return resource XLIFF
	 */
	private function generate_xliff_file( $xliff_content ) {
		$file = fopen( 'php://temp', 'rb+' );
		fwrite( $file, $xliff_content );
		rewind( $file );

		return $file;
	}

	/**
	 * @param $job
	 *
	 * @return false|null|string
	 */
	private function get_external_url( $job ) {
		$external_file_url = null;
		if ( isset( $job->original_doc_id ) && 'post' === $job->element_type_prefix ) {
			$external_file_url = get_permalink( $job->original_doc_id );

			return $external_file_url;
		}

		return $external_file_url;
	}

	private function get_shortcodes() {
		global $shortcode_tags;
		if ( $shortcode_tags ) {
			return array_keys( $shortcode_tags );
		}

		return array();
	}

	/**
	 * @return array
	 */
	private function get_shortcodes_data() {
		$short_codes = $this->get_shortcodes();

		if ( $short_codes ) {
			return array(
				'shortcodes' => array(
					'process-name' => 'Shortcodes identification',
					'note'         => implode( ',', $short_codes )
				)
			);
		}

		return array();
	}
}
