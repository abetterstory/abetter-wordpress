<?php

class WPML_Upgrade_Add_Editor_Column_To_Icl_Translate_Job implements IWPML_Upgrade_Command {

	/** @var WPML_Upgrade_Schema */
	private $upgrade_schema;

	public function __construct( array $args ) {
		$this->upgrade_schema = $args[0];
	}

	/** @return bool */
	private function run() {
		$table  = 'icl_translate_job';
		$column = 'editor';

		if ( $this->upgrade_schema->does_table_exist( $table ) ) {
			if ( ! $this->upgrade_schema->does_column_exist( $table, $column ) ) {
				$this->upgrade_schema->add_column( $table, $column, 'VARCHAR(16) NULL' );
			}
		}

		return true;
	}

	public function run_admin() {
		return $this->run();
	}

	public function run_ajax() {
		return $this->run();
	}

	public function run_frontend() {
		return $this->run();
	}

	/** @return bool */
	public function get_results() {
		return true;
	}
}
