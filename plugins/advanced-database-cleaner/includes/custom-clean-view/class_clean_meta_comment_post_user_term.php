<?php
/** Used to view comment meta and post meta */
class ADBC_Clean_Meta_Comment_Post_User_Term extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_elements_to_display = array();
	private $aDBc_type_to_clean = "";
	private $aDBc_plural_title = "";
	private $aDBc_column_meta_name = "";	
	private $aDBc_sql_get_elements = "";
	private $aDBc_custom_sql_args = "";	
	private $aDBc_search_sql_arg = "";
	private $aDBc_order_by_sql_arg = "";
	private $aDBc_limit_offset_sql_arg = "";
	private $aDBc_delete_from_table = "";
	private $aDBc_metaid_or_umetaid = "";

    /**
     * Constructor
     */
    function __construct($element_type){

		if($element_type == "orphan-commentmeta"){

			$this->aDBc_type_to_clean 			= "orphan-commentmeta";
			$aDBc_singular 						= __('Orphaned comment meta', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Orphaned comments meta', 'advanced-database-cleaner');
			$this->aDBc_column_meta_name 		= __('Comment meta key', 'advanced-database-cleaner');
			$this->aDBc_delete_from_table 		= "commentmeta";
			$this->aDBc_metaid_or_umetaid		= "meta_id";

		}else if($element_type == "orphan-postmeta"){

			$this->aDBc_type_to_clean 			= "orphan-postmeta";
			$aDBc_singular 						= __('Orphaned post meta', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Orphaned posts meta', 'advanced-database-cleaner');
			$this->aDBc_column_meta_name 		= __('Post meta key', 'advanced-database-cleaner');
			$this->aDBc_delete_from_table 		= "postmeta";
			$this->aDBc_metaid_or_umetaid		= "meta_id";

		}else if($element_type == "orphan-usermeta"){

			$this->aDBc_type_to_clean 			= "orphan-usermeta";
			$aDBc_singular 						= __('Orphaned User Meta', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Orphaned Users Meta', 'advanced-database-cleaner');
			$this->aDBc_column_meta_name 		= __('User meta key', 'advanced-database-cleaner');
			$this->aDBc_delete_from_table 		= "usermeta";
			$this->aDBc_metaid_or_umetaid		= "umeta_id";

		}else if($element_type == "orphan-termmeta"){

			$this->aDBc_type_to_clean 			= "orphan-termmeta";
			$aDBc_singular 						= __('Orphaned Term Meta', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Orphaned Terms Meta', 'advanced-database-cleaner');
			$this->aDBc_column_meta_name 		= __('Term meta key', 'advanced-database-cleaner');
			$this->aDBc_delete_from_table 		= "termmeta";
			$this->aDBc_metaid_or_umetaid		= "meta_id";
		}

		// Prepare additional sql args if any: per page, LIMIT, OFFSET, etc.
		if(ADBC_PLUGIN_F_TYPE == "pro"){
			$this->aDBc_search_sql_arg 			= aDBc_get_search_sql_arg("meta_key", "meta_value");
		}
		$this->aDBc_order_by_sql_arg 			= aDBc_get_order_by_sql_arg($this->aDBc_metaid_or_umetaid);
		$this->aDBc_limit_offset_sql_arg 		= aDBc_get_limit_offset_sql_args();

        parent::__construct(array(
            'singular'  => $aDBc_singular,		//singular name of the listed records
            'plural'    => $this->aDBc_plural_title,	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		$this->aDBc_prepare_elements_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_elements_to_clean(){

		global $wpdb;

		// Process bulk action if any before preparing meta to clean
		$this->process_bulk_action();

		// Get all elements (for the table usermeta, only one table exists for MU, do not switch over blogs for it)
		if(function_exists('is_multisite') && is_multisite() && $this->aDBc_type_to_clean != "orphan-usermeta"){
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

		if($this->aDBc_type_to_clean == "orphan-commentmeta"){
			$this->aDBc_custom_sql_args 		= "SELECT meta_id, meta_key, meta_value FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)";
		}else if($this->aDBc_type_to_clean == "orphan-postmeta"){
			$this->aDBc_custom_sql_args 		= "SELECT meta_id, meta_key, meta_value FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";
		}else if($this->aDBc_type_to_clean == "orphan-usermeta"){
			$this->aDBc_custom_sql_args 		= "SELECT umeta_id, meta_key, meta_value FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT ID FROM $wpdb->users)";
		}else if($this->aDBc_type_to_clean == "orphan-termmeta"){
			$this->aDBc_custom_sql_args 		= "SELECT meta_id, meta_key, meta_value FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms)";
		}

		// Get all elements query
		$this->aDBc_sql_get_elements = $this->aDBc_custom_sql_args
		. $this->aDBc_search_sql_arg
		. $this->aDBc_order_by_sql_arg
		//. $this->aDBc_limit_offset_sql_arg
		;

		$aDBc_all_elements = $wpdb->get_results($this->aDBc_sql_get_elements);

		foreach($aDBc_all_elements as $aDBc_element){

			// Get meta key
			$meta_key = aDBc_create_tooltip_for_long_string($aDBc_element->meta_key, 28);

			// Get meta value
			$meta_value = aDBc_create_tooltip_for_long_string($aDBc_element->meta_value, 95);

			array_push($this->aDBc_elements_to_display, array(
				'meta_id' 		=> ($this->aDBc_metaid_or_umetaid == 'meta_id' ? $aDBc_element->meta_id : $aDBc_element->umeta_id),
				'meta_key' 		=> $meta_key,
				'meta_value' 	=> $meta_value,
				'site_id'		=> $blog_id
				)
			);

		}
	}

	/** WP: Get columns */
	function get_columns(){
		$columns = array(
			'cb'       		=> '<input type="checkbox" />',
			'meta_id' 		=> __('ID','advanced-database-cleaner'),
			'meta_key'   	=> $this->aDBc_column_meta_name,
			'meta_value'   	=> __('Meta value','advanced-database-cleaner'),
			'site_id'   	=> __('Site id','advanced-database-cleaner')
		);
		return $columns;
	}

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'meta_id':
			case 'meta_key':
			case 'meta_value':			
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
			'meta_id'   	=> array($this->aDBc_metaid_or_umetaid, false),
			'meta_key'    	=> array('meta_key', false)
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
		return sprintf('<input type="checkbox" name="aDBc_meta_to_clean[]" value="%s" />', $item['site_id']."|".$item['meta_id']);
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
        }
        $action = $this->current_action();
        if($action == 'clean'){
			// If the user wants to clean the elements he/she selected
			if(isset($_POST['aDBc_meta_to_clean'])){
				if(function_exists('is_multisite') && is_multisite()){
					// Prepare meta to delete
					$meta_to_delete = array();
					foreach($_POST['aDBc_meta_to_clean'] as $meta){
						$meta_info = explode("|", $meta);
						if(empty($meta_to_delete[$meta_info[0]])){
							$meta_to_delete[$meta_info[0]] = array();
						}
						array_push($meta_to_delete[$meta_info[0]], $meta_info[1]);
					}
					// Delete meta
					foreach($meta_to_delete as $site_id => $meta_ids){
						switch_to_blog($site_id);
						global $wpdb;
						foreach($meta_ids as $id_meta) {
							$table_name = $wpdb->prefix . $this->aDBc_delete_from_table;
							$wpdb->query("DELETE FROM $table_name WHERE $this->aDBc_metaid_or_umetaid = $id_meta");
						}
						restore_current_blog();
					}
				}else{
					global $wpdb;
					$table_name = $wpdb->prefix . $this->aDBc_delete_from_table;
					foreach($_POST['aDBc_meta_to_clean'] as $meta) {
						$meta_info = explode("|", $meta);
						$wpdb->query("DELETE FROM $table_name WHERE $this->aDBc_metaid_or_umetaid = " . $meta_info[1]);
					}
				}
				// Update the message to show to the user
				$this->aDBc_message = __("Selected '$this->aDBc_plural_title' successfully cleaned!", "advanced-database-cleaner");
			}
        }
    }

	/** Print the page content */
	function aDBc_print_page_content(){

		include_once 'page_custom_clean.php';
	}
}

?>