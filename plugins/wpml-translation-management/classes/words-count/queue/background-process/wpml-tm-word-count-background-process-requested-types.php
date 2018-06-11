<?php

class WPML_TM_Word_Count_Background_Process_Requested_Types extends WPML_TM_Word_Count_Background_Process {

	/** @var WPML_TM_Word_Count_Queue_Items_Requested_Types $queue */
	protected $queue;

	/** @var WPML_TM_Word_Count_Records $records */
	private $records;

	/**
	 * @param WPML_TM_Word_Count_Queue_Items_Requested_Types $queue_items
	 * @param IWPML_TM_Word_Count_Set[]       $setters
	 */
	public function __construct(
		WPML_TM_Word_Count_Queue_Items_Requested_Types $queue_items,
		array $setters,
		WPML_TM_Word_Count_Records $records
	) {
		/** We need to set the action before constructing the parent class `WP_Async_Request` */
		$this->action = WPML_TM_Word_Count_Background_Process_Factory::ACTION_REQUESTED_TYPES;
		parent::__construct( $queue_items, $setters );
		$this->records = $records;
	}

	public function init( $requested_types ) {
		$this->queue->reset( $requested_types );
		$this->records->reset_all( $requested_types );
		$this->dispatch();
	}

	public function dispatch() {
		update_option(
			WPML_TM_Word_Count_Hooks_Factory::OPTION_KEY_REQUESTED_TYPES_STATUS,
			WPML_TM_Word_Count_Hooks_Factory::PROCESS_IN_PROGRESS
		);

		parent::dispatch();
	}

	public function complete() {
		update_option(
			WPML_TM_Word_Count_Hooks_Factory::OPTION_KEY_REQUESTED_TYPES_STATUS,
			WPML_TM_Word_Count_Hooks_Factory::PROCESS_COMPLETED
		);

		parent::complete();
	}
}