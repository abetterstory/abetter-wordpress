<?php
if ( filter_input( INPUT_GET, 'sm', FILTER_SANITIZE_STRING ) === 'basket' ) {
    add_action( 'admin_enqueue_scripts', array( 'SitePress_Table_Basket', 'enqueue_js' ) );
}

class WPML_TM_Menus {
	/** @var IWPML_Template_Service $template_service */
	private $template_service;

    private $active_languages;
	private $logger_ui;
	private $translatable_types;
    private $current_document_words_count;
    private $current_language;
    private $filter_post_status;
    private $filter_translation_type;
    private $messages = array();

    private $post_statuses;
    private $post_types;
    private $selected_languages;
    private $source_language;

    private $tab_items;

    private $base_target_url;

    private $current_shown_item;

    private $dashboard_title_sort_link;

    private $dashboard_date_sort_link;

    private $documents;

    private $selected_posts = array();
    private $translation_filter;

    private $found_documents;

    /** @var  WPML_UI_Screen_Options_Pagination $dashboard_pagination */
    private $dashboard_pagination;

	function __construct( IWPML_Template_Service $template_service ) {
		$this->template_service             = $template_service;

		$this->odd_row                      = false;
		$this->current_document_words_count = 0;
		$this->current_shown_item           = isset( $_GET[ 'sm' ] ) ? $_GET[ 'sm' ] : 'dashboard';
		$this->base_target_url              = dirname( __FILE__ );
		$logger_settings                    = new WPML_Jobs_Fetch_Log_Settings();
		$wpml_wp_api                        = new WPML_WP_API();
		$this->logger_ui                    = new WPML_Jobs_Fetch_Log_UI( $logger_settings, $wpml_wp_api );
	}

	public function display_main( WPML_UI_Screen_Options_Pagination $dashboard_pagination = null ) {
		$this->dashboard_pagination = $dashboard_pagination;
		if ( true !== apply_filters( 'wpml_tm_lock_ui', false ) ) {
			$this->render_main();
		}
	}

    private function render_main()
    {
        ?>
        <div class="wrap">
            <h2><?php echo esc_html__('Translation Management', 'wpml-translation-management') ?></h2>

            <?php do_action('icl_tm_messages');

            $this->implode_messages();

            $this->build_tab_items();

            $this->render_items();
            ?>
        </div>
    <?php

    }

    private function implode_messages()
    {
        if ($this->messages) {
            echo implode('', $this->messages);
        }
    }

    private function build_tab_item_target_url($target)
    {
        return $this->base_target_url . $target;
    }

	private function build_tab_items() {
		$this->tab_items = array();

		$this->build_dashboard_item();
		$this->build_translators_item();

		foreach( $this->get_admin_section_factories() as $factory ) {
			if ( in_array( 'IWPML_TM_Admin_Section_Factory', class_implements( $factory ), true ) ) {
				$sections_factory = new $factory;
				$section = $sections_factory->create();
				if ( in_array( 'IWPML_TM_Admin_Section', class_implements( $section ), true ) && $section->is_visible() ) {
					$this->tab_items[ $section->get_slug() ] = array(
						'caption' => $section->get_caption(),
						'current_user_can' => $section->get_capability(),
						'callback' => $section->get_callback(),
					);
				}
			}
		}

		$this->build_basket_item();
		$this->build_translation_jobs_item();
		$this->build_mcs_item();
		$this->build_translation_notifications_item();
		$this->build_tp_com_log_item();
		$this->build_tp_pickup_log_item();

		$this->tab_items = apply_filters( 'wpml_tm_tab_items', $this->tab_items );
	}

	/**
	 * @return array
	 */
	private function get_admin_section_factories() {
		$admin_sections_factories = array(
			'WPML_TM_Translation_Services_Admin_Section_Factory',
		);

		return apply_filters( 'wpml_tm_admin_sections_factories', $admin_sections_factories );
	}

	/**
	 * @param int $basket_items_count
	 *
	 * @return string
	 */
    private function build_basket_item_caption( $basket_items_count = 0 )
    {

		if ( isset( $_GET[ 'clear_basket' ] ) && $_GET[ 'clear_basket' ] ) {
            $basket_items_count = 0;
        } else {

			if (! is_numeric( $basket_items_count )) {
				$basket_items_count = TranslationProxy_Basket::get_basket_items_count( true );
			}
            if ( isset( $_GET[ 'action' ], $_GET[ 'id' ] ) && $_GET[ 'action' ] === 'delete' && $_GET[ 'id' ] ) {
                -- $basket_items_count;
            }
        }
        $basket_items_count_caption = esc_html__('Translation Basket', 'wpml-translation-management');
        if ($basket_items_count > 0) {
            $basket_item_count_badge = '<span id="wpml-basket-items"><span id="basket-item-count">' . $basket_items_count . '</span></span>';
            $basket_items_count_caption .= $basket_item_count_badge;
        }
        return $basket_items_count_caption;

    }

	/**
	 * @return bool
	 */
	private function can_display_translation_services() {
		global $sitepress;

		return ( defined( 'WPML_BYPASS_TS_CHECK' ) && WPML_BYPASS_TS_CHECK )
			   || ! $sitepress->get_setting( 'translation_service_plugin_activated' );
	}

    private function build_translation_notifications_item()
    {
        $this->tab_items['notifications']['caption'] = __('Translation Notifications', 'wpml-translation-management');
        //$this->tab_items['notifications']['target'] = $this->build_tab_item_target_url('/sub/notifications.php');
        $this->tab_items['notifications']['callback'] = array($this, 'build_content_translation_notifications');
    }

    private function build_mcs_item()
    {
	    global $sitepress;

        $this->tab_items['mcsetup']['caption'] = __('Multilingual Content Setup', 'wpml-translation-management');
	    $translate_link_targets = new WPML_Translate_Link_Target_Global_State( $sitepress );
	    if ( $translate_link_targets->is_rescan_required() ) {
		    $this->tab_items['mcsetup']['caption'] = '<i class="otgs-ico-warning"></i>' . esc_html( $this->tab_items['mcsetup']['caption'] );
	    }
        $this->tab_items['mcsetup']['callback'] = array($this, 'build_content_mcs');
    }

    private function build_translation_jobs_item()
    {
        $this->tab_items['jobs']['caption'] = __('Translation Jobs', 'wpml-translation-management');
        $this->tab_items['jobs']['callback'] = array($this, 'build_content_translation_jobs');
    }

    private function build_basket_item()
    {
	    $basket_items_count = TranslationProxy_Basket::get_basket_items_count( true );

        if ( $basket_items_count > 0 ) {

            $this->tab_items['basket']['caption'] = $this->build_basket_item_caption( $basket_items_count );
            $this->tab_items['basket']['callback'] = array($this, 'build_content_basket');

        }
    }

    private function build_translators_item()
    {
        $this->tab_items['translators']['caption'] = __('Translators', 'wpml-translation-management');
        $this->tab_items['translators']['current_user_can'] = 'list_users';
        $this->tab_items['translators']['callback'] = array($this, 'build_content_translators');
    }

    private function build_dashboard_item()
    {
        $this->tab_items['dashboard']['caption'] = __('Translation Dashboard', 'wpml-translation-management');
        $this->tab_items['dashboard']['callback'] = array($this, 'build_content_dashboard');
    }

    /**
     * @return string
     */
    private function get_current_shown_item()
    {
        return $this->current_shown_item;
    }

