<?php
/**
 * @author OnTheGo Systems
 */

class WPML_TM_REST_ATE_Public extends WPML_TM_ATE_Required_Rest_Base {

	const CODE_UNPROCESSABLE_ENTITY = 422;
	const CODE_OK                   = 200;

	/**
	 * @var WPML_TM_ATE_Jobs
	 */
	private $jobs;
	/**
	 * @var WPML_TM_ATE_Jobs_Actions
	 */
	private $jobs_actions;

	/**
	 * @var TranslationManagement
	 */
	private $translation_management;

	/**
	 * @param WPML_TM_ATE_Jobs         $jobs
	 * @param WPML_TM_ATE_Jobs_Actions $jobs_actions
	 * @param TranslationManagement    $translation_management
	 */
	public function __construct(
		WPML_TM_ATE_Jobs $jobs,
		WPML_TM_ATE_Jobs_Actions $jobs_actions,
		TranslationManagement $translation_management
	) {
		parent::__construct();
		$this->jobs                   = $jobs;
		$this->jobs_actions           = $jobs_actions;
		$this->translation_management = $translation_management;
	}

	function add_hooks() {
		$this->register_routes();
	}

	function register_routes() {
		parent::register_route( '/ate/jobs/receive/(?P<ateJobId>\d+)',
		                        array(
			                        'methods'             => 'POST',
			                        'callback'            => array( $this, 'receive_ate_job' ),
			                        'args'                => array(
				                        'ateJobId' => array(
					                        'required'          => true,
					                        'type'              => 'int',
					                        'validate_callback' => array( 'WPML_REST_Arguments_Validation', 'integer' ),
					                        'sanitize_callback' => array( 'WPML_REST_Arguments_Sanitation', 'integer' ),
				                        ),
			                        ),
			                        'permission_callback' => '__return_true',
		                        ) );
	}

	public function get_allowed_capabilities( WP_REST_Request $request ) {
		return array();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function receive_ate_job( WP_REST_Request $request ) {
		$ate_job_id  = $request->get_param( 'ateJobId' );
		$wpml_job_id = $this->jobs->get_wpml_job_id( $ate_job_id );

		if ( ! $wpml_job_id ) {
			return new WP_Error( self::CODE_UNPROCESSABLE_ENTITY );
		}

		$wpml_job = $this->translation_management->get_translation_job( $wpml_job_id );

		if ( ! $wpml_job ) {
			return new WP_Error( self::CODE_UNPROCESSABLE_ENTITY );
		}

		$this->jobs_actions->update_jobs( false, array( $wpml_job ) );

		return new WP_REST_Response( null, self::CODE_OK );
	}
}
