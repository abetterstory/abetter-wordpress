<?php

class WPML_Cornerstone_Translatable_Nodes implements IWPML_Page_Builders_Translatable_Nodes {

	const SETTINGS_FIELD = '_modules';

	/** @var array */
	protected $nodes_to_translate;

	/**
	 * @param string|int $node_id
	 * @param stdClass $settings
	 *
	 * @return WPML_PB_String[]
	 */
	public function get( $node_id, $settings ) {

		if ( ! $this->nodes_to_translate ) {
			$this->initialize_nodes_to_translate();
		}

		$strings = array();

		foreach ( $this->nodes_to_translate as $node_type => $node_data ) {
			if ( $this->conditions_ok( $node_data, $settings ) ) {
				foreach ( $node_data['fields'] as $field ) {
					$field_key = $field['field'];
					if ( isset( $settings[ $field_key ] ) && trim( $settings[ $field_key ] ) ) {

						$string = new WPML_PB_String(
							$settings[ $field_key ],
							$this->get_string_name( $node_id, $field, $settings ),
							$field['type'],
							$field['editor_type']
						);

						$strings[] = $string;
					}
				}
				if ( isset( $node_data['integration-class'] ) ) {
					try {
						$node    = new $node_data['integration-class']();
						$strings = $node->get( $node_id, $settings, $strings );
					} catch ( Exception $e ) {
					}
				}
			}
		}

		return $strings;
	}

	/**
	 * @param string $node_id
	 * @param stdClass $settings
	 * @param WPML_PB_String $string
	 *
	 * @return stdClass
	 */
	public function update( $node_id, $settings, WPML_PB_String $string ) {

		if ( ! $this->nodes_to_translate ) {
			$this->initialize_nodes_to_translate();
		}

		foreach ( $this->nodes_to_translate as $node_type => $node_data ) {
			if ( $this->conditions_ok( $node_data, $settings ) ) {
				foreach ( $node_data['fields'] as $field ) {
					$field_key = $field['field'];
					if ( $this->get_string_name( $node_id, $field, $settings ) == $string->get_name() ) {
						$settings[ $field_key ] = $string->get_value();
					}
				}
				if ( isset( $node_data['integration-class'] ) ) {
					try {
						$node     = new $node_data['integration-class']();
						$settings = $node->update( $node_id, $settings, $string );
					} catch ( Exception $e ) {

					}
				}
			}
		}

		return $settings;
	}

	/**
	 * @param string $node_id
	 * @param array $field
	 * @param stdClass $settings
	 *
	 * @return string
	 */
	public function get_string_name( $node_id, $field, $settings ) {
		return $field['field'] . '-' . $settings['_type'] . '-' . $node_id;
	}

	/**
	 * @param array $node_data
	 * @param stdClass $settings
	 *
	 * @return bool
	 */
	private function conditions_ok( $node_data, $settings ) {
		$conditions_meet = true;
		foreach ( $node_data['conditions'] as $field_key => $field_value ) {
			if ( ! isset( $settings[ $field_key ] ) || $settings[ $field_key ] != $field_value ) {
				$conditions_meet = false;
				break;
			}
		}

		return $conditions_meet;
	}

	public function initialize_nodes_to_translate() {

		$this->nodes_to_translate = array(
			'alert'                   => array(
				'conditions' => array( '_type' => 'alert' ),
				'fields'     => array(
					array(
						'field'       => 'alert_content',
						'type'        => __( 'Alert Content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'text'                    => array(
				'conditions' => array( '_type' => 'text' ),
				'fields'     => array(
					array(
						'field'       => 'text_content',
						'type'        => __( 'Text content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'quote'                   => array(
				'conditions' => array( '_type' => 'quote' ),
				'fields'     => array(
					array(
						'field'       => 'quote_content',
						'type'        => __( 'Quote content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
					array(
						'field'       => 'quote_cite_content',
						'type'        => __( 'Quote: cite content', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'counter'                 => array(
				'conditions' => array( '_type' => 'counter' ),
				'fields'     => array(
					array(
						'field'       => 'counter_number_prefix_content',
						'type'        => __( 'Counter: number prefix', 'sitepress' ),
						'editor_type' => 'LINE'
					),
					array(
						'field'       => 'counter_number_suffix_content',
						'type'        => __( 'Counter: number suffix', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'content-area'            => array(
				'conditions' => array( '_type' => 'content-area' ),
				'fields'     => array(
					array(
						'field'       => 'content',
						'type'        => __( 'Content Area: content', 'sitepress' ),
						'editor_type' => 'AREA'
					),
				),
			),
			'breadcrumbs'             => array(
				'conditions' => array( '_type' => 'breadcrumbs' ),
				'fields'     => array(
					array(
						'field'       => 'breadcrumbs_home_label_text',
						'type'        => __( 'Breadcrumbs: home label text', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'audio'                   => array(
				'conditions' => array( '_type' => 'audio' ),
				'fields'     => array(
					array(
						'field'       => 'audio_embed_code',
						'type'        => __( 'Audio: embed code', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'headline'                => array(
				'conditions' => array( '_type' => 'headline' ),
				'fields'     => array(
					array(
						'field'       => 'text_content',
						'type'        => __( 'Headline text content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'content-area-off-canvas' => array(
				'conditions' => array( '_type' => 'content-area-off-canvas' ),
				'fields'     => array(
					array(
						'field'       => 'off_canvas_content',
						'type'        => __( 'Canvas content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'content-area-modal'      => array(
				'conditions' => array( '_type' => 'content-area-modal' ),
				'fields'     => array(
					array(
						'field'       => 'modal_content',
						'type'        => __( 'Modal content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'content-area-dropdown'   => array(
				'conditions' => array( '_type' => 'content-area-dropdown' ),
				'fields'     => array(
					array(
						'field'       => 'dropdown_content',
						'type'        => __( 'Dropdown content', 'sitepress' ),
						'editor_type' => 'VISUAL'
					),
				),
			),
			'button'                  => array(
				'conditions' => array( '_type' => 'button' ),
				'fields'     => array(
					array(
						'field'       => 'anchor_text_primary_content',
						'type'        => __( 'Anchor text: primary content', 'sitepress' ),
						'editor_type' => 'LINE'
					),
					array(
						'field'       => 'anchor_text_secondary_content',
						'type'        => __( 'Anchor text: secondary content', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'video'                   => array(
				'conditions' => array( '_type' => 'video' ),
				'fields'     => array(
					array(
						'field'       => 'video_embed_code',
						'type'        => __( 'Video: embed code', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'search-inline'           => array(
				'conditions' => array( '_type' => 'search-inline' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder',
						'type'        => __( 'Search Inline: placeholder', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'search-modal'           => array(
				'conditions' => array( '_type' => 'search-modal' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder',
						'type'        => __( 'Search Modal: placeholder', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'search-dropdown'           => array(
				'conditions' => array( '_type' => 'search-dropdown' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder',
						'type'        => __( 'Search Dropdown: placeholder', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
			'accordion'               => array(
				'conditions'        => array( '_type' => 'accordion' ),
				'fields'            => array(),
				'integration-class' => 'WPML_Cornerstone_Accordion',
			),
			'tabs'               => array(
				'conditions'        => array( '_type' => 'tabs' ),
				'fields'            => array(),
				'integration-class' => 'WPML_Cornerstone_Tabs',
			),
		);

		$this->nodes_to_translate = apply_filters( 'wpml_cornerstone_modules_to_translate', $this->nodes_to_translate );

	}
}
