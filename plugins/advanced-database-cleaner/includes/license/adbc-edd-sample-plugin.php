<?php

/*
Plugin Name: Sample Plugin
Plugin URI: http://pippinsplugins.com/
Description: Illustrates how to include an updater in your plugin for EDD Software Licensing
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 1.0
License: GNU General Public License v2.0 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/***************************************************************************************************************************
* For further details please visit http://docs.easydigitaldownloads.com/article/383-automatic-upgrades-for-wordpress-plugins
***************************************************************************************************************************/

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'ADBC_EDD_STORE_URL', 'https://sigmaplugin.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the download ID for the product in Easy Digital Downloads
define( 'ADBC_EDD_ITEM_ID', 10); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
define( 'ADBC_EDD_ITEM_NAME', 'WordPress Advanced Database Cleaner' ); // you should use your own CONSTANT name, and be sure to replace it in this file

// The name of the settings page for the license input to be displayed
define( 'ADBC_EDD_PLUGIN_LICENSE_PAGE', 'advanced_db_cleaner&aDBc_tab=license' );

if(!class_exists('ADBC_EDD_SL_Plugin_Updater')){
	// load our custom updater
	include(dirname(__FILE__) . '/ADBC_EDD_SL_Plugin_Updater.php');
}

function aDBc_edd_sl_plugin_updater(){

	// retrieve our license key from the DB
	$license_key = trim(get_option('aDBc_edd_license_key'));

	// setup the updater
	$edd_updater = new ADBC_EDD_SL_Plugin_Updater( ADBC_EDD_STORE_URL, ADBC_MAIN_PLUGIN_FILE_PATH, array(
			'version' 	=> ADBC_PLUGIN_VERSION,     // current version number
			'license' 	=> $license_key,            // license key (used get_option above to retrieve from DB)
			'item_id' 	=> ADBC_EDD_ITEM_ID,       	// ID of the product
			'item_name' => ADBC_EDD_ITEM_NAME,      // name of the product
			'author'  	=> 'Younes JFR.', 			// author of this plugin
			'beta'    	=> false,
		)
	);

}
add_action('admin_init', 'aDBc_edd_sl_plugin_updater', 0);

/**************************************************************************
* the code below is just a standard options page. Substitute with your own.
**************************************************************************/
function aDBc_edd_license_page() {
	$license = get_option( 'aDBc_edd_license_key' );
	$status  = get_option( 'aDBc_edd_license_status' );
	// When the user activates the license, hide it by stars like passwords
	if(!empty(trim($license)) && $status !== false && $status == 'valid'){
		$license_key_hidden = substr(trim($license), 0, 4) . "************************" . substr(trim($license), -4);
	}else{
		$license_key_hidden = esc_attr($license);
	}
	?>
	<form method="post" action="options.php">

		<?php settings_fields('aDBc_edd_license'); ?>

		<table style="font-size:15px;text-align:left;" cellspacing="20px">
			<tr>
				<td>
					<b><?php _e('Enter your license key', 'advanced-database-cleaner'); ?></b>
				</td>
				<td>
					<input id="aDBc_edd_license_key" name="aDBc_edd_license_key" type="text" class="regular-text" value="<?php echo $license_key_hidden; ?>" />
				</td>
				<td>
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save new license key', 'advanced-database-cleaner'); ?>"  />
					<?php //submit_button(); ?>
				</td>
			</tr>

			<?php if(false !== $license){ ?>
			<tr>
				<td>
					<b><?php _e('Activate license', 'advanced-database-cleaner'); ?></b>
				</td>
				<td>

					<?php if( $status !== false && $status == 'valid' ) { ?>
						<span style="color:green;font-size:13px; background:#eee;padding:4px 8px;vertical-align:middle;border:1px solid #f1f1f1"><b><?php _e('Active', 'advanced-database-cleaner'); ?></b></span>
						<?php wp_nonce_field( 'aDBc_edd_nonce', 'aDBc_edd_nonce' ); ?>
						<input style="vertical-align:middle" type="submit" class="button-secondary" name="aDBc_edd_license_deactivate" value="<?php _e('Deactivate license', 'advanced-database-cleaner'); ?>"/>
					<?php } else { ?>
						<span style="color:red;font-size:13px; background:#f9f9f9;padding:4px 8px;vertical-align:middle;border:1px solid #f1f1f1"><b><?php _e('Inactive', 'advanced-database-cleaner'); ?></b></span>
						<?php wp_nonce_field( 'aDBc_edd_nonce', 'aDBc_edd_nonce' ); ?>
						<input style="vertical-align:middle" type="submit" class="button-secondary" name="aDBc_edd_license_activate" value="<?php _e('Activate license', 'advanced-database-cleaner'); ?>"/>
					<?php } ?>

				</td>
			</tr>
			<?php } ?>
		</table>

	</form>
	<?php
}