    /**
     * @return array
     */
    private function build_tabs()
    {
        $tm_sub_menu = $this->get_current_shown_item();
        foreach ($this->tab_items as $id => $tab_item) {
            if (!isset($tab_item['caption'])) {
                continue;
            }
            if (!isset($tab_item['target']) && !isset($tab_item['callback'])) {
                continue;
            }

            $caption = $tab_item['caption'];
            $current_user_can = isset($tab_item['current_user_can']) ? $tab_item['current_user_can'] : false;

            if ($current_user_can && !current_user_can($current_user_can)) {
                continue;
            }

            $classes = array(
                'nav-tab'
            );
            if ($tm_sub_menu == $id) {
                $classes[] = 'nav-tab-active';
            }

            $class = implode(' ', $classes);
            $href = 'admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=' . $id;
            ?>
            <a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_attr( $href ); ?>">
                <?php echo $caption; ?>
            </a>
        <?php
        }
    }

    private function build_content() {
        $tm_sub_menu = $this->get_current_shown_item();
        foreach ($this->tab_items as $id => $tab_item) {
            if (!isset($tab_item['caption'])) {
                continue;
            }
            if (!isset($tab_item['target']) && !isset($tab_item['callback'])) {
                continue;
            }

            if ($tm_sub_menu == $id) {
                if (isset($tab_item['target'])) {
                    $target = $tab_item['target'];
                    /** @noinspection PhpIncludeInspection */
                    include_once $this->build_tab_item_target_url($target);
                }
                if (isset($tab_item['callback'])) {
                    $callback = $tab_item['callback'];
                    call_user_func($callback);
                }
            }
        }
        do_action('icl_tm_menu_' . $tm_sub_menu);
    }

	public function build_content_dashboard() {
		/** @var SitePress $sitepress */
		global $sitepress;
		$this->active_languages   = $sitepress->get_active_languages();
		$this->translatable_types = apply_filters( 'wpml_tm_dashboard_translatable_types', $sitepress->get_translatable_documents() );
		$this->build_dashboard_data();

		$this->build_content_dashboard_remote_translations_controls();
		$this->build_content_dashboard_filter();
		$this->build_content_dashboard_results();
	}

	public function build_content_translators() {
		global $iclTranslationManagement, $wpdb, $sitepress;

		$tp_client_factory = new WPML_TP_Client_Factory();
		$tp_client         = $tp_client_factory->create();

		$translator_settings = new WPML_Translator_Settings( $wpdb, $sitepress, $iclTranslationManagement, $tp_client );

		$translator_settings->build_content_translators();

		$can_see_translation_services = ! defined( 'ICL_HIDE_TRANSLATION_SERVICES' ) || ! ICL_HIDE_TRANSLATION_SERVICES;
		if ( $can_see_translation_services && $this->can_display_translation_services()
		     && $translator_settings->translation_service_has_translators()
		     && ( $this->site_key_exist() || $this->is_any_translation_service_active() ) ) {
			$translator_settings->build_website_details_refresh();
		}
	}

	private function site_key_exist() {
		if ( class_exists( 'WP_Installer' ) ) {
			$repository_id = 'wpml';
			$site_key      = WP_Installer()->get_site_key( $repository_id );
		}

		return $does_exist = ( $site_key !== false ? true : false );
	}

	private function is_any_translation_service_active(){

		$is_active = TranslationProxy::get_current_service();

	return $feedback = ( $is_active !== false ? true : false );
	}

	private function build_link_to_register_plugin(){

		$link = sprintf( '<a class="button-secondary" href="%s">' . esc_html__( 'Please register WPML to enable the professional translation option', 'wpml-translation-management') . '</a>',
						admin_url('plugin-install.php?tab=commercial#repository-wpml') );

	return $link;
	}

