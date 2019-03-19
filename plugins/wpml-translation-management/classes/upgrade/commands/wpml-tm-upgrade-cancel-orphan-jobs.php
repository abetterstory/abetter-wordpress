<?php

class WPML_TM_Upgrade_Cancel_Orphan_Jobs implements IWPML_Upgrade_Command {
	/** @var WPML_TP_Sync_Orphan_Jobs_Factory */
	private $factory;

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		if ( ! isset( $args[0] ) || ! $args[0] instanceof WPML_TP_Sync_Orphan_Jobs_Factory ) {
			throw new InvalidArgumentException( 'The factory class must be passed as an argument in the constructor' );
		}

		$this->factory = $args[0];
	}

	/**
	 * @return bool
	 */
	public function run_admin() {
		if ( ! WPML_Translation_Jobs_Migration::is_migrated() ) {
			return false;
		}

		$this->factory->create()->cancel_orphans();

		return true;
	}

	/**
	 * @return null
	 */
	public function run_ajax() {
		return null;
	}

	/**
	 * @return null
	 */
	public function run_frontend() {
		return null;
	}

	/**
	 * @return null
	 */
	public function get_results() {
		return null;
	}
}