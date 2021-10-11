<?php

use ACFML\FieldState;

class WPML_ACF_Custom_Fields_Sync {

	const TR_JOB_FIELD_PATTERN = '/field-(\S+)-[0-9]/';
	
	/**
	 * @var FieldState
	 */
	private $field_state;
	
	public function __construct( FieldState $field_state ) {
		$this->field_state = $field_state;
	}

	/**
	 * Registers hooks related to custom fields synchronisation.
	 */
	public function register_hooks() {
		add_filter( 'wpml_tm_job_field_is_translatable', array( $this, 'adjust_is_translatable_for_field_in_translation_job' ), 10, 2 );
		// make copy once synchronisation working (acfml-155).
		add_filter( 'acf/update_value', array( $this, 'clean_empty_values_for_copy_once_field' ), 10, 3 );
	}

	/**
	 * Change empty custom field value into null when field is set to "Copy once".
	 *
	 * On the very beginning of the post creation process, ACF saves values into postmeta
	 * even though fields are empty. This makes "copy once" option not working:
	 * translated posts always has some value in this field and copying doesn't start.
	 * However, if this value is strictly equal to null, ACF deletes postmeta
	 * instead of saving empty value. Copy once sees there is no value and is copying
	 * value from original post.
	 *
	 * @see \acf_update_value()
	 *
	 * @param mixed      $value          Filtered custom field value.
	 * @param int|string $post_id        ID of the post or option page.
	 * @param array      $field          ACF field data.
	 *
	 * @return mixed|null Filtered value.
	 */
	public function clean_empty_values_for_copy_once_field( $value, $post_id, $field ) {
		if ( '' === $value
			&& ! $this->value_has_been_emptied( $field )
			&& isset( $field['wpml_cf_preferences'] )
			&& WPML_COPY_ONCE_CUSTOM_FIELD === $field['wpml_cf_preferences']
			&& ! $this->isFieldType( $field, 'group' )
		) {
			$value = null;
		}
		return $value;
	}
	
	private function value_has_been_emptied( $field ) {
		$state_before = $this->field_state->getStateBefore();
		return ! empty( $state_before[ $field['name'] ] );
	}

	/**
	 * Check if field is of given type.
	 *
	 * @param array  $field The ACF field.
	 * @param string $type  Field type.
	 *
	 * @return bool
	 */
	private function isFieldType( $field, $type ) {
		return isset( $field['type'] ) && $type === $field['type'];
	}

	/**
	 * WP filter hook to update $is_translatable to true when numeric values is being sent to translation and it is
	 * ACF field's value.
	 *
	 * @param bool  $is_translatable  if field should be displayed in Translation Editor
	 * @param array $job_translate    translation job details
	 *
	 * @return bool
	 */
	public function adjust_is_translatable_for_field_in_translation_job( $is_translatable, $job_translate ) {
		/*
		 * Numeric fields are set as not translatable in translation jobs
		 * but with ACF you can create fields with numeric value which actually you would like
		 * to translate. This filter is to check if field comes from ACF and then set it as translatable
		 */
		if ( ! $is_translatable && isset( $job_translate['field_type'] ) ) {
			if ( $this->is_acf_field( $job_translate ) ) {
				$is_translatable = true;
			}
		}

		return $is_translatable;
	}

	/**
	 * @param array $job_translate Translation Job data
	 *
	 * @return bool
	 */
	private function is_acf_field( $job_translate ) {
		return preg_match(self::TR_JOB_FIELD_PATTERN, $job_translate['field_type'], $matches) && (bool) acf_get_field( $matches[1] );
	}
}