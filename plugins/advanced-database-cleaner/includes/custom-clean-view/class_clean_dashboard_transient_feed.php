<?php

class ADBC_Clean_Dashboard_Transient_Feed extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_elements_to_display = array();

    /**
     * Constructor
     */
    function __construct(){

		$this->aDBc_message  = __('This feature is available in Pro version only.', 'advanced-database-cleaner');
		$this->aDBc_message .= " <a href='?page=advanced_db_cleaner&aDBc_tab=premium'>" . __('Please upgrade to pro version', 'advanced-database-cleaner') . "</a>";

        parent::__construct(array(
            'singular'  => __('Dashboard transient feed', 'advanced-database-cleaner'),	//singular name of the listed records
            'plural'    => __('Dashboard transients feed', 'advanced-database-cleaner'),	//plural name of the listed records
            'ajax'      => false													//does this table support ajax?
		));

		$this->aDBc_prepare_elements_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_elements_to_clean(){
		// Nothing to do!
		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){
		$columns = array(
			'cb'       					=> '<input type="checkbox" />',
			'dash_feed_option_id' 		=> __('Feed id','advanced-database-cleaner'),
			'dash_feed_option_name' 	=> __('Feed name','advanced-database-cleaner'),
			'dash_feed_autoload'   		=> __('Autoload','advanced-database-cleaner'),
			'site_id'   				=> __('Site id','advanced-database-cleaner')
		);
		return $columns;
	}

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'dash_feed_option_id':
			case 'dash_feed_option_name':
			case 'dash_feed_autoload':
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

	/** WP: Prepare items to display */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$per_page = 50;
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
		return sprintf('<input type="checkbox" name="aDBc_feed_to_clean[]" value="%s" />', $item['site_id']."|".$item['dash_feed_option_id']);
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
		_e('Available in Pro version!', 'advanced-database-cleaner');
	}

	/** WP: Process bulk actions */
    public function process_bulk_action() {
        // Nothing to do!
    }

	/** Print the page content */
	function aDBc_print_page_content(){
		// Print message
		echo '<div id="aDBc_message" class="aDBc-upgrade-msg notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
		?>
		<div class="aDBc-content-max-width">
			<div class="aDBc-float-left">
				<a style="text-decoration: none" href="?page=advanced_db_cleaner&aDBc_tab=general">
					<img src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/go_back.png'?>"/>
				</a>
			</div>
			<div class="aDBc-float-right aDBc-custom-clean-text">
				<?php echo __('Custom cleaning : <b>Dashboard transient feed</b>','advanced-database-cleaner'); ?>
			</div>
			<div>
				<form id="aDBc_form" action="" method="post">
					<?php
					// Print the elements to clean
					$this->display();
					?>
				</form>
			</div>
		</div>	
	<?php
	}
}

new ADBC_Clean_Dashboard_Transient_Feed();
?>