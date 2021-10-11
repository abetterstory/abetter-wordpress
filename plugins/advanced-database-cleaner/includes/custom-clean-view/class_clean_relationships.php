<?php

class ADBC_Clean_Relationship extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_elements_to_display = array();
	private $aDBc_type_to_clean = "";
	private $aDBc_plural_title = "";
	private $aDBc_column_meta_name = "";	
	private $aDBc_sql_get_elements = "";
	private $aDBc_search_sql_arg = "";
	private $aDBc_order_by_sql_arg = "";
	private $aDBc_limit_offset_sql_arg = "";
	private $aDBc_delete_from_table = "";

    /**
     * Constructor
     */
    function __construct(){

		$this->aDBc_plural_title = __('Orphaned Relationships', 'advanced-database-cleaner');

		// Prepare additional sql args if any: per page, LIMIT, OFFSET, etc.
		$this->aDBc_order_by_sql_arg 			= aDBc_get_order_by_sql_arg("object_id");
		$this->aDBc_limit_offset_sql_arg 		= aDBc_get_limit_offset_sql_args();

        parent::__construct(array(
            'singular'  => __('Orphaned Relationship', 'advanced-database-cleaner'),		//singular name of the listed records
            'plural'    => $this->aDBc_plural_title,	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		$this->aDBc_prepare_elements_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_elements_to_clean(){

		global $wpdb;

		// Process bulk action if any before preparing relationships to clean
		$this->process_bulk_action();

		// Get all relationships
		if(function_exists('is_multisite') && is_multisite()){
			$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach($blogs_ids as $blog_id){

				switch_to_blog($blog_id);

				$this->aDBc_fill_array_elements_to_clean($blog_id);

				restore_current_blog();

			}
		}else{

			$this->aDBc_fill_array_elements_to_clean("1");

		}
		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** Fill array elements to display */
	function aDBc_fill_array_elements_to_clean($blog_id){

		global $wpdb;

		// Get all elements query
		$this->aDBc_sql_get_elements = "SELECT * from $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)"
		. $this->aDBc_search_sql_arg
		. $this->aDBc_order_by_sql_arg
		//. $this->aDBc_limit_offset_sql_arg
		;

		$aDBc_all_elements = $wpdb->get_results($this->aDBc_sql_get_elements);

		foreach($aDBc_all_elements as $aDBc_element){

			array_push($this->aDBc_elements_to_display, array(
				'object_id' 		=> $aDBc_element->object_id,
				'term_taxonomy_id' 	=> $aDBc_element->term_taxonomy_id,
				'term_order' 		=> $aDBc_element->term_order,
				'site_id'			=> $blog_id
				)
			);

		}
	}

	/** WP: Get columns */
	function get_columns(){
		$columns = array(
			'cb'       			=> '<input type="checkbox" />',
			'object_id' 		=> __('ID','advanced-database-cleaner'),
			'term_taxonomy_id' 	=> __('Term taxonomy id','advanced-database-cleaner'),
			'term_order'   		=> __('Term order','advanced-database-cleaner'),
			'site_id'   		=> __('Site id','advanced-database-cleaner')
		);
		return $columns;
	}

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'object_id':
			case 'term_taxonomy_id':
			case 'term_order':
			case 'site_id':
				return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Get columns that should be hidden */
    function get_hidden_columns(){
		// If MU, nothing to hide, else hide Side ID column
		if(function_exists('is_multisite') && is_multisite()){
			return array();
		}else{
			return array('site_id');
		}
    }

	function get_sortable_columns() {

		$sortable_columns = array(
			'object_id'   		=> array('object_id',false),
			'term_taxonomy_id'  => array('term_taxonomy_id',false),
			'term_order'    	=> array('term_order',false)
		);
		// Since order_by works directly with sql request, we will not order_by in mutlisite since it will not work
		if(function_exists('is_multisite') && is_multisite()){
			return array();
		}else{
			return $sortable_columns;
		}
	}

	/** WP: Prepare items to display */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$per_page = 50;
		if(!empty($_GET['per_page'])){
			$per_page = absint($_GET['per_page']);
		}

		$current_page = $this->get_pagenum();
		// Prepare sequence of elements to display
		$display_data = array_slice($this->aDBc_elements_to_display,(($current_page-1) * $per_page), $per_page);
		$this->set_pagination_args( array(
			'total_items' => count($this->aDBc_elements_to_display),
			'per_page'    => $per_page
		));
		$this->items = $display_data;
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input type="checkbox" name="aDBc_elements_to_process[]" value="%s" />', $item['site_id']."|".$item['object_id']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		$actions = array(
			'clean'    => __('Clean','advanced-database-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('No elements found!','advanced-database-cleaner');
	}

	/** WP: Process bulk actions */
    public function process_bulk_action() {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])){
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            if (!wp_verify_nonce( $nonce, $action))
                wp_die('Security check failed!');
        }else{
			// If $_POST['_wpnonce'] is not set, return
			return;
		}

		// Check role
		if(!current_user_can('administrator'))
			wp_die('Security check failed!');

        $action = $this->current_action();

        if($action == 'clean'){
			// If the user wants to clean the elements he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){
				if(function_exists('is_multisite') && is_multisite()){
					// Prepare relationships to delete
					$relationships_to_delete = array();
					foreach($_POST['aDBc_elements_to_process'] as $relationship){
						$relationship_info 	= explode("|", $relationship);
						$sanitized_site_id 	= sanitize_html_class($relationship_info[0]);
						$sanitized_item_id 	= sanitize_html_class($relationship_info[1]);
						// For security, we only proceed if both parts are clean and are numbers
						if(is_numeric($sanitized_site_id) && is_numeric($sanitized_item_id)){
							if(empty($relationships_to_delete[$sanitized_site_id])){
								$relationships_to_delete[$sanitized_site_id] = array();
							}
							array_push($relationships_to_delete[$sanitized_site_id], $sanitized_item_id);
						}
					}
					// Delete relationships
					foreach($relationships_to_delete as $site_id => $object_ids){
						switch_to_blog($site_id);
						global $wpdb;
						foreach($object_ids as $object_id) {
							$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id = $object_id");
						}
						restore_current_blog();
					}
				}else{
					global $wpdb;
					foreach($_POST['aDBc_elements_to_process'] as $relationship){
						$relationship_info 	= explode("|", $relationship);
						$sanitized_id 		= sanitize_html_class($relationship_info[1]);
						if(is_numeric($sanitized_id)){
							$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id = " . $sanitized_id);
						}
					}
				}
				// Update the message to show to the user
				$this->aDBc_message = __("Selected 'Orphaned relationships' successfully cleaned!", "advanced-database-cleaner");
			}
        }
    }

	/** Print the page content */
	function aDBc_print_page_content(){

		include_once 'page_custom_clean.php';
	}
}

?>