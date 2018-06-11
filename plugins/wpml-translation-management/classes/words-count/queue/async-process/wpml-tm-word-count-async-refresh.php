<?php

class WPML_TM_Word_Count_Async_Refresh extends WP_Async_Request {

	protected $action = 'wpml_word_count_refresh';

	/** @var WPML_TM_Word_Count_Single_Process $single_process */
	private $single_process;

	public function __construct( WPML_TM_Word_Count_Single_Process $single_process ) {
		$this->single_process = $single_process;
		parent::__construct();
	}

	protected function handle() {
		if ( ! isset( $_POST['element_type'], $_POST['element_id'] ) ) {
			return;
		}

		$this->single_process->process( $_POST['element_type'], $_POST['element_id'] );
	}
}