	public function build_content_basket() {
		$basket_table = new SitePress_Table_Basket();
		$basket_table->prepare_items();

		$action_url = esc_attr( 'admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=' . $_GET[ 'sm' ] );

		?>
		<h3>1. <?php echo esc_html__( 'Review documents for translation', 'wpml-translation-management' ) ?></h3>

		<form method="post" id="translation-jobs-basket-form" class="js-translation-jobs-basket-form"
		      data-message="<?php echo esc_attr__( 'You are about to delete selected items from the basket. Are you sure you want to do that?',
		                              'wpml-translation-management' ) ?>"
		      name="translation-jobs-basket" action="<?php echo $action_url; ?>">
			<?php
			$basket_table->display();
			?>
		</form>
		<?php
		$this->build_translation_options();
	}

    private function build_translation_options() {
        global $sitepress, $wpdb;
        $basket_items_number = TranslationProxy_Basket::get_basket_items_count( true );

        if ( $basket_items_number > 0 ) {
        	$deadline_estimate_factory = new WPML_TM_Jobs_Deadline_Estimate_Factory();
        	$deadline_estimate_date = $deadline_estimate_factory->create()->get(
        		TranslationProxy_Basket::get_basket(),
		        array(
			        'translator_id' => TranslationProxy_Service::get_wpml_translator_id(),
			        'service'       => TranslationProxy::get_current_service_id(),
		        )
			);

			$basket_name_max_length  = TranslationProxy::get_current_service_batch_name_max_length();
			$source_language         = TranslationProxy_Basket::get_source_language();
			$basket                  = new WPML_Translation_Basket( $wpdb );
			$basket_name_placeholder = sprintf(
				__( "%s|WPML|%s", 'wpml-translation-management' ), get_option( 'blogname' ), $source_language
			);
			$basket_name_placeholder = esc_attr( $basket->get_unique_basket_name( $basket_name_placeholder, $basket_name_max_length ) );
			$basket_languages        = TranslationProxy_Basket::get_target_languages();
			$target_languages        = array();
			$translators_dropdowns   = array();

	        if ( $basket_languages ) {
		        $target_languages = $sitepress->get_active_languages();

		        foreach ( $target_languages as $key => $lang ) {
			        if ( ! in_array( $lang['code'], $basket_languages, true )
						 || TranslationProxy_Basket::get_source_language() === $lang['code']
					) {
				        unset( $target_languages[ $key ] );
			        } else {
				        $translators_dropdowns[ $lang['code'] ] = $this->get_translators_dropdown( $lang['code'] );
					}
		        }
	        }

	        $tooltip_content = esc_html__( 'This deadline is what WPML suggests according to the amount of work that you already sent to this translator. You can modify this date to set the deadline manually.', 'wpml-translation-management' );
			$tooltip_content .= '<br><br><a href="https://wpml.org/documentation/translating-your-contents/using-the-translation-editor/setting-translation-deadlines/" class="wpml-external-link" target="_blank">' . esc_html__( 'Learn more about using deadlines', 'wpml-translation-management' ) . '</a>';
			$tooltip_content = htmlentities( $tooltip_content );

	        $model = array(
				'strings' => array(
					'section_title'            => __( 'Choose translation options', 'wpml-translation-management' ),
					'batch_name_label'         => __( 'Batch name:', 'wpml-translation-management' ),
					'batch_name_desc'          => __( 'Give a name to the batch. If omitted, the default name will be applied.', 'wpml-translation-management' ),
					'column_language'          => __( 'Language', 'wpml-translation-management' ),
					'column_translator'        => __( 'Translator', 'wpml-translation-management' ),
					'translate_by_label'       => __( 'Translate by', 'wpml-translation-management' ),
					'manage_translators_label' => __( 'Manage translators', 'wpml-translation-management' ),
					'batch_deadline_label'     => __( 'Suggested deadline:', 'wpml-translation-management' ),
					'batch_deadline_tooltip'   => $tooltip_content,
					'button_send_all'          => __( 'Send all items for translation', 'wpml-translation-management' ),
				),
				'source_language'          => $source_language,
				'basket_name_max_length'   => $basket_name_max_length,
				'basket_name_placeholder'  => $basket_name_placeholder,
				'target_languages'         => $target_languages,
				'dropdowns_translators'    => $translators_dropdowns,
				'manage_translators_url'   => 'admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=translators',
				'deadline_estimation_date' => $deadline_estimate_date,
				'extra_basket_fields'      => TranslationProxy_Basket::get_basket_extra_fields_section(),
				'nonces'                   => array(
					'_icl_nonce_send_basket_items'  => wp_create_nonce( 'send_basket_items_nonce' ),
					'_icl_nonce_send_basket_item'   => wp_create_nonce( 'send_basket_item_nonce' ),
					'_icl_nonce_send_basket_commit' => wp_create_nonce( 'send_basket_commit_nonce' ),
					'_icl_nonce_check_basket_name'  => wp_create_nonce( 'check_basket_name_nonce' ),
					'_icl_nonce_refresh_deadline'   => wp_create_nonce( 'wpml-tm-jobs-deadline-estimate-ajax-action' ),
				),
	        );

            echo $this->template_service->show( $model, 'basket/options.twig' );
        }

		do_action( 'wpml_translation_basket_page_after' );
    }

	private function get_translators_dropdown( $lang_code ) {
		$selected_translator = TranslationProxy_Service::get_wpml_translator_id();

		$args = array(
			'from'     => TranslationProxy_Basket::get_source_language(),
			'to'       => $lang_code,
			'name'     => 'translator[' . $lang_code . ']',
			'selected' => $selected_translator,
			'services' => array( 'local', TranslationProxy::get_current_service_id() ),
			'echo'     => false,
		);

		$blog_translators     = wpml_tm_load_blog_translators();
		$translators_dropdown = new WPML_TM_Translators_Dropdown( $blog_translators );

		return $translators_dropdown->render( $args );
	}

	public function build_content_translation_jobs() {
		?>

		<span class="spinner waiting-1" style="display: inline-block; float:none; visibility: visible"></span>

		<fieldset class="filter-row"></fieldset>
		<div class="listing-table wpml-translation-management-jobs" id="icl-tm-jobs-form" style="display: none;">
			<h3><?php esc_html_e( 'Jobs', 'wpml-translation-management' ) ?></h3>
			<table id="icl-translation-jobs" class="wp-list-table widefat fixed">
				<thead>
				<tr>
					<td scope="col" id="cb" class="manage-column check-column" style="">
						<label class="screen-reader-text" for="bulk-select-top"><?php esc_html_e( 'Select All', 'wpml-translation-management' ) ?></label>
						<input id="bulk-select-top" class="bulk-select-checkbox" type="checkbox">
					</td>
					<th scope="col" id="job_id" class="manage-column column-job_id" style="">
						<?php esc_html_e( 'Job ID', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="title" class="manage-column column-title" style="">
						<?php esc_html_e( 'Title', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="language" class="manage-column column-language" style="">
						<?php esc_html_e( 'Language', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="status" class="manage-column column-status" style="">
						<?php esc_html_e( 'Status', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="deadline" class="manage-column column-deadline" style="">
						<?php esc_html_e( 'Deadline', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="translator" class="manage-column column-translator" style="">
						<?php esc_html_e( 'Translator', 'wpml-translation-management' ) ?>
					</th>
				</tr>
                </thead>
                <tfoot>
				<tr>
					<th scope="col" id="cb" class="manage-column check-column" style="">
						<label class="screen-reader-text" for="bulk-select-bottom"><?php esc_html_e( 'Select All', 'wpml-translation-management' ) ?></label>
						<input id="bulk-select-bottom" class="bulk-select-checkbox" type="checkbox">
					</th>
					<th scope="col" id="job_id" class="manage-column column-job_id" style="">
						<?php esc_html_e( 'Job ID', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="title" class="manage-column column-title" style="">
						<?php esc_html_e( 'Title', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="language" class="manage-column column-language" style="">
						<?php esc_html_e( 'Language', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="status" class="manage-column column-status" style="">
						<?php esc_html_e( 'Status', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="deadline" class="manage-column column-deadline" style="">
						<?php esc_html_e( 'Deadline', 'wpml-translation-management' ) ?>
					</th>
					<th scope="col" id="translator" class="manage-column column-translator" style="">
						<?php esc_html_e( 'Translator', 'wpml-translation-management' ) ?>
					</th>
				</tr>
				</tfoot>
                <tbody class="groups"></tbody>
            </table>

			<br/>

			<?php wp_nonce_field( 'assign_translator_nonce', '_icl_nonce_at' ) ?>
            <?php wp_nonce_field( 'check_batch_status_nonce', '_icl_check_batch_status_nonce' ) ?>
			<input type="hidden" name="icl_tm_action" value=""/>
			<input id="icl-tm-jobs-cancel-but" name="icl-tm-jobs-cancel-but" class="button-primary" type="submit" value="<?php esc_attr_e( 'Cancel selected', 'wpml-translation-management' ) ?>" disabled="disabled"/>
			<span id="icl-tm-jobs-cancel-msg" style="display: none"><?php esc_html_e( 'Are you sure you want to cancel these jobs?', 'wpml-translation-management' ); ?></span>
			<span id="icl-tm-jobs-cancel-msg-2" style="display: none"><?php esc_html_e( 'WARNING: %s job(s) are currently being translated.', 'wpml-translation-management' ); ?></span>
			<span id="icl-tm-jobs-cancel-msg-3" style="display: none"><?php esc_html_e( 'Are you sure you want to abort this translation?', 'wpml-translation-management' ); ?></span>

			<span class="navigator"></span>

			<span class="spinner waiting-2" style="display: none; float:none; visibility: visible"></span>

			<?php wp_nonce_field( 'icl_cancel_translation_jobs_nonce', 'icl_cancel_translation_jobs_nonce' ); ?>
			<?php wp_nonce_field( 'icl_get_jobs_table_data_nonce', 'icl_get_jobs_table_data_nonce' ); ?>
		</div>

		<?php
		TranslationManagement::include_underscore_templates( 'listing' );
	}

    public function build_content_mcs()
    {
        /**
         * included by menu translation-management.php
         *
         * @uses TranslationManagement
         */
        global $sitepress, $iclTranslationManagement, $wpdb, $ICL_Pro_Translation;


	    $doc_translation_method = isset($iclTranslationManagement->settings['doc_translation_method']) ? (int)$iclTranslationManagement->settings['doc_translation_method'] : ICL_TM_TMETHOD_MANUAL;

	    $translate_link_targets_ui = new WPML_Translate_Link_Targets_UI(
		    'ml-content-setup-sec-10',
		    __( 'Translate Link Targets', 'wpml-translation-management' ),
		    $wpdb,
		    $sitepress,
		    $ICL_Pro_Translation
	    );

	    $translate_link_targets = new WPML_Translate_Link_Target_Global_State( $sitepress );
	    if ( $translate_link_targets->is_rescan_required() ) {
		    ?>
			    <div class="update-nag">
				    <p><i class="otgs-ico-warning"></i> <?php echo esc_html__( 'There is new translated content on this site. You can scan posts and strings to adjust links to point to translated content.', 'wpml-translation-management' ); ?></p>
				    <p><?php $translate_link_targets_ui->render_top_link(); ?></p>
			    </div>
		    <?php
	    }

	    $end_user_factory = new WPML_End_User_Loader_Factory();
        $is_end_user_feature_enabled =  $end_user_factory->is_end_user_feature_enabled();
        ?>

        <ul class="wpml-navigation-links js-wpml-navigation-links">
	        <?php if ( $is_end_user_feature_enabled ) { ?>
                <li><a href="#ml-content-setup-sec-0"><?php echo esc_html__('General settings', 'wpml-translation-management'); ?></a></li>
            <?php } ?>
            <li><a href="#ml-content-setup-sec-1"><?php echo esc_html__('How to translate posts and pages', 'wpml-translation-management'); ?></a></li>
            <li><a href="#ml-content-setup-sec-2"><?php echo esc_html__('Posts and pages synchronization', 'wpml-translation-management'); ?></a></li>
            <li>
                <a href="#ml-content-setup-sec-3"><?php echo esc_html__('Translated documents options', 'wpml-translation-management'); ?></a>
            </li>
            <?php if (defined('WPML_ST_VERSION')): ?>
                <li>
                    <a href="#ml-content-setup-sec-4"><?php echo esc_html__('Custom posts slug translation options', 'wpml-translation-management'); ?></a>
                </li>
            <?php endif; ?>
            <li>
                <a href="#ml-content-setup-sec-5"><?php echo esc_html__('Translation pickup mode', 'wpml-translation-management'); ?></a>
            </li>
                <li><a href="#ml-content-setup-sec-5-1"><?php echo esc_html__('XLIFF file options', 'wpml-translation-management'); ?></a></li>
            <li>
                <a href="#ml-content-setup-sec-cf"><?php echo esc_html__('Custom Fields Translation', 'wpml-translation-management'); ?></a>
            </li>
            <li>
                <a href="#ml-content-setup-sec-tcf"><?php echo esc_html__('Custom Term Meta Translation', 'wpml-translation-management'); ?></a>
            </li>
            <?php


            $custom_posts = array();
            $this->post_types = $sitepress->get_translatable_documents(true);

            foreach ($this->post_types as $k => $v) {
	            $custom_posts[$k] = $v;
            }

            global $wp_taxonomies;
            $custom_taxonomies = array_diff(array_keys((array)$wp_taxonomies), array('post_tag', 'category', 'nav_menu', 'link_category', 'post_format'));
            ?>
            <?php if ($custom_posts): ?>
                <li><a href="#ml-content-setup-sec-7"><?php echo esc_html__('Post Types Translation', 'wpml-translation-management'); ?></a>
                </li>
            <?php endif; ?>
            <?php if ($custom_taxonomies): ?>
                <li><a href="#ml-content-setup-sec-8"><?php echo esc_html__('Taxonomies Translation', 'wpml-translation-management'); ?></a>
                </li>
            <?php endif; ?>
            <?php if (!empty($iclTranslationManagement->admin_texts_to_translate) && function_exists('icl_register_string')): ?>
                <li>
                    <a href="#ml-content-setup-sec-9"><?php echo esc_html__('Admin Strings to Translate', 'wpml-translation-management'); ?></a>
                </li>
            <?php endif; ?>
	        <li>
	            <?php $translate_link_targets_ui->render_top_link(); ?>
	        </li>
        </ul>

        <div class="wpml-section wpml-section-notice">
            <div class="updated below-h2">
                <p>
                    <?php echo esc_html__("WPML can read a configuration file that tells it what needs translation in themes and plugins. The file is named wpml-config.xml and it's placed in the root folder of the plugin or theme.", 'wpml-translation-management'); ?>
                </p>

                <p>
                    <a href="https://wpml.org/?page_id=5526"><?php echo esc_html__('Learn more', 'wpml-translation-management') ?></a>
                </p>
            </div>
        </div>

	    <?php if ( $is_end_user_feature_enabled ) { ?>
        <div class="wpml-section" id="ml-content-setup-sec-0">

            <div class="wpml-section-header">
                <h3><?php echo esc_html__('General settings', 'wpml-translation-management'); ?></h3>
            </div>

            <div class="wpml-section-content">
                <form id="wpml-tm-general-settings" action="">
                    <ul>
                        <li>
                            <label>
                                <?php
                                    $disabling_option = new WPML_End_User_Account_Creation_Disabled_Option();
                                    $is_disabled = $disabling_option->is_disabled();
                                ?>
                                <input type="checkbox" name="wpml-disabling" value="1"
                                    <?php checked(true, $is_disabled) ?>
                                    data-nonce="<?php echo wp_create_nonce( WPML_End_User_Account_Creation_Disabled::NONCE ) ?>"
                                />
                                <?php echo esc_html__( 'Disable the possibility of creating accounts for users on wpml.org', 'wpml-translation-management' ) ?>
                                <span class="spinner" style="float:none;" > </span>
                            </label>
                        </li>
                    </ul>

                </form>
            </div>
        </div>
        <?php } ?>

        <div class="wpml-section" id="ml-content-setup-sec-1">

            <div class="wpml-section-header">
                <h3><?php echo esc_html__('How to translate posts and pages', 'wpml-translation-management'); ?></h3>
            </div>

            <div class="wpml-section-content">

                <form id="icl_doc_translation_method" name="icl_doc_translation_method" action="">
                    <?php wp_nonce_field('icl_doc_translation_method_nonce', '_icl_nonce') ?>

                    <ul class="t_method">
                        <li>
	                        <label>
		                        <input type="radio" name="t_method" value="<?php echo ICL_TM_TMETHOD_MANUAL ?>"
		                               <?php if ( ! $doc_translation_method): ?>checked="checked"<?php endif; ?> />
		                        <?php echo esc_html__( 'Create translations manually', 'wpml-translation-management' ) ?>
	                        </label>
                        </li>
	                    <li>
		                    <label>
			                    <input type="radio" name="t_method" value="<?php echo ICL_TM_TMETHOD_EDITOR ?>"
			                           <?php if ($doc_translation_method): ?>checked="checked"<?php endif; ?> />
			                    <?php echo esc_html__( 'Use the translation editor', 'wpml-translation-management' ) ?>
		                    </label>
	                    </li>
                    </ul>

	                <?php do_action( 'wpml_doc_translation_method_below' ); ?>

	                <p id="tm_block_retranslating_terms"><label>
                            <input name="tm_block_retranslating_terms"
                                   value="1" <?php checked( icl_get_setting( 'tm_block_retranslating_terms' ),
                                                            "1" ) ?>
                                   type="checkbox"/>
                            <?php echo esc_html__( "Don't include already translated terms in the translation editor",
                                      'wpml-translation-management' ) ?>
                        </label>
                    </p>

                    <p>
                        <label>
                            <input name="how_to_translate"
                                   value="1" <?php checked(icl_get_setting('hide_how_to_translate'), false) ?>
                                   type="checkbox"/>
                            <?php echo esc_html__('Show translation instructions in the list of pages', 'wpml-translation-management') ?>
                        </label>
                    </p>
	                
	                <?php do_action('wpml_how_to_translate_posts_and_pages'); ?>

	                <?php do_action( 'wpml_how_to_translate_posts_and_pages_below' ); ?>

                    <p>
                        <a href="https://wpml.org/?page_id=3416"
                           target="_blank"><?php echo esc_html__('Learn more about the different translation options', 'wpml-translation-management') ?></a>
                    </p>

                    <p class="buttons-wrap">
                        <span class="icl_ajx_response" id="icl_ajx_response_dtm"> </span>
                        <input type="submit" class="button-primary"
                               value="<?php echo esc_html__('Save', 'wpml-translation-management') ?>"/>
                    </p>

                </form>
            </div>
            <!-- .wpml-section-content -->

        </div> <!-- .wpml-section -->

        <?php include_once ICL_PLUGIN_PATH . '/menu/_posts_sync_options.php'; ?>

        <div class="wpml-section" id="ml-content-setup-sec-3">

            <div class="wpml-section-header">
                <h3><?php echo esc_html__('Translated documents options', 'wpml-translation-management') ?></h3>
            </div>

            <div class="wpml-section-content">

                <form name="icl_tdo_options" id="icl_tdo_options" action="">
                    <?php wp_nonce_field('wpml-translated-document-options-nonce', WPML_TM_Options_Ajax::NONCE_TRANSLATED_DOCUMENT); ?>

                    <div class="wpml-section-content-inner">
                        <h4>
                            <?php echo esc_html__('Document status', 'wpml-translation-management') ?>
                        </h4>
                        <ul>
                            <li>
                                <label>
                                    <input type="radio" name="icl_translated_document_status" value="0"
	                                    <?php checked( (bool) icl_get_setting( 'translated_document_status' ), false ); ?> />
                                    <?php echo esc_html__('Draft', 'wpml-translation-management') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="icl_translated_document_status" value="1"
	                                    <?php checked( (bool) icl_get_setting( 'translated_document_status' ), true ); ?> />
                                    <?php echo esc_html__('Same as the original document', 'wpml-translation-management') ?>
                                </label>
                            </li>
                        </ul>
                        <p class="explanation-text">
                            <?php echo esc_html__( 'Choose if translations should be published when received. Note: If Publish is selected, the translation will only be published if the original document is published when the translation is received.', 'wpml-translation-management') ?>
                        </p>
                    </div>

                    <div class="wpml-section-content-inner">
                        <h4>
                            <?php echo esc_html__('Page URL', 'wpml-translation-management') ?>
                        </h4>
                        <ul>
                            <li>
                                <label><input type="radio" name="icl_translated_document_page_url" value="auto-generate"
                                              <?php if (empty($sitepress_settings['translated_document_page_url']) ||
                                              $sitepress_settings['translated_document_page_url'] === 'auto-generate'): ?>checked="checked"<?php endif; ?> />
                                    <?php echo esc_html__('Auto-generate from title (default)', 'wpml-translation-management') ?>
                                </label>
                            </li>
                            <li>
                                <label><input type="radio" name="icl_translated_document_page_url" value="translate"
                                              <?php if ($sitepress_settings['translated_document_page_url'] === 'translate'): ?>checked="checked"<?php endif; ?> />
                                    <?php echo esc_html__('Translate (this will include the slug in the translation and not create it automatically from the title)', 'wpml-translation-management') ?>
                                </label>
                            </li>
                            <li>
                                <label><input type="radio" name="icl_translated_document_page_url" value="copy-encoded"
                                              <?php if ($sitepress_settings['translated_document_page_url'] === 'copy-encoded'): ?>checked="checked"<?php endif; ?> />
                                    <?php echo esc_html__('Copy from original language if translation language uses encoded URLs', 'wpml-translation-management') ?>
                                </label>
                            </li>
                        </ul>
                    </div>

                    <div class="wpml-section-content-inner">
                        <p class="buttons-wrap">
                            <span class="icl_ajx_response" id="icl_ajx_response_tdo"> </span>
                            <input id="js-translated_document-options-btn" type="button" class="button-primary" value="<?php echo esc_attr__('Save', 'wpml-translation-management') ?>"/>
                        </p>
                    </div>

                </form>
            </div>
            <!-- .wpml-section-content -->

        </div> <!-- .wpml-section -->

        <?php if (defined('WPML_ST_VERSION')) include_once WPML_ST_PATH . '/menu/_slug-translation-options.php'; ?>

        <div class="wpml-section" id="ml-content-setup-sec-5">

            <div class="wpml-section-header">
                <h3><?php echo esc_html__('Translation pickup mode', 'wpml-translation-management'); ?></h3>
            </div>

            <div class="wpml-section-content">

                <form id="icl_translation_pickup_mode" name="icl_translation_pickup_mode" action="">
                    <?php wp_nonce_field( 'wpml_save_translation_pickup_mode', WPML_TM_Pickup_Mode_Ajax::NONCE_PICKUP_MODE ) ?>

                    <p>
                        <?php echo esc_html__('How should the site receive completed translations from Translation Service?', 'wpml-translation-management'); ?>
                    </p>

                    <p>
                        <label>
                            <input type="radio" name="icl_translation_pickup_method"
                                   value="<?php echo ICL_PRO_TRANSLATION_PICKUP_XMLRPC ?>"
                                   <?php if ($sitepress_settings['translation_pickup_method'] === ICL_PRO_TRANSLATION_PICKUP_XMLRPC): ?>checked="checked"<?php endif ?>/>
                            <?php echo esc_html__('Translation Service will deliver translations automatically using XML-RPC', 'wpml-translation-management'); ?>
                        </label>
                    </p>

                    <p>
                        <label>
                            <input type="radio" name="icl_translation_pickup_method"
                                   value="<?php echo ICL_PRO_TRANSLATION_PICKUP_POLLING ?>"
                                   <?php if ($sitepress_settings['translation_pickup_method'] === ICL_PRO_TRANSLATION_PICKUP_POLLING): ?>checked="checked"<?php endif; ?> />
                            <?php echo esc_html__('The site will fetch translations manually', 'wpml-translation-management'); ?>
                        </label>
                    </p>


                    <p class="buttons-wrap">
                        <span class="icl_ajx_response" id="icl_ajx_response_tpm"> </span>
                        <input id="translation-pickup-mode" class="button-primary" name="save"
                               value="<?php echo esc_attr__('Save', 'wpml-translation-management') ?>" type="button"/>
                    </p>

                    <?php
                    $this->build_content_dashboard_fetch_translations_box();
                    ?>
                </form>

            </div>
            <!-- .wpml-section-content -->

        </div> <!-- .wpml-section -->

        <?php
	    include_once WPML_TM_PATH . '/menu/xliff-options.php';
	    $this->build_content_mcs_custom_fields();


	    include_once ICL_PLUGIN_PATH . '/menu/_custom_types_translation.php'; ?>

        <?php if (!empty($iclTranslationManagement->admin_texts_to_translate) && function_exists('icl_register_string')): //available only with the String Translation plugin ?>
        <div class="wpml-section" id="ml-content-setup-sec-9">

            <div class="wpml-section-header">
                <h3><?php echo esc_html__('Admin Strings to Translate', 'wpml-translation-management'); ?></h3>
            </div>

            <div class="wpml-section-content">
                <table class="widefat">
                    <thead>
                    <tr>
                        <th colspan="3">
                            <?php echo esc_html__('Admin Strings', 'wpml-translation-management'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php
                            foreach ($iclTranslationManagement->admin_texts_to_translate as $option_name => $option_value) {
                                $iclTranslationManagement->render_option_writes($option_name, $option_value);
                            }
                            ?>
                            <br/>

                            <p><a class="button-secondary"
                                  href="<?php echo admin_url('admin.php?page=' . WPML_ST_FOLDER . '/menu/string-translation.php') ?>"><?php echo esc_html__('Edit translatable strings', 'wpml-translation-management') ?></a>
                            </p>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <!-- .wpml-section-content -->

        </div> <!-- .wpml-section -->
    <?php

    endif;

	    $translate_link_targets_ui->render();

	    wp_enqueue_script( 'wpml-tm-mcs' );
	    wp_enqueue_script( 'wpml-tm-mcs-translate-link-targets' );
    }

	private function build_content_mcs_custom_fields() {
		global $iclTranslationManagement, $wpdb;

		$settings_factory = new WPML_Custom_Field_Setting_Factory( $iclTranslationManagement );
		$settings_factory->show_system_fields = array_key_exists( 'show_system_fields', $_GET ) ? (bool) $_GET['show_system_fields'] : false;
		$unlock_button_ui = new WPML_UI_Unlock_Button();
		$menu_item        = new WPML_TM_MCS_Post_Custom_Field_Settings_Menu( $settings_factory, $unlock_button_ui );
		echo $menu_item->render();

		if ( ! empty( $wpdb->termmeta ) ) {
			$menu_item_terms = new WPML_TM_MCS_Term_Custom_Field_Settings_Menu( $settings_factory, $unlock_button_ui );
			echo $menu_item_terms->render();
		}
	}

    public function build_content_translation_notifications() {
        ?>
        <form method="post" name="translation-notifications" id="translation-notifications"
              action="admin.php?page=<?php echo WPML_TM_FOLDER ?>/menu/main.php&amp;sm=notifications">
            <input type="hidden" name="icl_tm_action" value="save_notification_settings"/>

	        <?php do_action( 'wpml_tm_translation_notification_setting_after' ); ?>

	        <div class="wpml-section" id="translation-notifications-sec-3">
		        <p class="submit">
			        <input type="submit" class="button-primary"
			               value="<?php echo esc_html__('Save', 'wpml-translation-management') ?>"/>
		        </p>
	        </div>

	        <?php wp_nonce_field( 'save_notification_settings_nonce', 'save_notification_settings_nonce' ); ?>
        </form>

    <?php
    }

    private function render_items()
    {
        if ($this->tab_items) {
            ?>
            <p class="icl-translation-management-menu wpml-tabs">
                <?php
                $this->build_tabs();
                ?>
            </p>
            <div class="icl_tm_wrap">
                <?php
                $this->build_content();
                ?>
            </div>
        <?php
        }
    }

	private function build_dashboard_filter_arguments() {
		global $sitepress, $iclTranslationManagement;

		$this->current_language = $sitepress->get_current_language();
		$this->source_language  = TranslationProxy_Basket::get_source_language();

		if ( isset( $_SESSION[ 'translation_dashboard_filter' ] ) ) {
			$this->translation_filter = $_SESSION[ 'translation_dashboard_filter' ];
		}
		if ( $this->source_language || ! isset( $this->translation_filter[ 'from_lang' ] ) ) {
			if ( $this->source_language ) {
				$this->translation_filter[ 'from_lang' ] = $this->source_language;
			} else {
				$this->translation_filter[ 'from_lang' ] = $this->current_language;
				if ( array_key_exists( 'lang', $_GET ) && $lang = filter_var( $_GET['lang'] , FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
					$this->translation_filter[ 'from_lang' ] = $lang;
				}
			}
		}

        if (!isset($this->translation_filter['to_lang'])) {
            $this->translation_filter['to_lang'] = '';
	        if ( array_key_exists( 'to_lang', $_GET ) && $lang = filter_var( $_GET['to_lang'] , FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
		        $this->translation_filter[ 'to_lang' ] = $lang;
	        }
        }

        if ($this->translation_filter['to_lang'] == $this->translation_filter['from_lang']) {
            $this->translation_filter['to_lang'] = false;
        }

        if (!isset($this->translation_filter['tstatus'])) {
            $this->translation_filter['tstatus'] = isset($_GET['tstatus']) ? $_GET['tstatus'] : -1; // -1 == All documents
        }

        if (!isset($this->translation_filter['sort_by']) || !$this->translation_filter['sort_by']) {
            $this->translation_filter['sort_by'] = 'date';
        }
        if (!isset($this->translation_filter['sort_order']) || !$this->translation_filter['sort_order']) {
            $this->translation_filter['sort_order'] = 'DESC';
        }
        $sort_order_next = $this->translation_filter['sort_order'] == 'ASC' ? 'DESC' : 'ASC';
        $this->dashboard_title_sort_link = 'admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=dashboard&icl_tm_action=sort&sort_by=title&sort_order=' . $sort_order_next;
        $this->dashboard_date_sort_link = 'admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=dashboard&icl_tm_action=sort&sort_by=date&sort_order=' . $sort_order_next;

        $this->post_statuses = array(
            'publish' => __('Published', 'wpml-translation-management'),
            'draft' => __('Draft', 'wpml-translation-management'),
            'pending' => __('Pending Review', 'wpml-translation-management'),
            'future' => __('Scheduled', 'wpml-translation-management'),
            'private' => __('Private', 'wpml-translation-management')
        );
        $this->post_statuses = apply_filters('wpml_tm_dashboard_post_statuses', $this->post_statuses);

        // Get the document types that we can translate
        $this->post_types = $sitepress->get_translatable_documents();
        $this->post_types = apply_filters('wpml_tm_dashboard_translatable_types', $this->post_types);
        $this->build_external_types();

        $this->selected_languages = array();
        if (!empty($iclTranslationManagement->dashboard_select)) {
            $this->selected_posts = $iclTranslationManagement->dashboard_select['post'];
            $this->selected_languages = $iclTranslationManagement->dashboard_select['translate_to'];
        }
        if (isset($this->translation_filter['icl_selected_posts'])) {
            parse_str($this->translation_filter['icl_selected_posts'], $this->selected_posts);
        }

        $this->filter_post_status = isset($this->translation_filter['status']) ? $this->translation_filter['status'] : false;

        if ( isset( $_GET[ 'type' ] ) ) {
            $this->translation_filter[ 'type' ] = $_GET[ 'type' ];
        }

		$paged           = (int) filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );
		$this->translation_filter['page'] = $paged ? $paged - 1 : 0;
        $this->filter_translation_type = isset( $this->translation_filter[ 'type' ] ) ? $this->translation_filter[ 'type' ] : false;
    }

    private function build_content_dashboard_documents_sorting_link( $url, $label, $filter_argument ) {
        $caption = $label;
        if ( $this->translation_filter[ 'sort_by' ] === $filter_argument ) {
            $caption .= '&nbsp;';
            $caption .= $this->translation_filter[ 'sort_order' ] === 'ASC' ? '&uarr;' : '&darr;';
        }
        ?>
        <a href="<?php echo esc_url($url); ?>">
            <?php echo $caption; ?>
        </a>
    <?php
    }

    private function build_content_dashboard_documents_head_footer_cells() {
        global $sitepress;
        ?>
        <tr>
            <td scope="col" class="manage-column column-cb check-column">
                <?php
                $check_all_checked = checked( true, isset( $_GET[ 'post_id' ] ), false );
                ?>
                <input type="checkbox" <?php echo $check_all_checked; ?>/>
            </td>
            <th scope="col" class="manage-column column-title">
                <?php
                $dashboard_title_sort_caption = __( 'Title', 'wpml-translation-management' );
                $this->build_content_dashboard_documents_sorting_link( $this->dashboard_title_sort_link, $dashboard_title_sort_caption, 'p.post_title' );
                ?>
            </th>
            <th scope="col" class="manage-column wpml-column-type">
		        <?php echo esc_html__( 'Type', 'wpml-translation-management' ) ?>
            </th>
	        <?php
	        $active_languages = $sitepress->get_active_languages();
	        $lang_count       = count( $active_languages );
	        $lang_col_width   = ( $lang_count - 1 ) * 26 . "px";
	        if ($lang_count > 10) {
		        $lang_col_width = '30%';
	        }
	        ?>

            <th scope="col" class="manage-column column-active-languages wpml-col-languages" style="width: <?php echo esc_attr($lang_col_width); ?>">
		        <?php
		        if ( $this->translation_filter['to_lang'] && array_key_exists( $this->translation_filter['to_lang'], $active_languages ) ) {
			        $lang = $active_languages[ $this->translation_filter['to_lang'] ];
			        ?>

                    <span title="<?php echo esc_attr($lang[ 'display_name' ]); ?>"><img src="<?php echo esc_url($sitepress->get_flag_url( $this->translation_filter[ 'to_lang' ] )) ?>" width="16" height="12" alt="<?php echo esc_attr($this->translation_filter[ 'to_lang' ]) ?>"/></span>
			        <?php
		        } else {
			        foreach ( $active_languages as $lang ) {
				        if ( $lang['code'] === $this->translation_filter['from_lang'] ) {
					        continue;
				        }
				        ?>
                        <span title="<?php echo esc_attr($lang[ 'display_name' ]); ?>"><img src="<?php echo esc_url($sitepress->get_flag_url( $lang[ 'code' ]) ) ?>" width="16" height="12" alt="<?php echo esc_attr($lang[ 'code' ]) ?>"/></span>
				        <?php
			        }
		        }
		        ?>
            </th>
            <th scope="col" class="manage-column column-date">
                <?php
                $dashboard_date_sort_label = __( 'Date', 'wpml-translation-management' );
                $this->build_content_dashboard_documents_sorting_link( $this->dashboard_date_sort_link, $dashboard_date_sort_label, 'p.post_date' );
                ?>
            </th>
            <th scope="col" class="manage-column column-note">
                <?php echo esc_html__( 'Notes', 'wpml-translation-management' ) ?>
            </th>

        </tr>
    <?php
    }

	private function build_content_dashboard_documents() {
		?>

        <input type="hidden" name="icl_tm_action" value="add_jobs"/>
        <input type="hidden" name="translate_from" value="<?php echo esc_attr( $this->translation_filter['from_lang'] ); ?>"/>
        <table class="widefat fixed striped" id="icl-tm-translation-dashboard">
            <thead>
			<?php $this->build_content_dashboard_documents_head_footer_cells(); ?>
            </thead>
            <tfoot>
			<?php $this->build_content_dashboard_documents_head_footer_cells(); ?>
            </tfoot>
            <tbody>
			<?php
			$this->build_content_dashboard_documents_body();
			?>
            </tbody>
        </table>
        <div class="tablenav">
            <div class="alignleft">
                <strong><?php echo esc_html__( 'Word count estimate:', 'wpml-translation-management' ) ?></strong>
				<?php printf( esc_html__( '%s words', 'wpml-translation-management' ), '<span id="icl-tm-estimated-words-count">0</span>' ) ?>
                <span id="icl-tm-doc-wrap" style="display: none">
	                <?php printf( esc_html__( 'in %s document(s)', 'wpml-translation-management' ), '<span id="icl-tm-sel-doc-count">0</span>' ); ?>
                </span>
            </div>
			<?php
            if ( ! empty( $this->translation_filter['type'] ) ) {
                do_action( 'wpml_tm_dashboard_pagination', $this->dashboard_pagination->get_items_per_page(), $this->found_documents );
            }
            ?>
        </div>
		<?php
	}

	public function build_content_dashboard_fetch_translations_box() {
		if ( TranslationProxy::is_current_service_active_and_authenticated() ) {
			$tp_polling_box = new WPML_TP_Polling_Box();
			echo $tp_polling_box->render();
		}
	}

	private function build_external_types() {
		$this->post_types = apply_filters( 'wpml_get_translatable_types', $this->post_types );
		foreach ( $this->post_types as $id => $type_info ) {
			if ( isset( $type_info->prefix ) ) {
				// this is an external type returned by wpml_get_translatable_types
				$new_type                        = new stdClass();
				$new_type->labels                = new stdClass();
				$new_type->labels->singular_name = isset( $type_info->labels->singular_name ) ? $type_info->labels->singular_name : $type_info->label;
				$new_type->labels->name          = isset( $type_info->labels->name ) ? $type_info->labels->name : $type_info->label;
				$new_type->prefix                = $type_info->prefix;
				$new_type->external_type         = 1;

				$this->post_types[ $id ] = $new_type;
			}
		}
	}

    public function build_content_dashboard_filter() {
        $dashboard_filter = new WPML_TM_Dashboard_Display_Filter(
            $this->active_languages,
            $this->source_language,
            $this->translation_filter,
            $this->post_types,
            $this->post_statuses
        );
        $dashboard_filter->display();
    }

    private function build_content_dashboard_results() {
        ?>
        <form method="post" id="icl_tm_dashboard_form">
            <?php
            // #############################################
            // Display the items for translation in a table.
            // #############################################

            $this->build_content_dashboard_documents();

            echo '<div style="clear:both">';
            $this->build_content_dashboard_documents_options();
            do_action('wpml_tm_dashboard_promo');
            echo '</div>';
            ?>

        </form>

        <br/>
    <?php
    }
    private function is_translation_locked() {
			global $WPML_Translation_Management;
			$result = $WPML_Translation_Management->service_activation_incomplete();

			return $result;
    }

    private function build_content_dashboard_documents_options() {
	    global $wpdb;

        $translate_checked = 'checked="checked"';
        $duplicate_checked = '';
        $do_nothing_checked = '';
        if( $this->is_translation_locked() ) {
            $translate_checked = 'disabled="disabled"';
            $do_nothing_checked = 'checked="checked"';
        }

        $flag_factory = new WPML_Flags_Factory( $wpdb );
        $flags = $flag_factory->create();

        ?>
        <table class="widefat fixed tm-dashboard-translation-options" cellspacing="0">
            <thead>
            <tr>
                <th><?php echo esc_html__( 'Translation options', 'wpml-translation-management' ) ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <table id="icl_tm_languages" class="widefat">
                        <thead>
                        <tr>
                            <th><?php echo esc_html__('All Languages', 'wpml-translation-management'); ?></th>
                            <td>
	                            <label>
                                <input type="radio" id="translate-all" value="1" name="radio-action-all" <?php echo $translate_checked;?> /> <?php echo esc_html__( 'Translate',
                                                                   'wpml-translation-management' ) ?>
	                            </label>
                            </td>
                            <td>
	                            <label>
                                <input type="radio" id="duplicate-all" value="2" name="radio-action-all" <?php echo $duplicate_checked ?> /> <?php echo esc_html__( 'Duplicate content',
                                                                   'wpml-translation-management' ) ?>
	                            </label>
                            </td>
                            <td>
	                            <label>
		                            <input type="radio" id="update-none" value="0" name="radio-action-all" <?php echo $do_nothing_checked; ?> /> <?php echo esc_html__( 'Do nothing', 'wpml-translation-management' ) ?>
	                            </label>
                            </td>
                        </tr>
                        <tr class="blank_row">
                            <td colspan="3" style="height:6px!important;"></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $this->active_languages as $lang ): ?>
                            <?php
                            if ( $lang[ 'code' ] == $this->translation_filter[ 'from_lang' ] ) {
                                continue;
                            }
                            $radio_prefix_html = '<input type="radio" name="tr_action[' . esc_attr( $lang[ 'code' ] ) . ']" ';
                            ?>
                            <tr>
                                <th>
                                    <img src="<?php echo esc_url( $flags->get_flag_url( $lang['code'] ) ); ?>"/> <strong><?php echo esc_html( $lang[ 'display_name' ] ); ?></strong>
                                </th>
                                <td>
                                    <label>
                                        <?php echo $radio_prefix_html ?> value="1" <?php echo $translate_checked ?>/>
                                        <?php echo esc_html__( 'Translate', 'wpml-translation-management' ); ?>
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <?php echo $radio_prefix_html ?> value="2" <?php echo $duplicate_checked ?>/>
                                        <?php echo esc_html__( 'Duplicate content', 'wpml-translation-management' ); ?>
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <?php echo $radio_prefix_html ?> value="0" <?php echo $do_nothing_checked ?>/>
                                        <?php echo esc_html__( 'Do nothing', 'wpml-translation-management' ); ?>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <br/>

                    <input name="iclnonce" type="hidden" value="<?php echo wp_create_nonce( 'pro-translation-icl' ) ?>"/>
                    <?php
                    $tm_jobs_submit_disabled = disabled(empty( $this->selected_languages ) && empty( $this->selected_posts ), true, false);
                    $tm_jobs_submit_caption = __( 'Add selected content to translation basket', 'wpml-translation-management' );
                    ?>
                    <input id="icl_tm_jobs_submit" class="button-primary" type="submit" value="<?php echo $tm_jobs_submit_caption; ?>" <?php echo $tm_jobs_submit_disabled; ?> />

                    <div id="icl_dup_ovr_warn" class="icl_dup_ovr_warn" style="display:none;">
                        <?php
                        $dup_message = '<p>';
                        $dup_message .= __( 'Any existing content (translations) will be overwritten when creating duplicates.', 'wpml-translation-management' );
                        $dup_message .= '</p>';
                        $dup_message .= '<p>';
                        $dup_message .= __( "When duplicating content, please first duplicate parent pages to maintain the site's hierarchy.", 'wpml-translation-management' );
                        $dup_message .= '</p>';

                        ICL_AdminNotifier::display_instant_message( $dup_message, 'error' );

                        ?>
                    </div>
                    <div style="width: 45%; margin: auto; position: relative; top: -30px;">
                        <?php
                        ICL_AdminNotifier::display_messages( 'translation-dashboard-under-translation-options' );
                        ICL_AdminNotifier::remove_message( 'items_added_to_basket' );
                        ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    <?php
    }

    private function build_content_dashboard_remote_translations_controls() {
        // shows only when translation polling is on and there are translations in progress
        $this->build_content_dashboard_fetch_translations_box();

        $active_service = icl_do_not_promote() ? false : TranslationProxy::get_current_service();
        $service_dashboard_info = TranslationProxy::get_service_dashboard_info();
        if ( $active_service && $service_dashboard_info ) {
            ?>
            <div class="icl_cyan_box">
                <h3><?php echo $active_service->name . ' ' . __( 'account status',
                                                                 'wpml-translation-management' ) ?></h3>
                <?php echo $service_dashboard_info; ?>
            </div>
            <?php
        }
    }

    private function build_dashboard_documents() {
        global $wpdb, $sitepress;
	    $wpml_tm_dashboard_pagination = new WPML_TM_Dashboard_Pagination();
	    $wpml_tm_dashboard_pagination->add_hooks();
        $tm_dashboard    = new WPML_TM_Dashboard( $wpdb, $sitepress );
        $this->translation_filter['limit_no'] = $this->dashboard_pagination ? $this->dashboard_pagination->get_items_per_page() : 20;
        $dashboard_data = $tm_dashboard->get_documents( $this->translation_filter );
	    $this->documents = $dashboard_data['documents'];
	    $this->found_documents = $dashboard_data['found_documents'];
    }

    public function get_dashboard_documents(){
        return $this->documents;
    }

    /**
     * Used only by unit tests at the moment
     * @return mixed
     */
    public function get_post_types(){
        return $this->post_types;
    }

    /**
     * Used only by unit tests at the moment
     * @return mixed
     */
    private function build_dashboard_data() {
        $this->build_dashboard_filter_arguments();
        $this->build_dashboard_documents();
    }

    private function build_content_dashboard_documents_body() {
        global $sitepress, $wpdb;
        $this->current_document_words_count = 0;
        if ( !$this->documents ) {
            $colspan = 6 + ( $this->translation_filter[ 'to_lang' ]
                    ? 1
                    : count(
                          $sitepress->get_active_languages()
                      ) - 1 );
            ?>
            <tr>
                <td scope="col" colspan="<?php echo $colspan; ?>" align="center">
	                <span class="no-documents-found"><?php echo esc_html__( 'No documents found', 'wpml-translation-management' ) ?></span>
                </td>
            </tr>
        <?php
        } else {
            wp_nonce_field( 'save_translator_note_nonce', '_icl_nonce_stn_' );
            $active_languages = $this->translation_filter[ 'to_lang' ]
                ? array( $this->translation_filter[ 'to_lang' ] => $this->active_languages[ $this->translation_filter[ 'to_lang' ] ] )
                : $this->active_languages;
            foreach ( $this->documents as $doc ) {
                $selected = is_array( $this->selected_posts ) && in_array( $doc->ID, $this->selected_posts );
                $doc_row  = new WPML_TM_Dashboard_Document_Row(
                    $doc,
                    $this->translation_filter,
                    $this->post_types,
                    $this->post_statuses,
                    $active_languages,
                    $selected,
										$sitepress,
										$wpdb
                );
                $doc_row->display();
            }
        }
    }

	private function build_tp_com_log_item( ) {
        if ( isset( $_GET[ 'sm' ] ) && 'com-log' === $_GET['sm' ] ) {
			$this->tab_items['com-log']['caption'] = __('Communication Log', 'wpml-translation-management');
			$this->tab_items['com-log']['callback'] = array($this, 'build_tp_com_log');
		}
	}

	private function build_tp_pickup_log_item() {
		$logger_settings = new WPML_Jobs_Fetch_Log_Settings();

		if ( isset( $_GET['sm'] ) && $logger_settings->get_ui_key() === $_GET['sm'] ) {
			$this->tab_items[ $logger_settings->get_ui_key() ]['caption']  = __( 'Content updates log', 'wpml-translation-management' );
			$this->tab_items[ $logger_settings->get_ui_key() ]['callback'] = array( $this, 'build_tp_pickup_log' );
		}
	}

	public function build_tp_com_log( ) {
		if ( isset( $_POST[ 'tp-com-clear-log' ] ) ) {
			WPML_TranslationProxy_Com_Log::clear_log( );
		}

		if ( isset( $_POST[ 'tp-com-disable-log' ] ) ) {
			WPML_TranslationProxy_Com_Log::set_logging_state( false );
		}

		if ( isset( $_POST[ 'tp-com-enable-log' ] ) ) {
			WPML_TranslationProxy_Com_Log::set_logging_state( true );
		}

		$action_url = esc_attr( 'admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=' . $_GET[ 'sm' ] );
		$com_log = WPML_TranslationProxy_Com_Log::get_log( );

		?>

		<form method="post" id="tp-com-log-form" name="tp-com-log-form" action="<?php echo $action_url; ?>">

			<?php if ( WPML_TranslationProxy_Com_Log::is_logging_enabled( ) ): ?>

				<?php echo esc_html__("This is a log of the communication between your site and the translation system. It doesn't include any private information and allows WPML support to help with problems related to sending content to translation.", 'wpml-translation-management'); ?>

				<br />
				<br />
				<?php if ( $com_log != '' ): ?>
					<textarea wrap="off" readonly="readonly" rows="16" style="font-size:10px; width:100%"><?php echo $com_log; ?></textarea>
					<br />
					<br />
					<input class="button-secondary" type="submit" name="tp-com-clear-log" value="<?php echo esc_attr__( 'Clear log', 'wpml-translation-management' ); ?>">
				<?php else: ?>
					<strong><?php echo esc_html__('The communication log is empty.', 'wpml-translation-management'); ?></strong>
					<br />
					<br />
				<?php endif; ?>

				<input class="button-secondary" type="submit" name="tp-com-disable-log" value="<?php echo esc_attr__( 'Disable logging', 'wpml-translation-management' ); ?>">

			<?php else: ?>
				<?php echo esc_html__("Communication logging is currently disabled. To allow WPML support to help you with issues related to sending content to translation, you need to enable the communication logging.", 'wpml-translation-management'); ?>

				<br />
				<br />
				<input class="button-secondary" type="submit" name="tp-com-enable-log" value="<?php echo esc_attr__( 'Enable logging', 'wpml-translation-management' ); ?>">

			<?php endif; ?>

		</form>
		<?php

	}

	public function build_tp_pickup_log() {
		$this->logger_ui->render();
	}
}
