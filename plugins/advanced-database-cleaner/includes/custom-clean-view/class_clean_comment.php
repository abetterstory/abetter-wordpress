<?php
/** Used to view Pending comments, Spam Comments, Trash comments, Pingbacks and Trackbacks */
class ADBC_Clean_Comment extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_elements_to_display = array();
	private $aDBc_type_to_clean = "";
	private $aDBc_plural_title = "";
	private $aDBc_column_comment_name = "";	
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

		if($element_type == "moderated-comments"){

			$this->aDBc_type_to_clean 			= "0";
			$aDBc_singular 						= __('Pending comment', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Pending comments', 'advanced-database-cleaner');
			$this->aDBc_column_comment_name 	= __('Comment content', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " comment_approved = '0'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('moderated-comments');

		}else if($element_type == "spam-comments"){

			$this->aDBc_type_to_clean 			= "spam";
			$aDBc_singular 						= __('Spam comment', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Spam comments', 'advanced-database-cleaner');
			$this->aDBc_column_comment_name 	= __('Comment content', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " comment_approved = 'spam'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('spam-comments');

		}else if($element_type == "trash-comments"){

			$this->aDBc_type_to_clean 			= "trash";
			$aDBc_singular 						= __('Trash comment', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Trash comments', 'advanced-database-cleaner');
			$this->aDBc_column_comment_name 	= __('Comment content', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " comment_approved = 'trash'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('trash-comments');

		}else if($element_type == "pingbacks"){

			$this->aDBc_type_to_clean 			= "pingback";
			$aDBc_singular 						= __('Pingback', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Pingbacks', 'advanced-database-cleaner');
			$this->aDBc_column_comment_name 	= __('Pingback content', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " comment_type = 'pingback'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('pingbacks');

		}else if($element_type == "trackbacks"){

			$this->aDBc_type_to_clean 			= "trackback";
			$aDBc_singular 						= __('Trackback', 'advanced-database-cleaner');
			$this->aDBc_plural_title 			= __('Trackbacks', 'advanced-database-cleaner');
			$this->aDBc_column_comment_name 	= __('Trackback content', 'advanced-database-cleaner');
			$this->aDBc_custom_sql_args 		= " comment_type = 'trackback'";
			$this->aDBc_keep_last_sql_arg 		= $this->aDBc_get_keep_last_sql_arg('trackbacks');

		}

		// Prepare additional sql args if any: per page, LIMIT, OFFSET, etc.
		$this->aDBc_order_by_sql_arg 			= aDBc_get_order_by_sql_arg("comment_ID");
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

		// Process bulk action if any before preparing elements to clean
		$this->process_bulk_action();

		// Get all elements to clean
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
		$this->aDBc_sql_get_elements = "SELECT comment_ID, comment_author, comment_content, comment_date FROM $wpdb->comments WHERE"
		. $this->aDBc_custom_sql_args
		. $this->aDBc_keep_last_sql_arg
		. $this->aDBc_search_sql_arg
		. $this->aDBc_order_by_sql_arg
		//. $this->aDBc_limit_offset_sql_arg
		;

		$aDBc_all_elements = $wpdb->get_results($this->aDBc_sql_get_elements);

		foreach($aDBc_all_elements as $aDBc_element){

			// Get author name
			$author_name = aDBc_create_tooltip_for_long_string($aDBc_element->comment_author, 16);

			// Get comment content
			$comment_content = aDBc_create_tooltip_for_long_string($aDBc_element->comment_content, 60);

			array_push($this->aDBc_elements_to_display, array(
				'comment_id' 		=> $aDBc_element->comment_ID,
				'comment_author' 	=> $author_name,
				'comment_content' 	=> $comment_content,
				'comment_date'		=> $aDBc_element->comment_date,
				'site_id'			=> $blog_id
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
				return  " and comment_date < NOW() - INTERVAL " . $keep_setting[$element_type] . " DAY";
		}
		return "";
	}

	/** WP: Get columns */
	function get_columns(){

		$columns = array(
			'cb'       			=> '<input type="checkbox" />',
			'comment_id' 		=> __('ID','advanced-database-cleaner'),
			'comment_author' 	=> __('Author','advanced-database-cleaner'),
			'comment_content'   => $this->aDBc_column_comment_name,
			'comment_date'   	=> __('Date','advanced-database-cleaner'),
			'site_id'   		=> __('Site id','advanced-database-cleaner')
		);
		return $columns;
	}

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'comment_id':
			case 'comment_author':
			case 'comment_content':
			case 'comment_date':
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
			'comment_id'   	=> array('comment_ID',false),
			'comment_author'    => array('comment_author',false)
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
		return sprintf('<input type="checkbox" name="aDBc_elements_to_process[]" value="%s" />', $item['site_id']."|".$item['comment_id']);
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
					// Prepare elements to delete
					$elements_to_delete = array();
					foreach($_POST['aDBc_elements_to_process'] as $element){
						$element_info 		= explode("|", $element);
						$sanitized_site_id 	= sanitize_html_class($element_info[0]);
						$sanitized_item_id 	= sanitize_html_class($element_info[1]);
						// For security, we only proceed if both parts are clean and are numbers
						if(is_numeric($sanitized_site_id) && is_numeric($sanitized_item_id)){
							if(empty($elements_to_delete[$sanitized_site_id])){
								$elements_to_delete[$sanitized_site_id] = array();
							}
							array_push($elements_to_delete[$sanitized_site_id], $sanitized_item_id);
						}
					}
					// Delete elements
					foreach($elements_to_delete as $site_id => $elements_ids){
						switch_to_blog($site_id);
						global $wpdb;
						foreach($elements_ids as $id_comment) {
							$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_ID = $id_comment");
						}
						restore_current_blog();
					}
				}else{
					global $wpdb;
					foreach($_POST['aDBc_elements_to_process'] as $element) {
						$element_info = explode("|", $element);
						$sanitized_id = sanitize_html_class($element_info[1]);
						if(is_numeric($sanitized_id)){
							$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_ID = " . $sanitized_id);
						}
					}
				}
				// Update the message to show to the user
				$this->aDBc_message = __("Selected '$this->aDBc_plural_title' successfully cleaned!", 'advanced-database-cleaner');
			}
        }
    }

	/** Print the page content */
	function aDBc_print_page_content(){

		include_once 'page_custom_clean.php';
	}
}
?>