function aDBc_edd_register_option() {
	// creates our settings in the options table
	register_setting('aDBc_edd_license', 'aDBc_edd_license_key', 'aDBc_edd_sanitize_license');
}
add_action('admin_init', 'aDBc_edd_register_option');

function aDBc_edd_sanitize_license( $new ) {
	$old = get_option( 'aDBc_edd_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'aDBc_edd_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

/************************************************
* this illustrates how to activate a license key
************************************************/

function aDBc_edd_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['aDBc_edd_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'aDBc_edd_nonce', 'aDBc_edd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'aDBc_edd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_id' 	 => ADBC_EDD_ITEM_ID,
			'item_name'  => urlencode( ADBC_EDD_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __('An error occurred, please try again.', 'advanced-database-cleaner');
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__('Your license key expired on %s.', 'advanced-database-cleaner'),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'disabled' :
					case 'revoked' :

						$message = __('Your license key has been disabled.', 'advanced-database-cleaner');
						break;

					case 'missing' :

						$message = __('Invalid license key', 'advanced-database-cleaner');
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __('Your license is not active for this URL.', 'advanced-database-cleaner');
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __('This appears to be an invalid license key for %s.', 'advanced-database-cleaner'), ADBC_EDD_ITEM_NAME);
						break;

					case 'no_activations_left':

						$message = __('Your license key has reached its activation limit.', 'advanced-database-cleaner');
						break;

					default :

						$message = __('An error occurred, please try again.', 'advanced-database-cleaner');
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'admin.php?page=' . ADBC_EDD_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"

		update_option('aDBc_edd_license_status', $license_data->license);
		wp_redirect(admin_url('admin.php?page=' . ADBC_EDD_PLUGIN_LICENSE_PAGE));
		exit();
	}
}
add_action('admin_init', 'aDBc_edd_activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function aDBc_edd_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['aDBc_edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'aDBc_edd_nonce', 'aDBc_edd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'aDBc_edd_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_id' 	 => ADBC_EDD_ITEM_ID,
			'item_name'  => urlencode( ADBC_EDD_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __('An error occurred, please try again.', 'advanced-database-cleaner');
			}

			$base_url = admin_url( 'admin.php?page=' . ADBC_EDD_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'aDBc_edd_license_status' );
			delete_option( 'aDBc_edd_license_key' );
		}

		wp_redirect( admin_url( 'admin.php?page=' . ADBC_EDD_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action('admin_init', 'aDBc_edd_deactivate_license');

/***********************************************
* Deactivate a license key after uninstall.
* This will descrease the site count
***********************************************/
function aDBc_edd_deactivate_license_after_uninstall() {

	// retrieve the license from the database
	$license = trim( get_option( 'aDBc_edd_license_key' ) );

	// data to send in our API request
	$api_params = array(
		'edd_action'=> 'deactivate_license',
		'license' 	=> $license,
		'item_id' 	=> ADBC_EDD_ITEM_ID,
		'item_name' => urlencode( ADBC_EDD_ITEM_NAME ), // the name of our product in EDD
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
}

/*********************************************************************************************
* this illustrates how to check if a license key is still valid the updater does this for you, 
* so this is only needed if you want to do something custom
*********************************************************************************************/
function aDBc_edd_check_license(){
	global $wp_version;
	$license = trim(get_option('aDBc_edd_license_key'));
	$api_params = array(
		'edd_action' 	=> 'check_license',
		'license' 		=> $license,
		'item_id' 		=> ADBC_EDD_ITEM_ID,
		'item_name' 	=> urlencode( ADBC_EDD_ITEM_NAME ),
		'url'       	=> home_url()
	);
	// Call the custom API.
	$response = wp_remote_post(ADBC_EDD_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
	if(is_wp_error($response))
		return false;
	$license_data = json_decode(wp_remote_retrieve_body($response));
	if($license_data->license == 'valid'){
		echo 'valid'; exit;
		// this license is still valid
	}else{
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}

/********************************************************************************************************
 * This is a means of catching errors from the activation method above and displaying it to the customer
 *******************************************************************************************************/
function aDBc_edd_admin_notices(){
	if(isset($_GET['sl_activation']) && ! empty($_GET['message'])){
		switch($_GET['sl_activation']){
			case 'false':
				$message = urldecode($_GET['message']);
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;
			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;
		}
	}
}
add_action('admin_notices', 'aDBc_edd_admin_notices');

/*****************************************************************************************
* Check if a license is activated
*****************************************************************************************/
function aDBc_edd_is_license_activated(){
	$license_status = trim( get_option( 'aDBc_edd_license_status'));
	if($license_status == 'valid'){
		return true;
	}else{
		return false;
	}
}
