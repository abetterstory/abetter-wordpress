<?php

class WPML_TM_Jobs_List_Translators {
	/** @var WPML_Translator_Records */
	private $translator_records;

	/**
	 * @param WPML_Translator_Records $translator_records
	 */
	public function __construct( WPML_Translator_Records $translator_records ) {
		$this->translator_records = $translator_records;
	}


	public function get() {
		$translators = $this->translator_records->get_users_with_capability();

		return array_map( array( $this, 'map' ), $translators );
	}

	private function map( $translator ) {
		return array(
			'value' => $translator->ID,
			'label' => $translator->display_name,
		);
	}
}