<?php

class WPML_TM_Translation_Services_Admin_Section_Services_List_Template {

	const SERVICES_LIST_TEMPLATE = 'services-list.twig';

	/**
	 * @var IWPML_Template_Service
	 */
	private $template_service;

	/**
	 * @var WPML_TM_Translation_Services_Admin_Active_Template
	 */
	private $active_service_template;

	/**
	 * @var array
	 */
	private $available_services;

	/**
	 * @var array
	 */
	private $filtered_services;

	/**
	 * @var string
	 */
	private $translation_service_type_requested;

	/**
	 * @var string
	 */
	private $pagination;

	/**
	 * @var string
	 */
	private $current_url;

	/**
	 * @var string
	 */
	private $search_string;

	/**
	 * @var WPML_Admin_Table_Sort
	 */
	private $table_sort;
	/**
	 * @var
	 */
	private $has_preferred_service;

	/**
	 * WPML_TM_Translation_Services_Admin_Section_Template constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args ) {
		$this->template_service                   = $args['template_service'];
		$this->active_service_template            = $args['active_service_template'];
		$this->available_services                 = $args['available_services'];
		$this->filtered_services                  = $args['filtered_services'];
		$this->translation_service_type_requested = $args['translation_service_type_requested'];
		$this->current_url                        = $args['current_url'];
		$this->search_string                      = $args['search_string'];
		$this->pagination                         = $args['pagination'];
		$this->table_sort                         = $args['table_sort'];
		$this->has_preferred_service              = $args['has_preferred_service'];
	}

	public function render() {
		echo $this->template_service->show( $this->get_services_list_model(), self::SERVICES_LIST_TEMPLATE );
	}

	/**
	 * @return array
	 */
	private function get_services_list_model() {
		$filtered_services = $this->get_filtered_services();

		$model = array(
			'active_service'                     => $this->active_service_template->render(),
			'available_services'                 => $this->available_services,
			'filtered_services'                  => $filtered_services,
			'has_preferred_service'              => $this->has_preferred_service,
			'pagination_model'                   => $this->pagination,
			'table_sort'                         => $this->get_table_sort_columns(),
			'clean_search_url'                   => remove_query_arg( array( 'paged', 's' ), $this->current_url ),
			'current_url'                        => $this->current_url,
			'nonces'                             => array(
				WPML_TM_Translation_Services_Admin_Section_Ajax::NONCE_ACTION => wp_create_nonce( WPML_TM_Translation_Services_Admin_Section_Ajax::NONCE_ACTION ),
				WPML_TM_Translation_Service_Authentication_Ajax::AJAX_ACTION  => wp_create_nonce( WPML_TM_Translation_Service_Authentication_Ajax::AJAX_ACTION ),
			),
			'translation_service_type_requested' => $this->translation_service_type_requested,
			'search_string'                      => $this->search_string,
			'strings'                            => array(
				'no_service_found'        => array(
					__( 'WPML cannot load the list of translation services. This can be a connection problem. Please wait a minute and reload this page.', 'wpml-translation-management' ),
					__( 'If the problem continues, please contact %s.', 'wpml-translation-management' ),
				),
				'wpml_support'            => 'WPML support',
				'support_link'            => 'https://wpml.org/forums/forum/english-support/',
				'inactive_services_title' => WPML_TP_API_Services::TRANSLATION_MANAGEMENT_SYSTEM === $this->translation_service_type_requested ?
					__( 'Available Translation Management Systems', 'wpml-translation-management' ) :
					__( 'Available Translation Services', 'wpml-translation-management' ),
				'activate'                => __( 'Activate', 'wpml-translation-management' ),
				'documentation'           => __( 'Documentation', 'wpml-translation-management' ),
				'ts'                      => array(
					'link'        => __( "I'm looking for translation services", 'wpml-translation-management' ),
					'different'   => __( 'Looking for a different translation service?', 'wpml-translation-management' ),
					'tell_us_url' => 'https://wpml.org/documentation/content-translation/how-to-add-translation-services-to-wpml/#add-service-form',
					'tell_us'     => __( 'Tell us which one', 'wpml-translation-management' ),
					'url'         => add_query_arg( 'service-type', 'ts', $this->current_url ),
					'visible'     => WPML_TP_API_Services::TRANSLATION_MANAGEMENT_SYSTEM === $this->translation_service_type_requested,
				),
				'tms'                     => array(
					'link'    => __( "I'm looking for translation management systems", 'wpml-translation-management' ),
					'url'     => add_query_arg( 'service-type', WPML_TP_API_Services::TRANSLATION_MANAGEMENT_SYSTEM, $this->current_url ),
					'visible' => WPML_TP_API_Services::TRANSLATION_SERVICE === $this->translation_service_type_requested,
				),
				'filter'                  => array(
					'search'       => WPML_TP_API_Services::TRANSLATION_MANAGEMENT_SYSTEM === $this->translation_service_type_requested ?
						__( 'Search Translation Management Services', 'wpml-translation-management' ) :
						__( 'Search Translation Services', 'wpml-translation-management' ),
					'countries'    => __( 'All countries', 'wpml-translation-management' ),
					'filter_label' => __( 'Filter', 'wpml-translation-management' ),
					'clean_search' => __( 'Clear search', 'wpml-translation-management' ),
				),
				'pagination_items'        => __( 'items', 'wpml-translation-management' ),
				'columns'                 => array(
					'name'        => __( 'Name', 'wpml-translation-management' ),
					'description' => __( 'Description', 'wpml-translation-management' ),
					'popularity'  => __( 'Popularity', 'wpml-translation-management' ),
					'speed'       => __( 'Speed', 'wpml-translation-management' ),
				)
			)
		);

		return $model;
	}

	/**
	 * @return array
	 */
	private function get_table_sort_columns() {
		return array(
			'name'       => array(
				'url'     => $this->table_sort->get_column_url( 'name' ),
				'classes' => $this->table_sort->get_column_classes( 'name' ),
			),
			'popularity' => array(
				'url'     => $this->table_sort->get_column_url( 'popularity' ),
				'classes' => $this->table_sort->get_column_classes( 'popularity' ),
			),
			'speed'      => array(
				'url'     => $this->table_sort->get_column_url( 'speed' ),
				'classes' => $this->table_sort->get_column_classes( 'speed' ),
			),
		);
	}

	/**
	 * @return array
	 */
	private function get_filtered_services() {
		$services_model = array();

		foreach ( $this->filtered_services as $service ) {
			$services_model[] = array(
				'id'                             => $service->get_id(),
				'logo_url'                       => $service->get_logo_url(),
				'name'                           => $service->get_name(),
				'description'                    => $service->get_description(),
				'doc_url'                        => $service->get_doc_url(),
				'active'                         => $this->active_service_template && $service->get_id() === $this->active_service_template->get_id() ? 'active' : 'inactive',
				'popularity'                     => $service->get_rankings()->popularity,
				'speed'                          => $service->get_rankings()->speed,
				'how_to_get_credentials_desc'    => $service->get_how_to_get_credentials_desc(),
				'how_to_get_credentials_url'     => $service->get_how_to_get_credentials_url(),
				'client_create_account_page_url' => $service->get_client_create_account_page_url(),
				'custom_fields'                  => $service->get_custom_fields(),
			);
		}

		return $services_model;
	}
}