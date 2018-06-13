<?php

class WPML_TM_ATE_Job_Records {

	const DELIVERING_JOB_STATUS = WPML_TM_ATE_AMS_Endpoints::ATE_JOB_STATUS_DELIVERING;
	const WPML_TM_ATE_JOB_RECORDS = 'WPML_TM_ATE_JOBS';

	private $ate_jobs = array();
	private $ate_statuses;
	private $data = array();
	private $wpml_ate_status_translations;

	public function __construct() {
		$this->ate_statuses                 = array(
			0 => 'created',
			1 => 'translating',
			6 => 'translated',
			7 => 'delivering',
			8 => 'delivered',
		);
		$this->wpml_ate_status_translations = array(
			0 => ICL_TM_WAITING_FOR_TRANSLATOR,
			1 => ICL_TM_IN_PROGRESS,
			6 => ICL_TM_COMPLETE,
			7 => ICL_TM_COMPLETE,
			8 => ICL_TM_COMPLETE,
		);
	}

	public function get_ate_job_progress( $wpml_job_id ) {
		return $this->get_ate_job_field( $wpml_job_id, 'progress_details' );
	}

	public function get_ate_job_status( $wpml_job_id ) {
		$status = null;

		$ate_status = (int) $this->get_ate_job_field( $wpml_job_id, 'status_id' );

		if ( array_key_exists( $ate_status, $this->ate_statuses ) ) {
			$status = $this->wpml_ate_status_translations[ $ate_status ];
		}

		return $status;
	}

	public function get_ate_job_url( $wpml_job_id ) {
		return $this->get_ate_job_field( $wpml_job_id, 'ateJobUrl' );
	}

	public function get_translated_xliff( $wpml_job_id ) {
		return $this->get_ate_job_field( $wpml_job_id, 'translated_xliff_download' );
	}

	public function is_ate_job_delivered( $wpml_job_id ) {
		$ate_status = (int) $this->get_ate_job_field( $wpml_job_id, 'status_id' );

		return $ate_status >= self::DELIVERING_JOB_STATUS;
	}

	public function set_ate_job_field( $wpml_job_id, $field_name, $field_value ) {
		$job_id = (int) $wpml_job_id;

		$this->read_option_value();

		$data = $this->data[ $job_id ];
		if ( ! array_key_exists( $job_id, $this->data ) ) {
			$data = array();
		}
		$data[ $field_name ] = $field_value;
		$this->store( $wpml_job_id, $data );
	}

	public function update_ate_job_data( $ate_job_id, $job_data ) {
		$ate_job_id           = (int) $ate_job_id;
		$data_from_ate_job_id = $this->get_data_from_ate_job_id( $ate_job_id );
		$job_data             = array_merge( $data_from_ate_job_id['ate_job_data'], $job_data );
		$this->store( $data_from_ate_job_id['wpml_job_id'], $job_data );
	}

	public function get_data_from_ate_job_id( $ate_job_id ) {
		$ate_job_id = (int) $ate_job_id;
		if ( ! array_key_exists( $ate_job_id, $this->ate_jobs ) ) {
			$this->read_option_value();
			foreach ( $this->data as $job_id => $job_data ) {
				$this->ate_jobs[ $ate_job_id ] = array(
					'wpml_job_id'  => $job_id,
					'ate_job_data' => $job_data
				);
			}
		}

		if ( array_key_exists( $ate_job_id, $this->ate_jobs ) ) {
			return $this->ate_jobs[ $ate_job_id ];
		}

		return null;
	}

	public function store( $wpml_job_id, array $ate_job_data ) {
		$wpml_job_id = (int) $wpml_job_id;
		$this->read_option_value();
		if ( isset( $this->data[ $wpml_job_id ] ) ) {
			$this->data[ $wpml_job_id ] = array_merge( $this->data[ $wpml_job_id ], $ate_job_data );
		} else {
			$this->data[ $wpml_job_id ] = $ate_job_data;
		}

		if ( array_key_exists( 'translated_xliff', $ate_job_data ) && $ate_job_data['translated_xliff'] ) {
			$response = wp_remote_get( $ate_job_data['translated_xliff'] );
			if ( 200 === (int) $response['response']['code'] ) {
				$this->data[ $wpml_job_id ]['translated_xliff_download'] = $response['body'];
			}
		}
		update_option( self::WPML_TM_ATE_JOB_RECORDS, $this->data );
	}

	private function read_option_value() {
		$this->data = get_option( self::WPML_TM_ATE_JOB_RECORDS, array() );

		return $this->data;
	}

	/**
	 * @param int $wpml_job_id
	 *
	 * @return int
	 */
	public function get_ate_job_id( $wpml_job_id ) {
		return $this->get_ate_job_field( $wpml_job_id, 'ateJobId' );
	}

	private function get_ate_job_field( $wpml_job_id, $field_name ) {
		$this->read_option_value();

		$job_id = (int) $wpml_job_id;
		if ( ! array_key_exists( $job_id, $this->data ) ) {
			$this->data[ $job_id ] = array();
		}

		if ( isset( $this->data[ $job_id ][ $field_name ] ) ) {
			return $this->data[ $job_id ][ $field_name ];
		}

		return '';
	}

}
