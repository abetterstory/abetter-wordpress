<?php
/** Used to view revisions, auto-drafts and trash posts */
class ADBC_Clean_Revision_Trash_Draft extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_elements_to_display = array();
	private $aDBc_type_to_clean = "";
	private $aDBc_plural_title = "";
	private $aDBc_column_post_name_title = "";
	private $aDBc_sql_get_elements = "";
	private $aDBc_custom_sql_args = "";
	private $aDBc_search_sql_arg = "";
	private $aDBc_order_by_sql_arg = "";
	private $aDBc_limit_offset_sql_arg = "";
	private $aDBc_keep_last_sql_arg = "";

    /**
     * Constructor
     */
    function __construct($element_type){

		if($element_type == "auto-draft"){

			$this->aDBc_type_to_clean 			= "auto-draft";
			$aDBc_singular 						= __('Auto draft', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Auto drafts', 'advanced-database-cleaner');
			$this->aDBc_column_post_name_title 	= __('Auto draft title', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " post_status = 'auto-draft'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('auto-draft');

		}else if($element_type == "trash-posts"){

			$this->aDBc_type_to_clean 			= "trash";
			$aDBc_singular 						= __('Trash post', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Trash posts', 'advanced-database-cleaner');
			$this->aDBc_column_post_name_title 	= __('Trash post title', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " post_status = 'trash'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('trash-posts');

		}else if($element_type == "revision"){

			$this->aDBc_type_to_clean 			= "revision";
			$aDBc_singular 						= __('Revision', 'advanced-database-cleaner');
			$this->aDBc_plural_title			= __('Revisions', 'advanced-database-cleaner');
			$this->aDBc_column_post_name_title 	= __('Revision title', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " post_type = 'revision'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('revision');

		}

		// Prepare additional sql args if any: per page, LIMIT, OFFSET, etc.
		if(ADBC_PLUGIN_F_TYPE == "pro"){
			$this->aDBc_search_sql_arg 				= aDBc_get_search_sql_arg("post_title", "post_content");
		}
		$this->aDBc_order_by_sql_arg 			= aDBc_get_order_by_sql_arg("ID");
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

		// Process bulk action if any before preparing posts to clean
		$this->process_bulk_action();

		// Get all concerned posts
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
		$this->aDBc_sql_get_elements = "SELECT ID, post_title, post_date, post_content FROM $wpdb->posts WHERE"
		. $this->aDBc_custom_sql_args
		. $this->aDBc_keep_last_sql_arg
		. $this->aDBc_search_sql_arg
		. $this->aDBc_order_by_sql_arg
		//. $this->aDBc_limit_offset_sql_arg
		;

		$aDBc_all_elements = $wpdb->get_results($this->aDBc_sql_get_elements);

		foreach($aDBc_all_elements as $aDBc_element){

			// Susbstr post title
			$post_title = aDBc_create_tooltip_for_long_string($aDBc_element->post_title, 35);

			// Get content
			$post_content = aDBc_create_tooltip_for_long_string($aDBc_element->post_content, 35);

			array_push($this->aDBc_elements_to_display, array(
				'post_id' 		=> $aDBc_element->ID,
				'post_title' 	=> $post_title,
				'post_content' 	=> $post_content,
				'post_date'		=> $aDBc_element->post_date,
				'site_id'		=> $blog_id
				)
			);
		}
	}

	/** Prepare keep_last element if any **/
	function aDBc_get_keep_last_sql_arg($element_type){
		$settings = get_option('aDBc_settings');
		if(!empty($settings['keep_last'])){
			$keep_setting = $settings['keep_last'];
			if(!empty($keep_setting[$element_type]))
				return  " and post_modified < NOW() - INTERVAL " . $keep_setting[$element_type] . " DAY";
		}
		return "";
	}
	
	/** WP: Get columns */
	function get_columns(){
		$columns = array(
			'cb'       		=> '<input type="checkbox" />',
			'post_id' 		=> __('ID','advanced-database-cleaner'),
			'post_title' 	=> $this->aDBc_column_post_name_title,
			'post_content' 	=> __('Content','advanced-database-cleaner'),
			'post_date'   	=> __('Date','advanced-database-cleaner'),
			'site_id'   	=> __('Site id','advanced-database-cleaner')
		);
		return $columns;
	}

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'post_id':
			case 'post_title':
			case 'post_content':
			case 'post_date':
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
			'post_id'   	=> array('ID',false),     //true means it's already sorted
			'post_title'    => array('post_title',false),
			'post_date'  	=> array('post_date',false)
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
		return sprintf('<input type="checkbox" name="aDBc_posts_to_clean[]" value="%s" />', $item['site_id']."|".$item['post_id']);
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
			if(isset($_POST['aDBc_posts_to_clean'])){
				if(function_exists('is_multisite') && is_multisite()){
					// Prepare posts to delete
					$posts_to_delete = array();
					foreach($_POST['aDBc_posts_to_clean'] as $post){
						$post_info = explode("|", $post);
						if(empty($posts_to_delete[$post_info[0]])){
							$posts_to_delete[$post_info[0]] = array();
						}
						array_push($posts_to_delete[$post_info[0]], $post_info[1]);
					}
					// Delete posts
					foreach($posts_to_delete as $site_id => $posts_ids){
						switch_to_blog($site_id);
						global $wpdb;
						foreach($posts_ids as $id_post) {
							$wpdb->query("DELETE FROM $wpdb->posts WHERE ID = $id_post");
						}
						restore_current_blog();
					}
				}else{
					global $wpdb;
					foreach($_POST['aDBc_posts_to_clean'] as $post) {
						$post_info = explode("|", $post);
						$wpdb->query("DELETE FROM $wpdb->posts WHERE ID = " . $post_info[1]);
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