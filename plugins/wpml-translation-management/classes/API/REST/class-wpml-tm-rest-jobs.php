<?php

class WPML_TM_REST_Jobs extends WPML_REST_Base {
	const CAPABILITY = 'translate';

	/** @var WPML_TM_Jobs_Repository */
	private $jobs_repository;

	/** @var WPML_TM_Rest_Jobs_Criteria_Parser */
	private $criteria_parser;

	/** @var WPML_TM_Rest_Jobs_View_Model */
	private $view_model;

	/** @var WPML_TP_Sync_Update_Job */
	private $update_jobs;

	/** @var WPML_TM_Last_Picked_Up $wpml_tm_last_picked_up */
	private $wpml_tm_last_picked_up;

	/**
	 * @param WPML_TM_Jobs_Repository           $jobs_repository
	 * @param WPML_TM_Rest_Jobs_Criteria_Parser $criteria_parser
	 * @param WPML_TM_Rest_Jobs_View_Model      $view_model
	 * @param WPML_TP_Sync_Update_Job           $update_jobs
	 * @param WPML_TM_Last_Picked_Up            $wpml_tm_last_picked_up
	 */
	public function __construct(
		WPML_TM_Jobs_Repository $jobs_repository,
		WPML_TM_Rest_Jobs_Criteria_Parser $criteria_parser,
		WPML_TM_Rest_Jobs_View_Model $view_model,
		WPML_TP_Sync_Update_Job $update_jobs,
		WPML_TM_Last_Picked_Up $wpml_tm_last_picked_up
	) {
		parent::__construct( 'wpml/tm/v1' );

		$this->jobs_repository        = $jobs_repository;
		$this->criteria_parser        = $criteria_parser;
		$this->view_model             = $view_model;
		$this->update_jobs            = $update_jobs;
		$this->wpml_tm_last_picked_up = $wpml_tm_last_picked_up;
	}


	public function add_hooks() {
		$this->register_routes();
	}

	public function register_routes() {
		parent::register_route( '/jobs',
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_jobs' ),
				'args'     => array(
					'scope'           => array(
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_scope' ),
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'string' ),
					),
					'title'           => array(
						'type'              => 'string',
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'string' ),
					),
					'source_language' => array(
						'type'              => 'string',
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'string' ),
					),
					'target_language' => array(
						'type'              => 'string',
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'string' ),
					),
					'status'          => array(
						'type'              => 'string',
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'string' ),
					),
					'limit'           => array(
						'type'              => 'integer',
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'integer' ),
					),
					'offset'          => array(
						'type'              => 'integer',
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'integer' ),
					),
					'sorting'         => array(
						'validate_callback' => array( $this, 'validate_sorting' ),
					),
					'translated_by'   => array(
						'type'              => 'string',
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'string' ),
					),
					'sent_from'       => array(
						'type'              => 'string',
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'date' ),
					),
					'sent_to'         => array(
						'type'              => 'string',
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'date' ),
					),
					'deadline_from'   => array(
						'type'              => 'string',
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'date' ),
					),
					'deadline_to'     => array(
						'type'              => 'string',
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'date' ),
					),
				),
			)
		);

		parent::register_route( '/jobs/assign',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'assign_job' ),
				'args'     => array(
					'jobId'        => array(
						'required'          => true,
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'integer' ),
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'integer' ),
					),
					'type'         => array(
						'required'          => false,
						'validate_callback' => array( $this, 'validate_job_type' ),
					),
					'translatorId' => array(
						'required'          => true,
						'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'integer' ),
						'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'integer' ),
					),
				),
			)
		);

		parent::register_route( '/jobs/cancel',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'cancel_jobs' ),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function get_jobs( WP_REST_Request $request ) {
		try {
			$criteria = $this->criteria_parser->build_criteria( $request );

			$model = $this->view_model->build(
				$this->jobs_repository->get( $criteria ),
				$this->jobs_repository->get_count( $criteria )
			);

			$model['last_picked_up_date'] = $this->wpml_tm_last_picked_up->get();

			return $model;
		} catch ( Exception $e ) {
			return new WP_Error( 500, $e->getMessage() );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function assign_job( WP_REST_Request $request ) {
		$result = null;

		/**
		 * It can be job_id from icl_translate_job or id from icl_string_translations
		 * @var int $job_id
		 */
		$job_id           = $request->get_param( 'jobId' );
		$job_type         = $request->get_param( 'type' ) ? $request->get_param( 'type' ) : WPML_TM_Job_Entity::POST_TYPE;
		$translator_email = $request->get_param( 'translatorId' );
		$user             = get_user_by( 'ID', $translator_email );

		if ( $user ) {
			$assign_to = wpml_tm_assign_translation_job( $job_id, $user->ID, 'local', $job_type );
			if ( $assign_to ) {
				$result = array( 'assigned' => $assign_to, );
			}
		}

		return $result;
	}

	public function cancel_jobs( WP_REST_Request $request ) {
		$result = array();

		$jobs = array_filter( $request->get_params(), array( $this, 'validate_job' ) );
		if ( $jobs ) {
			foreach ( $jobs as $job_id ) {
				$job = $this->jobs_repository->get_job( $job_id['id'], $job_id['type'] );
				if ( $job ) {
					$job->set_status( ICL_TM_NOT_TRANSLATED );
					$this->update_jobs->update_state( $job );

					$result[] = $job_id;
				}
			}
		}

		return $result;
	}

	public function get_allowed_capabilities( WP_REST_Request $request ) {
		return array( WPML_Manage_Translations_Role::CAPABILITY, WPML_Translator_Role::CAPABILITY );
	}

	public function validate_scope( $scope ) {
		return in_array( $scope, array(
			WPML_TM_Jobs_Search_Params::SCOPE_ALL,
			WPML_TM_Jobs_Search_Params::SCOPE_REMOTE,
			WPML_TM_Jobs_Search_Params::SCOPE_LOCAL,
		), true );
	}

	public function validate_sorting( $sorting ) {
		if ( ! is_array( $sorting ) ) {
			return false;
		}

		try {
			foreach ( $sorting as $column => $asc_or_desc ) {
				new WPML_TM_Jobs_Sorting_Param( $column, $asc_or_desc );
			}
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * @param array $job
	 *
	 * @return bool
	 */
	private function validate_job( array $job ) {
		return isset( $job['id'] ) && isset( $job['type'] ) && $this->validate_job_type( $job['type'] );
	}

	public function validate_job_type( $value ) {
		return ! $value || in_array( $value, array(
				WPML_TM_Job_Entity::POST_TYPE,
				WPML_TM_Job_Entity::STRING_TYPE,
				WPML_TM_Job_Entity::PACKAGE_TYPE
			) );
	}
}