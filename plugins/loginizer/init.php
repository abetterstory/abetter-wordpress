<?php

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

define('LOGINIZER_VERSION', '1.6.8');
define('LOGINIZER_DIR', dirname(LOGINIZER_FILE));
define('LOGINIZER_URL', plugins_url('', LOGINIZER_FILE));
define('LOGINIZER_PRO_URL', 'https://loginizer.com/features#compare');
define('LOGINIZER_PRICING_URL', 'https://loginizer.com/pricing');
define('LOGINIZER_DOCS', 'https://loginizer.com/docs/');

include_once(LOGINIZER_DIR.'/functions.php');

// Ok so we are now ready to go
register_activation_hook(LOGINIZER_FILE, 'loginizer_activation');

// Is called when the ADMIN enables the plugin
function loginizer_activation(){

	global $wpdb;

	$sql = array();
	
	$sql[] = "DROP TABLE IF EXISTS `".$wpdb->prefix."loginizer_logs`";
	
	$sql[] = "CREATE TABLE `".$wpdb->prefix."loginizer_logs` (
				`username` varchar(255) NOT NULL DEFAULT '',
				`time` int(10) NOT NULL DEFAULT '0',
				`count` int(10) NOT NULL DEFAULT '0',
				`lockout` int(10) NOT NULL DEFAULT '0',
				`ip` varchar(255) NOT NULL DEFAULT '',
				`url` varchar(255) NOT NULL DEFAULT '',
				UNIQUE KEY `ip` (`ip`)
			) DEFAULT CHARSET=utf8;";

	foreach($sql as $sk => $sv){
		$wpdb->query($sv);
	}
	
	add_option('loginizer_version', LOGINIZER_VERSION);
	add_option('loginizer_options', array());
	add_option('loginizer_last_reset', 0);
	add_option('loginizer_whitelist', array());
	add_option('loginizer_blacklist', array());
	add_option('loginizer_2fa_whitelist', array());

}

/**
 * Updates the database structure for Loginizer
 *
 * If the plugin files are updated but database structure is not updated
 * this function will update the database structure as per the plugin version
 * NOTE: This does not update plugin files it just updates the database structure
 */
function loginizer_update_check(){

global $wpdb;

	$sql = array();
	$current_version = get_option('loginizer_version');
	
	// It must be the 1.0 pre stuff
	if(empty($current_version)){
		$current_version = get_option('lz_version');
	}
	
	$version = (int) str_replace('.', '', $current_version);
	
	// No update required
	if($current_version == LOGINIZER_VERSION){
		return true;
	}
	
	// Is it first run ?
	if(empty($current_version)){
		
		// Reinstall
		loginizer_activation();
		
		// Trick the following if conditions to not run
		$version = (int) str_replace('.', '', LOGINIZER_VERSION);
		
	}
	
	// Is it less than 1.0.1 ?
	if($version < 101){
		
		// TODO : GET the existing settings
	
		// Get the existing settings		
		$lz_failed_logs = lz_selectquery("SELECT * FROM `".$wpdb->prefix."lz_failed_logs`;", 1);
		$lz_options = lz_selectquery("SELECT * FROM `".$wpdb->prefix."lz_options`;", 1);
		$lz_iprange = lz_selectquery("SELECT * FROM `".$wpdb->prefix."lz_iprange`;", 1);
				
		// Delete the three tables
		$sql = array();
		$sql[] = "DROP TABLE IF EXISTS ".$wpdb->prefix."lz_failed_logs;";
		$sql[] = "DROP TABLE IF EXISTS ".$wpdb->prefix."lz_options;";
		$sql[] = "DROP TABLE IF EXISTS ".$wpdb->prefix."lz_iprange;";

		foreach($sql as $sk => $sv){
			$wpdb->query($sv);
		}
		
		// Delete option
		delete_option('lz_version');
	
		// Reinstall
		loginizer_activation();
	
		// TODO : Save the existing settings

		// Update the existing failed logs to new table
		if(is_array($lz_failed_logs)){
			foreach($lz_failed_logs as $fk => $fv){
				$insert_data = array('username' => $fv['username'], 
									'time' => $fv['time'], 
									'count' => $fv['count'], 
									'lockout' => $fv['lockout'], 
									'ip' => $fv['ip']);
									
				$format = array('%s','%d','%d','%d','%s');
				
				$wpdb->insert($wpdb->prefix.'loginizer_logs', $insert_data, $format);
			}			
		}

		// Update the existing options to new structure
		if(is_array($lz_options)){
			foreach($lz_options as $ok => $ov){
				
				if($ov['option_name'] == 'lz_last_reset'){
					update_option('loginizer_last_reset', $ov['option_value']);
					continue;
				}
				
				$old_option[str_replace('lz_', '', $ov['option_name'])] = $ov['option_value'];
			}
			// Save the options
			update_option('loginizer_options', $old_option);
		}

		// Update the existing iprange to new structure
		if(is_array($lz_iprange)){
			
			$old_blacklist = array();
			$old_whitelist = array();
			$bid = 1;
			$wid = 1;
			foreach($lz_iprange as $ik => $iv){
				
				if(!empty($iv['blacklist'])){
					$old_blacklist[$bid] = array();
					$old_blacklist[$bid]['start'] = long2ip($iv['start']);
					$old_blacklist[$bid]['end'] = long2ip($iv['end']);
					$old_blacklist[$bid]['time'] = strtotime($iv['date']);
					$bid = $bid + 1;
				}
				
				if(!empty($iv['whitelist'])){
					$old_whitelist[$wid] = array();
					$old_whitelist[$wid]['start'] = long2ip($iv['start']);
					$old_whitelist[$wid]['end'] = long2ip($iv['end']);
					$old_whitelist[$wid]['time'] = strtotime($iv['date']);
					$wid = $wid + 1;
				}
			}
			
			if(!empty($old_blacklist)) update_option('loginizer_blacklist', $old_blacklist);
			if(!empty($old_whitelist)) update_option('loginizer_whitelist', $old_whitelist);
		}
		
	}
	
	// Is it less than 1.3.9 ?
	if($version < 139){
		
		$wpdb->query("ALTER TABLE ".$wpdb->prefix."loginizer_logs  ADD `url` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ip`;");
	
	}
	
	// Save the new Version
	update_option('loginizer_version', LOGINIZER_VERSION);
	
	// In Sitepad Math Captcha is enabled by default
	if(defined('SITEPAD') && get_option('loginizer_captcha') === false){
		$option['captcha_no_google'] = 1;
		add_option('loginizer_captcha', $option);
	}
	
}

// Add the action to load the plugin 
add_action('plugins_loaded', 'loginizer_load_plugin');

// The function that will be called when the plugin is loaded
function loginizer_load_plugin(){
	
	global $loginizer;
	
	// Check if the installed version is outdated
	loginizer_update_check();
	
	// Set the array
	$loginizer = array();
	
	$loginizer['prefix'] = !defined('SITEPAD') ? 'Loginizer ' : 'SitePad ';
	$loginizer['app'] = !defined('SITEPAD') ? 'WordPress' : 'SitePad';
	$loginizer['login_basename'] = !defined('SITEPAD') ? 'wp-login.php' : 'login.php';
	$loginizer['wp-includes'] = !defined('SITEPAD') ? 'wp-includes' : 'site-inc';
	
	// The IP Method to use
	$loginizer['ip_method'] = get_option('loginizer_ip_method');
	if($loginizer['ip_method'] == 3){
		$loginizer['custom_ip_method'] = get_option('loginizer_custom_ip_method');
	}
	
	// Load settings
	$options = get_option('loginizer_options');
	$loginizer['max_retries'] = empty($options['max_retries']) ? 3 : $options['max_retries'];
	$loginizer['lockout_time'] = empty($options['lockout_time']) ? 900 : $options['lockout_time']; // 15 minutes
	$loginizer['max_lockouts'] = empty($options['max_lockouts']) ? 5 : $options['max_lockouts'];
	$loginizer['lockouts_extend'] = empty($options['lockouts_extend']) ? 86400 : $options['lockouts_extend']; // 24 hours
	$loginizer['reset_retries'] = empty($options['reset_retries']) ? 86400 : $options['reset_retries']; // 24 hours
	$loginizer['notify_email'] = empty($options['notify_email']) ? 0 : $options['notify_email'];
	$loginizer['notify_email_address'] = lz_is_multisite() ? get_site_option('admin_email') : get_option('admin_email');
	
	if(!empty($options['notify_email_address'])){
		$loginizer['notify_email_address'] = $options['notify_email_address'];
		$loginizer['custom_notify_email'] = 1;
	}
	
	// Default messages
	$loginizer['d_msg']['inv_userpass'] = __('Incorrect Username or Password', 'loginizer');
	$loginizer['d_msg']['ip_blacklisted'] = __('Your IP has been blacklisted', 'loginizer');
	$loginizer['d_msg']['attempts_left'] = __('attempt(s) left', 'loginizer');
	$loginizer['d_msg']['lockout_err'] = __('You have exceeded maximum login retries<br /> Please try after', 'loginizer');
	$loginizer['d_msg']['minutes_err'] = __('minute(s)', 'loginizer');
	$loginizer['d_msg']['hours_err'] = __('hour(s)', 'loginizer');
	
	// Message Strings
	$loginizer['msg'] = get_option('loginizer_msg');
	
	foreach($loginizer['d_msg'] as $lk => $lv){
		if(empty($loginizer['msg'][$lk])){
			$loginizer['msg'][$lk] = $loginizer['d_msg'][$lk];
		}
	}
	
	$loginizer['2fa_d_msg']['otp_app'] = __('Please enter the OTP as seen in your App', 'loginizer');
	$loginizer['2fa_d_msg']['otp_email'] = __('Please enter the OTP emailed to you', 'loginizer');
	$loginizer['2fa_d_msg']['otp_field'] = __('One Time Password', 'loginizer');
	$loginizer['2fa_d_msg']['otp_question'] = __('Please answer your security question', 'loginizer');
	$loginizer['2fa_d_msg']['otp_answer'] = __('Your Answer', 'loginizer');
	
	// Message Strings
	$loginizer['2fa_msg'] = get_option('loginizer_2fa_msg');
	
	foreach($loginizer['2fa_d_msg'] as $lk => $lv){
		if(empty($loginizer['2fa_msg'][$lk])){
			$loginizer['2fa_msg'][$lk] = $loginizer['2fa_d_msg'][$lk];
		}
	}
		
	// Load the blacklist and whitelist
	$loginizer['blacklist'] = get_option('loginizer_blacklist');
	$loginizer['whitelist'] = get_option('loginizer_whitelist');
	$loginizer['2fa_whitelist'] = get_option('loginizer_2fa_whitelist');
	
	// It should not be false
	if(empty($loginizer['2fa_whitelist'])){
		$loginizer['2fa_whitelist'] = array();
	}
	
	// When was the database cleared last time
	$loginizer['last_reset']  = get_option('loginizer_last_reset');
	
	//print_r($loginizer);
	
	// Clear retries
	if((time() - $loginizer['last_reset']) >= $loginizer['reset_retries']){
		loginizer_reset_retries();
	}
	
	$ins_time = get_option('loginizer_ins_time');
	if(empty($ins_time)){
		$ins_time = time();
		update_option('loginizer_ins_time', $ins_time);
	}
	$loginizer['ins_time'] = $ins_time;
	
	// Set the current IP
	$loginizer['current_ip'] = lz_getip();
	
	// Is Brute Force Disabled ?
	$loginizer['disable_brute'] = get_option('loginizer_disable_brute');

	// Filters and actions
	if(empty($loginizer['disable_brute'])){
	
		// Use this to verify before WP tries to login
		// Is always called and is the first function to be called
		//add_action('wp_authenticate', 'loginizer_wp_authenticate', 10, 2);// Not called by XML-RPC
		add_filter('authenticate', 'loginizer_wp_authenticate', 10001, 3);// This one is called by xmlrpc as well as GUI
		
		// Is called when a login attempt fails
		// Hence Update our records that the login failed
		add_action('wp_login_failed', 'loginizer_login_failed');
		
		// Is called before displaying the error message so that we dont show that the username is wrong or the password
		// Update Error message
		add_action('wp_login_errors', 'loginizer_error_handler', 10001, 2);
		add_action('woocommerce_login_failed', 'loginizer_woocommerce_error_handler', 10001);
	
	}
	
	// ----------------
	// PRO INIT
	// ----------------
	
	// Email to Login
	$options = get_option('loginizer_epl');
	$loginizer['pl_d_sub'] = 'Login at $site_name';
	$loginizer['pl_d_msg'] = 'Hi,

A login request was submitted for your account $email at :
$site_name - $site_url

Login at $site_name by visiting this url : 
$login_url

If you have not requested for the Login URL, please ignore this email.

Regards,
$site_name';
	$loginizer['email_pass_less'] = empty($options['email_pass_less']) ? 0 : $options['email_pass_less'];
	$loginizer['passwordless_sub'] = empty($options['passwordless_sub']) ? $loginizer['pl_d_sub'] : $options['passwordless_sub'];
	$loginizer['passwordless_msg'] = empty($options['passwordless_msg']) ? $loginizer['pl_d_msg'] : $options['passwordless_msg'];
	$loginizer['passwordless_msg_is_custom'] = empty($options['passwordless_msg']) ? 0 : 1;
	$loginizer['passwordless_html'] = empty($options['passwordless_html']) ? 0 : $options['passwordless_html'];
	
	// 2FA OTP Email to Login
	$options = get_option('loginizer_2fa_email_template');
	$loginizer['2fa_email_d_sub'] = 'OTP : Login at $site_name';
	$loginizer['2fa_email_d_msg'] = 'Hi,

A login request was submitted for your account $email at :
$site_name - $site_url

Please use the following One Time password (OTP) to login : 
$otp

Note : The OTP expires after 10 minutes.

If you haven\'t requested for the OTP, please ignore this email.

Regards,
$site_name';

	$loginizer['2fa_email_sub'] = empty($options['2fa_email_sub']) ? $loginizer['2fa_email_d_sub'] : $options['2fa_email_sub'];
	$loginizer['2fa_email_msg'] = empty($options['2fa_email_msg']) ? $loginizer['2fa_email_d_msg'] : $options['2fa_email_msg'];
	
	// For SitePad its always on
	if(defined('SITEPAD')){
		$loginizer['email_pass_less'] = 1;
	}
	
	// Captcha
	$options = get_option('loginizer_captcha');
	$loginizer['captcha_type'] = empty($options['captcha_type']) ? '' : $options['captcha_type'];
	$loginizer['captcha_key'] = empty($options['captcha_key']) ? '' : $options['captcha_key'];
	$loginizer['captcha_secret'] = empty($options['captcha_secret']) ? '' : $options['captcha_secret'];
	$loginizer['captcha_theme'] = empty($options['captcha_theme']) ? 'light' : $options['captcha_theme'];
	$loginizer['captcha_size'] = empty($options['captcha_size']) ? 'normal' : $options['captcha_size'];
	$loginizer['captcha_lang'] = empty($options['captcha_lang']) ? '' : $options['captcha_lang'];
	$loginizer['captcha_user_hide'] = !isset($options['captcha_user_hide']) ? 0 : $options['captcha_user_hide'];
	$loginizer['captcha_no_css_login'] = !isset($options['captcha_no_css_login']) ? 0 : $options['captcha_no_css_login'];
	$loginizer['captcha_no_js'] = 1;
	$loginizer['captcha_login'] = !isset($options['captcha_login']) ? 1 : $options['captcha_login'];
	$loginizer['captcha_lostpass'] = !isset($options['captcha_lostpass']) ? 1 : $options['captcha_lostpass'];
	$loginizer['captcha_resetpass'] = !isset($options['captcha_resetpass']) ? 1 : $options['captcha_resetpass'];
	$loginizer['captcha_register'] = !isset($options['captcha_register']) ? 1 : $options['captcha_register'];
	$loginizer['captcha_comment'] = !isset($options['captcha_comment']) ? 1 : $options['captcha_comment'];
	$loginizer['captcha_wc_checkout'] = !isset($options['captcha_wc_checkout']) ? 1 : $options['captcha_wc_checkout'];
	
	$loginizer['captcha_no_google'] =  !isset($options['captcha_no_google']) ? 0 : $options['captcha_no_google'];
	$loginizer['captcha_text'] =  empty($options['captcha_text']) ? __('Math Captcha', 'loginizer') : $options['captcha_text'];
	$loginizer['captcha_time'] =  empty($options['captcha_time']) ? 300 : $options['captcha_time'];
	$loginizer['captcha_words'] =  !isset($options['captcha_words']) ? 0 : $options['captcha_words'];
	$loginizer['captcha_add'] =  !isset($options['captcha_add']) ? 1 : $options['captcha_add'];
	$loginizer['captcha_subtract'] =  !isset($options['captcha_subtract']) ? 1 : $options['captcha_subtract'];
	$loginizer['captcha_multiply'] =  !isset($options['captcha_multiply']) ? 0 : $options['captcha_multiply'];
	$loginizer['captcha_divide'] =  !isset($options['captcha_divide']) ? 0 : $options['captcha_divide'];
	
	// 2fa/question
	$options = get_option('loginizer_2fa');
	$loginizer['2fa_app'] = !isset($options['2fa_app']) ? 0 : $options['2fa_app'];
	$loginizer['2fa_email'] = !isset($options['2fa_email']) ? 0 : $options['2fa_email'];
	$loginizer['2fa_email_force'] = !isset($options['2fa_email_force']) ? 0 : $options['2fa_email_force'];
	$loginizer['2fa_sms'] = !isset($options['2fa_sms']) ? 0 : $options['2fa_sms'];
	$loginizer['question'] = !isset($options['question']) ? 0 : $options['question'];
	$loginizer['2fa_default'] = empty($options['2fa_default']) ? 'question' : $options['2fa_default'];
	$loginizer['2fa_roles'] = empty($options['2fa_roles']) ? array() : $options['2fa_roles'];
	
	// Security Settings
	$options = get_option('loginizer_security');
	$loginizer['login_slug'] = empty($options['login_slug']) ? '' : $options['login_slug'];
	$loginizer['rename_login_secret'] = empty($options['rename_login_secret']) ? '' : $options['rename_login_secret'];
	$loginizer['xmlrpc_slug'] = empty($options['xmlrpc_slug']) ? '' : $options['xmlrpc_slug'];
	$loginizer['xmlrpc_disable'] = empty($options['xmlrpc_disable']) ? '' : $options['xmlrpc_disable'];// Disable XML-RPC
	$loginizer['pingbacks_disable'] = empty($options['pingbacks_disable']) ? '' : $options['pingbacks_disable'];// Disable Pingbacks
	
	// Admin Slug Settings
	$options = get_option('loginizer_wp_admin');
	$loginizer['admin_slug'] = empty($options['admin_slug']) ? '' : $options['admin_slug'];
	$loginizer['restrict_wp_admin'] = empty($options['restrict_wp_admin']) ? '' : $options['restrict_wp_admin'];
	$loginizer['wp_admin_msg'] = empty($options['wp_admin_msg']) ? '' : $options['wp_admin_msg'];
	
	// Checksum Settings
	$options = get_option('loginizer_checksums');
	$loginizer['disable_checksum'] = empty($options['disable_checksum']) ? '' : $options['disable_checksum'];
	$loginizer['checksum_time'] = empty($options['checksum_time']) ? '' : $options['checksum_time'];
	$loginizer['checksum_frequency'] = empty($options['checksum_frequency']) ? 7 : $options['checksum_frequency'];
	$loginizer['no_checksum_email'] = empty($options['no_checksum_email']) ? '' : $options['no_checksum_email'];
	$loginizer['checksums_last_run'] = get_option('loginizer_checksums_last_run');
	
	// Auto Blacklist Usernames
	$loginizer['username_blacklist'] = get_option('loginizer_username_blacklist');
	
	$loginizer['domains_blacklist'] = get_option('loginizer_domains_blacklist');
	
	$loginizer['wp_admin_d_msg'] = __('LZ : Not allowed via WP-ADMIN. Please access over the new Admin URL', 'loginizer');
	
	// ----------------
	// PRO INIT END
	// ----------------
	
	// Is the premium features there ?
	if(file_exists(LOGINIZER_DIR.'/premium.php')){
		
		// Include the file
		include_once(LOGINIZER_DIR.'/premium.php');
		
		loginizer_security_init();
	
	// Its the free version
	}else{
		
		// The promo time
		$loginizer['promo_time'] = get_option('loginizer_promo_time');
		if(empty($loginizer['promo_time'])){
			$loginizer['promo_time'] = time();
			update_option('loginizer_promo_time', $loginizer['promo_time']);
		}
		
		// Are we to show the loginizer promo
		if(!empty($loginizer['promo_time']) && $loginizer['promo_time'] > 0 && $loginizer['promo_time'] < (time() - (30*24*3600))){
		
			add_action('admin_notices', 'loginizer_promo');
		
		}
		
		// Are we to disable the promo
		if(isset($_GET['loginizer_promo']) && (int)$_GET['loginizer_promo'] == 0){
			update_option('loginizer_promo_time', (0 - time()) );
			die('DONE');
		}
		
	}

}

// Show the promo
function loginizer_promo(){
	
	echo '
<style>
.lz_button {
background-color: #4CAF50; /* Green */
border: none;
color: white;
padding: 8px 16px;
text-align: center;
text-decoration: none;
display: inline-block;
font-size: 16px;
margin: 4px 2px;
-webkit-transition-duration: 0.4s; /* Safari */
transition-duration: 0.4s;
cursor: pointer;
}

.lz_button:focus{
border: none;
color: white;
}

.lz_button1 {
color: white;
background-color: #4CAF50;
border:3px solid #4CAF50;
}

.lz_button1:hover {
box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
color: white;
border:3px solid #4CAF50;
}

.lz_button2 {
color: white;
background-color: #0085ba;
}

.lz_button2:hover {
box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
color: white;
}

.lz_button3 {
color: white;
background-color: #365899;
}

.lz_button3:hover {
box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
color: white;
}

.lz_button4 {
color: white;
background-color: rgb(66, 184, 221);
}

.lz_button4:hover {
box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
color: white;
}

.loginizer_promo-close{
float:right;
text-decoration:none;
margin: 5px 10px 0px 0px;
}

.loginizer_promo-close:hover{
color: red;
}
</style>	

<script>
jQuery(document).ready( function() {
	(function($) {
		$("#loginizer_promo .loginizer_promo-close").click(function(){
			var data;
			
			// Hide it
			$("#loginizer_promo").hide();
			
			// Save this preference
			$.post("'.admin_url('?loginizer_promo=0').'", data, function(response) {
				//alert(response);
			});
		});
	})(jQuery);
});
</script>

<div class="notice notice-success" id="loginizer_promo" style="min-height:120px">
	<a class="loginizer_promo-close" href="javascript:" aria-label="Dismiss this Notice">
		<span class="dashicons dashicons-dismiss"></span> Dismiss
	</a>
	<img src="'.LOGINIZER_URL.'/loginizer-200.png" style="float:left; margin:10px 20px 10px 10px" width="100" />
	<p style="font-size:16px">We are glad you like Loginizer and have been using it since the past few days. It is time to take the next step </p>
	<p>
		<a class="lz_button lz_button1" target="_blank" href="https://loginizer.com/features">Upgrade to Pro</a>
		<a class="lz_button lz_button2" target="_blank" href="https://wordpress.org/support/view/plugin-reviews/loginizer">Rate it 5★\'s</a>
		<a class="lz_button lz_button3" target="_blank" href="https://www.facebook.com/Loginizer-815504798591884/">Like Us on Facebook</a>
		<a class="lz_button lz_button4" target="_blank" href="https://twitter.com/home?status='.rawurlencode('I use @loginizer to secure my #WordPress site - https://loginizer.com').'">Tweet about Loginizer</a>
	</p>
</div>';

}

// Should return NULL if everything is fine
function loginizer_wp_authenticate($user, $username, $password){
	
	global $loginizer, $lz_error, $lz_cannot_login, $lz_user_pass;
	
	if(!empty($username) && !empty($password)){
		$lz_user_pass = 1;
	}
	
	// Are you whitelisted ?
	if(loginizer_is_whitelisted()){
		$loginizer['ip_is_whitelisted'] = 1;
		return $user;
	}
	
	// Are you blacklisted ?
	if(loginizer_is_blacklisted()){
		$lz_cannot_login = 1;
		
		// This is used by WP Activity Log
		apply_filters( 'wp_login_blocked', $username );
		
		return new WP_Error('ip_blacklisted', implode('', $lz_error), 'loginizer');
	}
	
	// Is the username blacklisted ?
	if(function_exists('loginizer_user_blacklisted')){
		if(loginizer_user_blacklisted($username)){
			$lz_cannot_login = 1;
		
			// This is used by WP Activity Log
			apply_filters( 'wp_login_blocked', $username );
			
			return new WP_Error('user_blacklisted', implode('', $lz_error), 'loginizer');
		}
	}
	
	if(loginizer_can_login()){
		return $user;
	}
	
	$lz_cannot_login = 1;
		
	// This is used by WP Activity Log
	apply_filters( 'wp_login_blocked', $username );
	
	return new WP_Error('ip_blocked', implode('', $lz_error), 'loginizer');
	
}

function loginizer_can_login(){
	
	global $wpdb, $loginizer, $lz_error;
	
	// Get the logs
	$sel_query = $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."loginizer_logs` WHERE `ip` = %s", $loginizer['current_ip']);
	$result = lz_selectquery($sel_query);
	
	if(!empty($result['count']) && ($result['count'] % $loginizer['max_retries']) == 0){
		
		// Has he reached max lockouts ?
		if($result['lockout'] >= $loginizer['max_lockouts']){
			$loginizer['lockout_time'] = $loginizer['lockouts_extend'];
		}
		
		// Is he in the lockout time ?
		if($result['time'] >= (time() - $loginizer['lockout_time'])){
			$banlift = ceil((($result['time'] + $loginizer['lockout_time']) - time()) / 60);
			
			//echo 'Current Time '.date('d/M/Y H:i:s P', time()).'<br />';
			//echo 'Last attempt '.date('d/M/Y H:i:s P', $result['time']).'<br />';
			//echo 'Unlock Time '.date('d/M/Y H:i:s P', $result['time'] + $loginizer['lockout_time']).'<br />';
			
			$_time = $banlift.' '.$loginizer['msg']['minutes_err'];
			
			if($banlift > 60){
				$banlift = ceil($banlift / 60);
				$_time = $banlift.' '.$loginizer['msg']['hours_err'];
			}
			
			$lz_error['ip_blocked'] = $loginizer['msg']['lockout_err'].' '.$_time;
			
			return false;
		}
	}
	
	return true;
}

function loginizer_is_blacklisted(){
	
	global $wpdb, $loginizer, $lz_error;
	
	$blacklist = $loginizer['blacklist'];
	
	if(empty($blacklist)){
		return false;
	}
	  
	foreach($blacklist as $k => $v){
		
		// Is the IP in the blacklist ?
		if(inet_ptoi($v['start']) <= inet_ptoi($loginizer['current_ip']) && inet_ptoi($loginizer['current_ip']) <= inet_ptoi($v['end'])){
			$result = 1;
			break;
		}
		
		// Is it in a wider range ?
		if(inet_ptoi($v['start']) >= 0 && inet_ptoi($v['end']) < 0){
			
			// Since the end of the RANGE (i.e. current IP range) is beyond the +ve value of inet_ptoi, 
			// if the current IP is <= than the start of the range, it is within the range
			// OR
			// if the current IP is <= than the end of the range, it is within the range
			if(inet_ptoi($v['start']) <= inet_ptoi($loginizer['current_ip'])
				|| inet_ptoi($loginizer['current_ip']) <= inet_ptoi($v['end'])){				
				$result = 1;
				break;
			}
			
		}
		
	}
		
	// You are blacklisted
	if(!empty($result)){
		$lz_error['ip_blacklisted'] = $loginizer['msg']['ip_blacklisted'];
		return true;
	}
	
	return false;
	
}

function loginizer_is_whitelisted(){
	
	global $wpdb, $loginizer, $lz_error;
	
	$whitelist = $loginizer['whitelist'];
			
	if(empty($whitelist)){
		return false;
	}
	  
	foreach($whitelist as $k => $v){
		
		// Is the IP in the blacklist ?
		if(inet_ptoi($v['start']) <= inet_ptoi($loginizer['current_ip']) && inet_ptoi($loginizer['current_ip']) <= inet_ptoi($v['end'])){
			$result = 1;
			break;
		}
		
		// Is it in a wider range ?
		if(inet_ptoi($v['start']) >= 0 && inet_ptoi($v['end']) < 0){
			
			// Since the end of the RANGE (i.e. current IP range) is beyond the +ve value of inet_ptoi, 
			// if the current IP is <= than the start of the range, it is within the range
			// OR
			// if the current IP is <= than the end of the range, it is within the range
			if(inet_ptoi($v['start']) <= inet_ptoi($loginizer['current_ip'])
				|| inet_ptoi($loginizer['current_ip']) <= inet_ptoi($v['end'])){				
				$result = 1;
				break;
			}
			
		}
		
	}
		
	// You are whitelisted
	if(!empty($result)){
		return true;
	}
	
	return false;
	
}


// When the login fails, then this is called
// We need to update the database
function loginizer_login_failed($username, $is_2fa = ''){
	
	global $wpdb, $loginizer, $lz_cannot_login;
	
	// Some plugins are changing the value for username as null so we need to handle it before using it for the INSERT OR UPDATE query
	if(empty($username) || is_null($username)){
		$username = '';
	}
	
	$fail_type = 'Login';
	
	if(!empty($is_2fa)){
		$fail_type = '2FA';
	}

	if(empty($lz_cannot_login) && empty($loginizer['ip_is_whitelisted']) && empty($loginizer['no_loginizer_logs'])){
		
		$url = @addslashes((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$url = esc_url($url);
		
		$sel_query = $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."loginizer_logs` WHERE `ip` = %s", $loginizer['current_ip']);
		$result = lz_selectquery($sel_query);
		
		if(!empty($result)){
			$lockout = floor((($result['count']+1) / $loginizer['max_retries']));
			
			$update_data = array('username' => $username, 
								'time' => time(), 
								'count' => $result['count']+1, 
								'lockout' => $lockout, 
								'url' => $url);
			
			$where_data = array('ip' => $loginizer['current_ip']);
			
			$format = array('%s','%d','%d','%d','%s');
			$where_format = array('%s');
			
			$wpdb->update($wpdb->prefix.'loginizer_logs', $update_data, $where_data, $format, $where_format);
			
			// Do we need to email admin ?
			if(!empty($loginizer['notify_email']) && $lockout >= $loginizer['notify_email']){
				
				$sitename = lz_is_multisite() ? get_site_option('site_name') : get_option('blogname');
				$mail = array();
				$mail['to'] = $loginizer['notify_email_address'];	
				$mail['subject'] = 'Failed '.$fail_type.' Attempts from IP '.$loginizer['current_ip'].' ('.$sitename.')';
				$mail['message'] = 'Hi,

'.($result['count']+1).' failed '.strtolower($fail_type).' attempts and '.$lockout.' lockout(s) from IP '.$loginizer['current_ip'].' on your site :
'.home_url().'

Last '.$fail_type.' Attempt : '.date('d/M/Y H:i:s P', time()).'
Last User Attempt : '.$username.'
IP has been blocked until : '.date('d/M/Y H:i:s P', time() + $loginizer['lockout_time']).'

Regards,
Loginizer';

				@wp_mail($mail['to'], $mail['subject'], $mail['message']);
			}
		}else{
			$result = array();
			$result['count'] = 0;
			
			$insert_data = array('username' => $username, 
								'time' => time(), 
								'count' => 1, 
								'ip' => $loginizer['current_ip'], 
								'lockout' => 0, 
								'url' => $url);
								
			$format = array('%s','%d','%d','%s','%d','%s');
			
			$wpdb->insert($wpdb->prefix.'loginizer_logs', $insert_data, $format);
		}
	
		// We need to add one as this is a failed attempt as well
		$result['count'] = $result['count'] + 1;
		$loginizer['retries_left'] = ($loginizer['max_retries'] - ($result['count'] % $loginizer['max_retries']));
		$loginizer['retries_left'] = $loginizer['retries_left'] == $loginizer['max_retries'] ? 0 : $loginizer['retries_left'];
		
	}
}

// Handles the error of the password not being there
function loginizer_error_handler($errors, $redirect_to){
	
	global $wpdb, $loginizer, $lz_user_pass, $lz_cannot_login;
	
	//echo 'loginizer_error_handler :';print_r($errors->errors);echo '<br>';
	
	// Remove the empty password error
	if(is_wp_error($errors)){
		
		$codes = $errors->get_error_codes();
		
		foreach($codes as $k => $v){
			if($v == 'invalid_username' || $v == 'incorrect_password'){
				$show_error = 1;
			}
		}
		
		$errors->remove('invalid_username');
		$errors->remove('incorrect_password');
		
	}
	
	// Add the error
	if(!empty($lz_user_pass) && !empty($show_error) && empty($lz_cannot_login)){
		$errors->add('invalid_userpass', '<b>ERROR:</b> ' . $loginizer['msg']['inv_userpass']);
	}
	
	// Add the number of retires left as well
	if(count($errors->get_error_codes()) > 0 && isset($loginizer['retries_left'])){
		$errors->add('retries_left', loginizer_retries_left());
	}
	
	return $errors;
	
}



// Handles the error of the password not being there
function loginizer_woocommerce_error_handler(){
	
	global $wpdb, $loginizer, $lz_user_pass, $lz_cannot_login;
	
	if(function_exists('wc_add_notice')){
		wc_add_notice( loginizer_retries_left(), 'error' );
	}
	
}

// Returns a string with the number of retries left
function loginizer_retries_left(){
	
	global $wpdb, $loginizer, $lz_user_pass, $lz_cannot_login;
	
	// If we are to show the number of retries left
	if(isset($loginizer['retries_left'])){
		return '<b>'.$loginizer['retries_left'].'</b> '.$loginizer['msg']['attempts_left'];
	}
	
}

function loginizer_reset_retries(){
	
	global $wpdb, $loginizer;
	
	$deltime = time() - $loginizer['reset_retries'];
	
	$del_query = $wpdb->prepare("DELETE FROM `".$wpdb->prefix."loginizer_logs` WHERE `time` <= %d", $deltime);
	$result = $wpdb->query($del_query);
	
	update_option('loginizer_last_reset', time());
	
}

add_filter("plugin_action_links_$plugin_loginizer", 'loginizer_plugin_action_links');

// Add settings link on plugin page
function loginizer_plugin_action_links($links) {
	
	if(!defined('LOGINIZER_PREMIUM')){
		 $links[] = '<a href="'.LOGINIZER_PRO_URL.'" style="color:#3db634;" target="_blank">'._x('Upgrade', 'Plugin action link label.', 'loginizer').'</a>';
	}

	$settings_link = '<a href="admin.php?page=loginizer">Settings</a>';	
	array_unshift($links, $settings_link); 
	
	return $links;
}

add_action('admin_menu', 'loginizer_admin_menu');

// Shows the admin menu of Loginizer
function loginizer_admin_menu() {
	
	global $wp_version, $loginizer;
	
	if(!defined('SITEPAD')){
	
		// Add the menu page
		add_menu_page(__('Loginizer Dashboard', 'loginizer'), __('Loginizer Security', 'loginizer'), 'activate_plugins', 'loginizer', 'loginizer_page_dashboard');
	
		// Dashboard
		add_submenu_page('loginizer', __('Loginizer Dashboard', 'loginizer'), __('Dashboard', 'loginizer'), 'activate_plugins', 'loginizer', 'loginizer_page_dashboard');
	
	}else{
	
		// Add the menu page
		add_menu_page(__('Security', 'loginizer'), __('Security', 'loginizer'), 'activate_plugins', 'loginizer', 'loginizer_page_security', 'dashicons-shield', 85);
	
		// Rename Login
		add_submenu_page('loginizer', __('Security Settings', 'loginizer'), __('Rename Login', 'loginizer'), 'activate_plugins', 'loginizer', 'loginizer_page_security');
		
	}
	
	// Brute Force
	add_submenu_page('loginizer', __('Brute Force Settings', 'loginizer'), __('Brute Force', 'loginizer'), 'activate_plugins', 'loginizer_brute_force', 'loginizer_page_brute_force');
	
	// PasswordLess
	add_submenu_page('loginizer', __($loginizer['prefix'].'PasswordLess Settings', 'loginizer'), __('PasswordLess', 'loginizer'), 'activate_plugins', 'loginizer_passwordless', 'loginizer_page_passwordless');
	
	// Security Settings
	if(!defined('SITEPAD')){
	
		// Two Factor Auth
		add_submenu_page('loginizer', __($loginizer['prefix'].' Two Factor Authentication', 'loginizer'), __('Two Factor Auth', 'loginizer'), 'activate_plugins', 'loginizer_2fa', 'loginizer_page_2fa');
	
	}
	
	// reCaptcha
	add_submenu_page('loginizer', __($loginizer['prefix'].'reCAPTCHA Settings', 'loginizer'), __('reCAPTCHA', 'loginizer'), 'activate_plugins', 'loginizer_recaptcha', 'loginizer_page_recaptcha');
	
	// Security Settings
	if(!defined('SITEPAD')){
	
		// Security Settings
		add_submenu_page('loginizer', __($loginizer['prefix'].'Security Settings', 'loginizer'), __('Security Settings', 'loginizer'), 'activate_plugins', 'loginizer_security', 'loginizer_page_security');
		
		// File Checksums
		add_submenu_page('loginizer', __('Loginizer File Checksums', 'loginizer'), __('File Checksums', 'loginizer'), 'activate_plugins', 'loginizer_checksums', 'loginizer_page_checksums');
	
	}
	
	if(!defined('LOGINIZER_PREMIUM') && !empty($loginizer['ins_time']) && $loginizer['ins_time'] < (time() - (30*24*3600))){
		
		// Go Pro link
		add_submenu_page('loginizer', __('Loginizer Go Pro', 'loginizer'), __('Go Pro', 'loginizer'), 'activate_plugins', LOGINIZER_PRO_URL);
		
	}
	
}

// The Loginizer Admin Options Page
function loginizer_page_header($title = 'Loginizer'){
	
	global $loginizer;

?>
<style>
.lz-right-ul{
	padding-left: 10px !important;
}

.lz-right-ul li{
	list-style: circle !important;
}
</style>
<?php
	
	echo '<div style="margin: 10px 20px 0 2px;">	
<div class="metabox-holder columns-2">
<div class="postbox-container">	
<div id="top-sortables" class="meta-box-sortables ui-sortable">
	
	<table cellpadding="2" cellspacing="1" width="100%" class="fixed" border="0">
		<tr>
			<td valign="top"><h3>'.$loginizer['prefix'].$title.'</h3></td>';
			
	if(!defined('SITEPAD')){
			
		echo '<td align="right"><a target="_blank" class="button button-primary" href="https://wordpress.org/support/view/plugin-reviews/loginizer">'.__('Review Loginizer', 'loginizer').'</a></td>
			<td align="right" width="40"><a target="_blank" href="https://twitter.com/loginizer"><img src="'.LOGINIZER_URL.'/twitter.png" /></a></td>
			<td align="right" width="40"><a target="_blank" href="https://www.facebook.com/Loginizer-815504798591884"><img src="'.LOGINIZER_URL.'/facebook.png" /></a></td>';
			
	}
			
		echo '
		</tr>
	</table>
	<hr />
	
	<!--Main Table-->
	<table cellpadding="8" cellspacing="1" width="100%" class="fixed">
	<tr>
		<td valign="top">';

}

// The Loginizer Theme footer
function loginizer_page_footer(){
	
	if(!loginizer_is_premium()){
		echo '<script>
		jQuery("[loginizer-premium-only]").each(function(index) {
			jQuery(this).find( "input, textarea, select" ).attr("disabled", true);
		});
		</script>';
	}
	
	echo '</td>
	<td width="200" valign="top" id="loginizer-right-bar">';
			
	if(!defined('SITEPAD')){
	
		if(!defined('LOGINIZER_PREMIUM')){
		
			echo '
		<div class="postbox" style="min-width:0px !important;">
			<div class="postbox-header">
				<h2 class="hndle ui-sortable-handle">
					<span>Premium Version</span>
				</h2>
			</div>
			
			<div class="inside">
				<i>Upgrade to the premium version and get the following features </i>:<br>
				<ul class="lz-right-ul">
					<li>PasswordLess Login</li>
					<li>Two Factor Auth - Email</li>
					<li>Two Factor Auth - App</li>
					<li>Login Challenge Question</li>
					<li>reCAPTCHA</li>
					<li>Rename Login Page</li>
					<li>Disable XML-RPC</li>
					<li>And many more ...</li>
				</ul>
				<center><a class="button button-primary" target="_blank" href="'.LOGINIZER_PRICING_URL.'">Upgrade</a></center>
			</div>
		</div>';
		
		}else{
	
			echo '
		<div class="postbox" style="min-width:0px !important;">
			<div class="postbox-header">
			<h2 class="hndle ui-sortable-handle">
				<span>Recommendations</span>
			</h2>
			</div>
			<div class="inside">
				<i>We recommed that you enable atleast one of the following security features</i>:<br>
				<ul class="lz-right-ul">
					<li>Rename Login Page</li>
					<li>Login Challenge Question</li>
					<li>reCAPTCHA</li>
					<li>Two Factor Auth - Email</li>
					<li>Two Factor Auth - App</li>
					<li>Change \'admin\' Username</li>
				</ul>
			</div>
		</div>';
		}
		
		echo '
		<div class="postbox" style="min-width:0px !important;">
			<div class="postbox-header">
			<h2 class="hndle ui-sortable-handle">
				<span><a target="_blank" href="https://pagelayer.com/?from=loginizer-plugin"><img src="'.LOGINIZER_URL.'/images/pagelayer_product.png" width="100%" /></a></span>
			</h2>
			</div>
			<div class="inside">
				<i>Easily manage and make professional pages and content with our Pagelayer builder </i>:<br>
				<ul class="lz-right-ul">
					<li>30+ Free Widgets</li>
					<li>60+ Premium Widgets</li>
					<li>400+ Premium Sections</li>
					<li>Theme Builder</li>
					<li>WooCommerce Builder</li>
					<li>Theme Creator and Exporter</li>
					<li>Form Builder</li>
					<li>Popup Builder</li>
					<li>And many more ...</li>
				</ul>
				<center><a class="button button-primary" target="_blank" href="https://wordpress.org/plugins/pagelayer/">Visit Pagelayer</a></center>
			</div>
		</div>';
		
		echo '
		<div class="postbox" style="min-width:0px !important;">
			<div class="postbox-header">
			<h2 class="hndle ui-sortable-handle">
				<span><a target="_blank" href="https://wpcentral.co/?from=loginizer-plugin"><img src="'.LOGINIZER_URL.'/images/wpcentral_product.png" width="100%" /></a></span>
			</h2>
			</div>
			<div class="inside">
				<i>Manage all your WordPress sites from <b>1 dashboard</b> </i>:<br>
				<ul class="lz-right-ul">
					<li>1-click Admin Access</li>
					<li>Update WordPress</li>
					<li>Update Themes</li>
					<li>Update Plugins</li>
					<li>Backup your WordPress Site</li>
					<li>Plugins & Theme Management</li>
					<li>Post Management</li>
					<li>And many more ...</li>
				</ul>
				<center><a class="button button-primary" target="_blank" href="https://wpcentral.co/?from=loginizer-plugin">Visit wpCentral</a></center>
			</div>
		</div>';
	
	}
	
	echo '</td>
	</tr>
	</table>';
	
	if(!defined('SITEPAD')){
	
		echo '<br />
	<div style="width:45%;background:#FFF;padding:15px; margin:auto">
		<b>Let your friends know that you have secured your website :</b>
		<form method="get" action="https://twitter.com/intent/tweet" id="tweet" onsubmit="return dotweet(this);">
			<textarea name="text" cols="45" row="3" style="resize:none;">I just secured my @WordPress site against #bruteforce using @loginizer</textarea>
			&nbsp; &nbsp; <input type="submit" value="Tweet!" class="button button-primary" onsubmit="return false;" id="twitter-btn" style="margin-top:20px;"/>
		</form>
		
	</div>
	<br />
	
	<script>
	function dotweet(ele){
		window.open(jQuery("#"+ele.id).attr("action")+"?"+jQuery("#"+ele.id).serialize(), "_blank", "scrollbars=no, menubar=no, height=400, width=500, resizable=yes, toolbar=no, status=no");
		return false;
	}
	</script>
	
	<hr />
	<a href="http://loginizer.com" target="_blank">Loginizer</a> v'.LOGINIZER_VERSION.'. You can report any bugs <a href="http://wordpress.org/support/plugin/loginizer" target="_blank">here</a>.';
	
	}
	
	echo '
</div>	
</div>
</div>
</div>';

}

// The Loginizer Admin Options Page
function loginizer_page_dashboard(){
	
	global $loginizer, $lz_error, $lz_env;
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	// Dismiss the announcement
	if(isset($_GET['dismiss_announcement'])){
		update_option('loginizer_no_announcement', 1);
	}

	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	do_action('loginizer_pre_page_dashboard');
	
	// Is there a IP Method ?
	if(isset($_POST['save_lz_ip_method'])){
		
		$ip_method = (int) lz_optpost('lz_ip_method');
		$custom_ip_method = lz_optpost('lz_custom_ip_method');
		
		if($ip_method >= 0 && $ip_method <= 3){
			update_option('loginizer_ip_method', $ip_method);
		}
		
		// Custom Method name ?
		if($ip_method == 3){
			update_option('loginizer_custom_ip_method', $custom_ip_method);
		}
		
	}
	
	loginizer_page_dashboard_T();
	
}

// The Loginizer Admin Options Page - THEME
function loginizer_page_dashboard_T(){
	
	global $loginizer, $lz_error, $lz_env;

	loginizer_page_header('Dashboard');
?>
<style>
.welcome-panel{
	margin: 0px;
	padding: 10px;
}

input[type="text"], textarea, select {
    width: 70%;
}

.form-table label{
	font-weight:bold;
}

.exp{
	font-size:12px;
}
</style>
		
	<?php 
	$lz_ip = lz_getip();
	
	if($lz_ip != '127.0.0.1' && @$_SERVER['SERVER_ADDR'] == $lz_ip){
		echo '<div class="update-message notice error inline notice-error notice-alt"><p style="color:red"> &nbsp; Your Server IP Address seems to match the Client IP detected by Loginizer. You might want to change the IP detection method to HTTP_X_FORWARDED_FOR under System Information section.</p></div><br>';
	 }
	
	loginizer_newsletter_subscribe();
	
	$hide_announcement = get_option('loginizer_no_announcement');
	if(empty($hide_announcement)){
		echo '<div id="message" class="welcome-panel">'. __('<a href="https://loginizer.com/blog/loginizer-has-been-acquired-by-softaculous/" target="_blank" style="text-decoration:none;">We are excited to announce that we have joined forces with Softaculous and have been acquired by them 😊. Read full announcement here.</a>', 'loginizer'). '<a class="welcome-panel-close" style="top:3px;right:2px;" href="'.menu_page_url('loginizer', false).'&dismiss_announcement=1" aria-label="Dismiss announcement"></a></div><br />';
	}
		
	echo '<div class="welcome-panel">Thank you for choosing Loginizer! Many more features coming soon... &nbsp; Review Loginizer at WordPress &nbsp; &nbsp; <a href="https://wordpress.org/support/view/plugin-reviews/loginizer" class="button button-primary" target="_blank">Add Review</a></div><br />';

	// Saved ?
	if(!empty($GLOBALS['lz_saved'])){
		echo '<div id="message" class="updated"><p>'. __('The settings were saved successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($lz_error)){
		lz_report_error($lz_error);echo '<br />';
	}
	
	?>	
	
	<div class="postbox">
		
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Getting Started', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2" style="line-height:150%">
					<i>Welcome to Loginizer Security. By default the <b>Brute Force Protection</b> is immediately enabled. You should start by going over the default settings and tweaking them as per your needs.</i>
					<?php 
					if(defined('LOGINIZER_PREMIUM')){
						echo '<br><i>In the Premium version of Loginizer you have many more features. We recommend you enable features like <b>reCAPTCHA, Two Factor Auth or Email based PasswordLess</b> login. These features will improve your websites security.</i>';
					}else{
						echo '<br><i><a href="'.LOGINIZER_PRICING_URL.'" target="_blank" style="text-decoration:none;color:red;">Upgrade to Pro</a> for more features like <b>reCAPTCHA, Two Factor Auth, Rename wp-admin and wp-login.php pages, Email based PasswordLess</b> login and more. These features will improve your website\'s security.</i>';
					}
					?>
				</td>
			</tr>
		</table>
		</form>
		
		</div>
	</div>
	
	<div class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('System Information', 'loginizer'); ?></span>
		</h2>
		</div>
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="wp-list-table fixed striped users" cellspacing="1" border="0" width="95%" cellpadding="10" align="center">
		<?php
			echo '
			<tr>				
				<th align="left" width="25%">'.__('Loginizer Version', 'loginizer').'</th>
				<td>'.LOGINIZER_VERSION.(defined('LOGINIZER_PREMIUM') ? ' (<font color="green">Security PRO Version</font>)' : '').'</td>
			</tr>';
			
			do_action('loginizer_system_information');
			
			echo '<tr>
				<th align="left">'.__('URL', 'loginizer').'</th>
				<td>'.get_site_url().'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Path', 'loginizer').'</th>
				<td>'.ABSPATH.'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Server\'s IP Address', 'loginizer').'</th>
				<td>'.@$_SERVER['SERVER_ADDR'].'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Your IP Address', 'loginizer').'</th>
				<td>'.lz_getip().'
					<div style="float:right">
						Method : 
						<select name="lz_ip_method" id="lz_ip_method" style="font-size:11px; width:150px" onchange="lz_ip_method_handle()">
							<option value="0" '.lz_POSTselect('lz_ip_method', 0, (@$loginizer['ip_method'] == 0)).'>REMOTE_ADDR</option>
							<option value="1" '.lz_POSTselect('lz_ip_method', 1, (@$loginizer['ip_method'] == 1)).'>HTTP_X_FORWARDED_FOR</option>
							<option value="2" '.lz_POSTselect('lz_ip_method', 2, (@$loginizer['ip_method'] == 2)).'>HTTP_CLIENT_IP</option>
							<option value="3" '.lz_POSTselect('lz_ip_method', 3, (@$loginizer['ip_method'] == 3)).'>CUSTOM</option>
						</select>
						<input name="lz_custom_ip_method" id="lz_custom_ip_method" type="text" value="'.lz_optpost('lz_custom_ip_method', @$loginizer['custom_ip_method']).'" style="font-size:11px; width:100px; display:none" />
						<input name="save_lz_ip_method" class="button button-primary" value="Save" type="submit" />
					</div>
				</td>
			</tr>
			<tr>				
				<th align="left">'.__('wp-config.php is writable', 'loginizer').'</th>
				<td>'.(is_writable(ABSPATH.'/wp-config.php') ? '<span style="color:red">Yes</span>' : '<span style="color:green">No</span>').'</td>
			</tr>';
			
			if(file_exists(ABSPATH.'/.htaccess')){
				echo '
			<tr>				
				<th align="left">'.__('.htaccess is writable', 'loginizer').'</th>
				<td>'.(is_writable(ABSPATH.'/.htaccess') ? '<span style="color:red">Yes</span>' : '<span style="color:green">No</span>').'</td>
			</tr>';
			
			}
			
		?>
		</table>
		</form>
		
		</div>
	</div>

<script type="text/javascript">

function lz_ip_method_handle(){
	var ele = jQuery('#lz_ip_method');
	if(ele.val() == 3){
		jQuery('#lz_custom_ip_method').show();
	}else{
		jQuery('#lz_custom_ip_method').hide();
	}
};

lz_ip_method_handle();

</script>
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('File Permissions', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="wp-list-table fixed striped users" border="0" width="95%" cellpadding="10" align="center">
			<?php
			
			echo '
			<tr>
				<th style="background:#EFEFEF;">'.__('Relative Path', 'loginizer').'</th>
				<th style="width:10%; background:#EFEFEF;">'.__('Suggested', 'loginizer').'</th>
				<th style="width:10%; background:#EFEFEF;">'.__('Actual', 'loginizer').'</th>
			</tr>';
			
			$wp_content = basename(dirname(dirname(dirname(__FILE__))));
			
			$files_to_check = array('/' => array('0755', '0750'),
								'/wp-admin' => array('0755'),
								'/wp-includes' => array('0755'),
								'/wp-config.php' => array('0444'),
								'/'.$wp_content => array('0755'),
								'/'.$wp_content.'/themes' => array('0755'),
								'/'.$wp_content.'/plugins' => array('0755'),
								'.htaccess' => array('0444'));
			
			$root = ABSPATH;
			
			foreach($files_to_check as $k => $v){
				
				$path = $root.'/'.$k;
				$stat = @stat($path);
				$suggested = $v;
				$actual = substr(sprintf('%o', $stat['mode']), -4);
				
				echo '
			<tr>
				<td>'.$k.'</td>
				<td>'.current($suggested).'</td>
				<td><span '.(!in_array($actual, $suggested) ? 'style="color: red;"' : '').'>'.$actual.'</span></td>
			</tr>';
				
			}
			
			?>
		</table>
		</form>
		
		</div>
	</div>

<?php
	
	loginizer_page_footer();

}

// The Loginizer Admin Options Page
function loginizer_page_brute_force(){

	global $wpdb, $wp_roles, $loginizer;
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}

	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	// BEGIN THEME
	loginizer_page_header('Brute Force Settings');
	
	// Load the blacklist and whitelist
	$loginizer['blacklist'] = get_option('loginizer_blacklist');
	$loginizer['whitelist'] = get_option('loginizer_whitelist');
	
	// Disable Brute Force
	if(isset($_POST['disable_brute_lz'])){
		
		// Save the options
		update_option('loginizer_disable_brute', 1);
		
		$loginizer['disable_brute'] = 1;
		
		echo '<div id="message" class="updated"><p>'
			. __('The Brute Force Protection feature is now disabled', 'loginizer')
			. '</p></div><br />';
		
	}
	
	// Enable brute force
	if(isset($_POST['enable_brute_lz'])){
			
		// Save the options
		update_option('loginizer_disable_brute', 0);
		
		$loginizer['disable_brute'] = 0;
		
		echo '<div id="message" class="updated"><p>'
			. __('The Brute Force Protection feature is now enabled', 'loginizer')
			. '</p></div><br />';
		
	}
	
	// The Brute Force Settings
	if(isset($_POST['save_lz'])){
		
		$max_retries = (int) lz_optpost('max_retries');
		$lockout_time = (int) lz_optpost('lockout_time');
		$max_lockouts = (int) lz_optpost('max_lockouts');
		$lockouts_extend = (int) lz_optpost('lockouts_extend');
		$reset_retries = (int) lz_optpost('reset_retries');
		$notify_email = (int) lz_optpost('notify_email');
		$notify_email_address = lz_optpost('notify_email_address');
		
		if(!empty($notify_email_address) && !lz_valid_email($notify_email_address)){
			$error[] = __('Email address is invalid', 'loginizer');
		}
		
		$lockout_time = $lockout_time * 60;
		$lockouts_extend = $lockouts_extend * 60 * 60;
		$reset_retries = $reset_retries * 60 * 60;
		
		if(empty($error)){
			
			$option['max_retries'] = $max_retries;
			$option['lockout_time'] = $lockout_time;
			$option['max_lockouts'] = $max_lockouts;
			$option['lockouts_extend'] = $lockouts_extend;
			$option['reset_retries'] = $reset_retries;
			$option['notify_email'] = $notify_email;
			$option['notify_email_address'] = $notify_email_address;
			
			// Save the options
			update_option('loginizer_options', $option);
			
			$saved = true;
			
		}else{
			lz_report_error($error);
		}
	
		if(!empty($notice)){
			lz_report_notice($notice);	
		}
			
		if(!empty($saved)){
			echo '<div id="message" class="updated"><p>'
				. __('The settings were saved successfully', 'loginizer')
				. '</p></div><br />';
		}
	
	}
	
	// Delete a Blackist IP range
	if(isset($_POST['bdelid'])){
		
		$delid = (int) lz_optreq('bdelid');
		
		// Unset and save
		$blacklist = $loginizer['blacklist'];
		unset($blacklist[$delid]);
		update_option('loginizer_blacklist', $blacklist);
		
		echo '<div id="message" class="updated fade"><p>'
			. __('The Blacklist IP range has been deleted successfully', 'loginizer')
			. '</p></div><br />';
			
	}
	
	// Delete all Blackist IP ranges
	if(isset($_POST['del_all_blacklist'])){
		
		// Unset and save
		update_option('loginizer_blacklist', array());
		
		echo '<div id="message" class="updated fade"><p>'
			. __('The Blacklist IP range(s) have been cleared successfully', 'loginizer')
			. '</p></div><br />';
			
	}
	
	// Delete a Whitelist IP range
	if(isset($_POST['delid'])){
		
		$delid = (int) lz_optreq('delid');
		
		// Unset and save
		$whitelist = $loginizer['whitelist'];
		unset($whitelist[$delid]);
		update_option('loginizer_whitelist', $whitelist);
		
		echo '<div id="message" class="updated fade"><p>'
			. __('The Whitelist IP range has been deleted successfully', 'loginizer')
			. '</p></div><br />';
			
	}
	
	// Delete all Blackist IP ranges
	if(isset($_POST['del_all_whitelist'])){
		
		// Unset and save
		update_option('loginizer_whitelist', array());
		
		echo '<div id="message" class="updated fade"><p>'
			. __('The Whitelist IP range(s) have been cleared successfully', 'loginizer')
			. '</p></div><br />';
			
	}
	
	// Reset All Logs
	if(isset($_POST['lz_reset_all_ip'])){
	
		$result = $wpdb->query("DELETE FROM `".$wpdb->prefix."loginizer_logs` WHERE `time` > 0");
		
		echo '<div id="message" class="updated fade"><p>'
					. __('All the IP Logs have been cleared', 'loginizer')
					. '</p></div><br />';
	}
	
	// Reset Logs
	if(isset($_POST['lz_reset_ip']) && isset($_POST['lz_reset_ips']) && is_array($_POST['lz_reset_ips'])){

		$ips = $_POST['lz_reset_ips'];
		
		foreach($ips as $ip){
			if(!lz_valid_ip($ip)){
				$error[] = 'The IP - '.esc_html($ip).' is invalid !';
			}
		}
		
		if(count($ips) < 1){
			$error[] = __('There are no IPs submitted', 'loginizer');
		}
		
		// Should we start deleting logs
		if(empty($error)){
			
			foreach($ips as $ip){			
				$result = $wpdb->query($wpdb->prepare("DELETE FROM `".$wpdb->prefix."loginizer_logs` WHERE `ip` = %s", $ip));			
			}
			
			if(empty($error)){
				
				echo '<div id="message" class="updated fade"><p>'
						. __('The selected IP Logs have been reset', 'loginizer')
						. '</p></div><br />';
				
			}
			
		}
		
		if(!empty($error)){
			lz_report_error($error);echo '<br />';
		}
		
	}
	
	if(isset($_POST['blacklist_iprange'])){

		$start_ip = lz_optpost('start_ip');
		$end_ip = lz_optpost('end_ip');
		
		// If no end IP we consider only 1 IP
		if(empty($end_ip)){
			$end_ip = $start_ip;
		}
		
		// Validate the IP against all checks
		loginizer_iprange_validate($start_ip, $end_ip, $loginizer['blacklist'], $error);
		
		if(empty($error)){
		
			$blacklist = $loginizer['blacklist'];
			
			$newid = ( empty($blacklist) ? 0 : max(array_keys($blacklist)) ) + 1;
			
			$blacklist[$newid] = array();
			$blacklist[$newid]['start'] = $start_ip;
			$blacklist[$newid]['end'] = $end_ip;
			$blacklist[$newid]['time'] = time();
			
			update_option('loginizer_blacklist', $blacklist);
			
			echo '<div id="message" class="updated fade"><p>'
					. __('Blacklist IP range added successfully', 'loginizer')
					. '</p></div><br />';
			
		}
		
		if(!empty($error)){
			lz_report_error($error);echo '<br />';
		}
		
	}
	
	if(isset($_POST['whitelist_iprange'])){

		$start_ip = lz_optpost('start_ip_w');
		$end_ip = lz_optpost('end_ip_w');
		
		// If no end IP we consider only 1 IP
		if(empty($end_ip)){
			$end_ip = $start_ip;
		}
		
		// Validate the IP against all checks
		loginizer_iprange_validate($start_ip, $end_ip, $loginizer['whitelist'], $error);
		
		if(empty($error)){
			
			$whitelist = $loginizer['whitelist'];
			
			$newid = ( empty($whitelist) ? 0 : max(array_keys($whitelist)) ) + 1;
			
			$whitelist[$newid] = array();
			$whitelist[$newid]['start'] = $start_ip;
			$whitelist[$newid]['end'] = $end_ip;
			$whitelist[$newid]['time'] = time();
			
			update_option('loginizer_whitelist', $whitelist);
			
			echo '<div id="message" class="updated fade"><p>'
					. __('Whitelist IP range added successfully', 'loginizer')
					. '</p></div><br />';
			
		}
		
		if(!empty($error)){
			lz_report_error($error);echo '<br />';
		}
	}
	
	if(isset($_POST['lz_import_csv'])){

		if(!empty($_FILES['lz_import_file_csv']['name'])){

			$lz_csv_type = lz_optpost('lz_csv_type');
			
			// Is the submitted type in the allowed list ? 
			if(!in_array($lz_csv_type, array('blacklist', 'whitelist'))){
				$error[] = __('Invalid import type', 'loginizer');
			}
			
			if(empty($error)){
				
				//Get the extension of the file
				$csv_file_name = basename($_FILES['lz_import_file_csv']['name']);
				$csv_ext_name = strtolower(pathinfo($csv_file_name, PATHINFO_EXTENSION));

				//Check if it's a csv file
				if($csv_ext_name == 'csv'){
					
					$file = fopen($_FILES['lz_import_file_csv']['tmp_name'], "r");

					$line_count = 0;
					$update_record = 0;
					
					while($content = fgetcsv($file)){

						//Increment the $line_count
						$line_count++;
						
						//Skip the first line
						if($line_count <= 1){
							continue;
						}
						
						if(loginizer_iprange_validate($content[0], $content[1], $loginizer[$lz_csv_type], $error, $line_count)){
							
							$newid = ( empty($loginizer[$lz_csv_type]) ? 0 : max(array_keys($loginizer[$lz_csv_type])) ) + 1;
							
							$loginizer[$lz_csv_type][$newid] = array();
							$loginizer[$lz_csv_type][$newid]['start'] = $content[0];
							$loginizer[$lz_csv_type][$newid]['end'] = $content[1];
							$loginizer[$lz_csv_type][$newid]['time'] = time();
							
							$update_record = 1;
							
						}
					}
					
					fclose($file);
					
					if(!empty($update_record)){
						
						update_option('loginizer_'.$lz_csv_type, $loginizer[$lz_csv_type]);
						
						echo '<div id="message" class="updated fade"><p>'
								. __('Imported '.ucfirst($lz_csv_type).' IP range(s) successfully', 'loginizer')
								. '</p></div><br />';
						
					}
					
					if(!empty($error)){
						lz_report_error($error);echo '<br />';
					}
				}
				
			}
		}
	}
 
	//Brute Force Bulk Blacklist/ Whitelist Ip
	if(isset($_POST['lz_blacklist_selected_ip'])){
		if(isset($_POST['lz_reset_ips']) && is_array($_POST['lz_reset_ips'])){

			$ips = $_POST['lz_reset_ips'];
			
			foreach($ips as $ip){
				if(!lz_valid_ip($ip)){
					$error[] = 'The IP - '.esc_html($ip).' is invalid !';
				}
			}
			
			if(count($ips) < 1){
				$error[] = __('There are no IPs submitted', 'loginizer');
			}
			
			// Should we start deleting logs
			if(empty($error)){
				
				$update_record = 0;
				
				foreach($ips as $ip){
					
					if(loginizer_iprange_validate($ip, '', $loginizer['blacklist'], $error)){
							
						$newid = ( empty($loginizer['blacklist']) ? 0 : max(array_keys($loginizer['blacklist'])) ) + 1;
						
						$loginizer['blacklist'][$newid] = array();
						$loginizer['blacklist'][$newid]['start'] = $ip;
						$loginizer['blacklist'][$newid]['end'] = $ip;
						$loginizer['blacklist'][$newid]['time'] = time();
						
						$update_record = 1;
					}
				}
				
				if(!empty($update_record)){
						
					update_option('loginizer_blacklist', $loginizer['blacklist']);
					
					echo '<div id="message" class="updated fade"><p>'
							. __('The selected IP(s) have been blacklisted', 'loginizer')
							. '</p></div><br />';
					
				}
				
			}
		}else{
			$error[] = __('No IP(s) selected', 'loginizer');
		}
			
		if(!empty($error)){
			lz_report_error($error);echo '<br />';
		}
	}
	
	// Save the messages
	if(isset($_POST['save_err_msgs_lz'])){
		
		$msgs['inv_userpass'] = lz_optpost('msg_inv_userpass');
		$msgs['ip_blacklisted'] = lz_optpost('msg_ip_blacklisted');
		$msgs['attempts_left'] = lz_optpost('msg_attempts_left');
		$msgs['lockout_err'] = lz_optpost('msg_lockout_err');
		$msgs['minutes_err'] = lz_optpost('msg_minutes_err');
		$msgs['hours_err'] = lz_optpost('msg_hours_err');
		
		// Update them
		update_option('loginizer_msg', $msgs);
				
		echo '<div id="message" class="updated fade"><p>'
				. __('Error messages were saved successfully', 'loginizer')
				. '</p></div><br />';
				
	}

	// Count the Results
	$tmp = lz_selectquery("SELECT COUNT(*) AS num FROM `".$wpdb->prefix."loginizer_logs`");
	//print_r($tmp);
	
	// Which Page is it
	$lz_env['res_len'] = 10;
	$lz_env['cur_page'] = lz_get_page('lzpage', $lz_env['res_len']);
	$lz_env['num_res'] = $tmp['num'];
	$lz_env['max_page'] = ceil($lz_env['num_res'] / $lz_env['res_len']);
	
	// Get the logs
	$result = lz_selectquery("SELECT * FROM `".$wpdb->prefix."loginizer_logs` 
							ORDER BY `time` DESC 
							LIMIT ".$lz_env['cur_page'].", ".$lz_env['res_len']."", 1);
	//print_r($result);
	
	$lz_env['cur_page'] = ($lz_env['cur_page'] / $lz_env['res_len']) + 1;
	$lz_env['cur_page'] = $lz_env['cur_page'] < 1 ? 1 : $lz_env['cur_page'];
	$lz_env['next_page'] = ($lz_env['cur_page'] + 1) > $lz_env['max_page'] ? $lz_env['max_page'] : ($lz_env['cur_page'] + 1);
	$lz_env['prev_page'] = ($lz_env['cur_page'] - 1) < 1 ? 1 : ($lz_env['cur_page'] - 1);
	
	// Reload the settings
	$loginizer['blacklist'] = get_option('loginizer_blacklist');
	$loginizer['whitelist'] = get_option('loginizer_whitelist');
	
	$saved_msgs = get_option('loginizer_msg');
	
	?>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<?php echo __('<span>Failed Login Attempts Logs</span> &nbsp; (Past '.($loginizer['reset_retries']/60/60).' hours)','loginizer'); ?>
		</h2>
		</div>
		
		<script>
		function yesdsd(){
			window.location = '<?php echo menu_page_url('loginizer_brute_force', false);?>&lzpage='+jQuery("#current-page-selector").val();
			return false;
		}
		
		function lz_export_ajax(lz_csv_type){
	
			var data = new Object();
			data["action"] = lz_csv_type != "failed_login" ? "loginizer_export" : "loginizer_failed_login_export";
			data["lz_csv_type"] = lz_csv_type;
			data["nonce"]	= "<?php echo wp_create_nonce('loginizer_admin_ajax'); ?>";
			
			var admin_url = "<?php admin_url(); ?>"+"admin-ajax.php";
			
			jQuery.post(admin_url, data, function(response){
				
				// Was the ajax call successful ?
				if(response.substring(0,2) == "-1"){
					
					var err_message = response.substring(2);
					
					if(err_message){
						alert(err_message);
					}else{
						alert("Failed to export data");
					}
					
					return false;
				}
				
				/*
				* Make CSV downloadable
				*/
				var downloadLink = document.createElement("a");
				var fileData = ['\ufeff'+response];

				var blobObject = new Blob(fileData,{
				 type: "text/csv;charset=utf-8;"
				});

				var url = URL.createObjectURL(blobObject);
				downloadLink.href = url;
				downloadLink.download = "loginizer-"+lz_csv_type+".csv";

				/*
				* Actually download CSV
				*/
				document.body.appendChild(downloadLink);
				downloadLink.click();
				document.body.removeChild(downloadLink);
				
			});
			
		}
		
		</script>
		
		<form method="get" onsubmit="return yesdsd();">
			<div class="tablenav">
				<p class="tablenav-pages" style="margin: 5px 10px" align="right">
					<span class="displaying-num"><?php echo $lz_env['num_res'];?> items</span>
					<span class="pagination-links">
						<a class="first-page" href="<?php echo menu_page_url('loginizer_brute_force', false).'&lzpage=1';?>"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></a>
						<a class="prev-page" href="<?php echo menu_page_url('loginizer_brute_force', false).'&lzpage='.$lz_env['prev_page'];?>"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>
						<span class="paging-input">
							<label for="current-page-selector" class="screen-reader-text">Current Page</label>
							<input class="current-page" id="current-page-selector" name="lzpage" value="<?php echo $lz_env['cur_page'];?>" size="3" aria-describedby="table-paging" type="text"><span class="tablenav-paging-text"> of <span class="total-pages"><?php echo $lz_env['max_page'];?></span></span>
						</span>						
						<a class="next-page" href="<?php echo menu_page_url('loginizer_brute_force', false).'&lzpage='.$lz_env['next_page'];?>"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>
						<a class="last-page" href="<?php echo menu_page_url('loginizer_brute_force', false).'&lzpage='.$lz_env['max_page'];?>"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></a>
					</span>
				</p>
			</div>
		</form>
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('loginizer-options'); ?>
		<div class="inside">
		<table class="wp-list-table widefat fixed users" border="0">
			<tr>
				<th scope="row" valign="top" style="background:#EFEFEF;" width="20"><input type="checkbox" id="lz_check_all_logs" onchange="lz_multiple_check()" style="margin-left:-1px;"/></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('IP','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Attempted Username','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Last Failed Attempt  (DD/MM/YYYY)','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Failed Attempts Count','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Lockouts Count','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;" width="150"><?php echo __('URL Attacked','loginizer'); ?></th>
			</tr>
			<?php
			
			if(empty($result)){
				echo '
				<tr>
					<td colspan="4">
						'.__('No Logs. You will see logs about failed login attempts here.', 'loginizer').'
					</td>
				</tr>';
			}else{
				foreach($result as $ik => $iv){
					$status_button = (!empty($iv['status']) ? 'disable' : 'enable');
					echo '
					<tr>
						<td>
							<input type="checkbox" value="'.esc_attr($iv['ip']).'" name="lz_reset_ips[]" class="lz_shift_select_logs lz_check_all_logs" />
						</td>
						<td>
							<a href="https://ipinfo.io/'.esc_html($iv['ip']).'" target="_blank">'.esc_html($iv['ip']).'&nbsp;<span class="dashicons dashicons-external"></span></a>
						</td>
						<td>
							'.esc_html($iv['username']).'
						</td>
						<td>
							'.date('d/M/Y H:i:s P', $iv['time']).'
						</td>
						<td>
							'.esc_html($iv['count']).'
						</td>
						<td>
							'.esc_html($iv['lockout']).'
						</td>
						<td>
							'.esc_html($iv['url']).'
						</td>
					</tr>';
				}
			}
			
			?>
		</table>
		
		<br>
		<input name="lz_reset_ip" class="button button-primary action" value="<?php echo __('Remove From Logs', 'loginizer'); ?>" type="submit" />
		&nbsp; &nbsp; 
		<input name="lz_reset_all_ip" class="button button-primary action" value="<?php echo __('Clear All Logs', 'loginizer'); ?>" type="submit" />
		&nbsp; &nbsp; 
		<input name="lz_blacklist_selected_ip" class="button button-primary action" value="<?php echo __('Blacklist Selected IPs', 'loginizer'); ?>" type="submit" />
		&nbsp; &nbsp; 
		<input name="lz_export_csv" onclick="lz_export_ajax('failed_login'); return false;" class="button button-primary action" value="<?php echo __('Export CSV', 'loginizer'); ?>" type="submit" />
		</div>
	</div>
	</form>
	<br />
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Brute Force Settings', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="max_retries"><?php echo __('Max Retries','loginizer'); ?></label></th>
				<td>
					<input type="text" size="3" value="<?php echo lz_optpost('max_retries', $loginizer['max_retries']); ?>" name="max_retries" id="max_retries" /> <?php echo __('Maximum failed attempts allowed before lockout','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="lockout_time"><?php echo __('Lockout Time','loginizer'); ?></label></th>
				<td>
				<input type="text" size="3" value="<?php echo (!empty($lockout_time) ? $lockout_time : $loginizer['lockout_time']) / 60; ?>" name="lockout_time" id="lockout_time" /> <?php echo __('minutes','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="max_lockouts"><?php echo __('Max Lockouts','loginizer'); ?></label></th>
				<td>
					<input type="text" size="3" value="<?php echo lz_optpost('max_lockouts', $loginizer['max_lockouts']); ?>" name="max_lockouts" id="max_lockouts" /> <?php echo __('','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="lockouts_extend"><?php echo __('Extend Lockout','loginizer'); ?></label></th>
				<td>
					<input type="text" size="3" value="<?php echo (!empty($lockouts_extend) ? $lockouts_extend : $loginizer['lockouts_extend']) / 60 / 60; ?>" name="lockouts_extend" id="lockouts_extend" /> <?php echo __('hours. Extend Lockout time after Max Lockouts','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="reset_retries"><?php echo __('Reset Retries','loginizer'); ?></label></th>
				<td>
					<input type="text" size="3" value="<?php echo (!empty($reset_retries) ? $reset_retries : $loginizer['reset_retries']) / 60 / 60; ?>" name="reset_retries" id="reset_retries" /> <?php echo __('hours','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="notify_email"><?php echo __('Email Notification','loginizer'); ?></label></th>
				<td>
					<?php echo __('after ','loginizer'); ?>
					<input type="text" size="3" value="<?php echo (!empty($notify_email) ? $notify_email : $loginizer['notify_email']); ?>" name="notify_email" id="notify_email" /> <?php echo __('lockouts <br />0 to disable email notifications','loginizer'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="notify_email_address"><?php echo __('Email Address','loginizer'); ?></label></th>
				<td>
					<input type="text" value="<?php echo (!empty($notify_email_address) ? $notify_email_address : (!empty($loginizer['custom_notify_email']) ? $loginizer['notify_email_address'] : '')); ?>" name="notify_email_address" id="notify_email_address" size="30" /> <?php echo __('<br />failed login attempts notifications will be sent to this email','loginizer'); ?>
				</td>
			</tr>
		</table><br />
		<input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings','loginizer'); ?>" type="submit" />
		<?php
		
		if(empty($loginizer['disable_brute'])){		
			
			echo '<input name="disable_brute_lz" class="button action" value="'.__('Disable Brute Force Protection','loginizer').'" type="submit" style="float:right" />';
			
		}else{
			
			echo '<input name="enable_brute_lz" class="button button-primary action" value="'.__('Enable Brute Force Protection','loginizer').'" type="submit" style="float:right" />';
			
		}
		
		?>
		</form>
	
		</div>
	</div>
	<br />
	
<?php
	
	wp_enqueue_script('jquery-paginate', LOGINIZER_URL.'/jquery-paginate.js', array('jquery'), '1.10.15');
	
?>

<style>
.page-navigation a {
margin: 5px 2px;
display: inline-block;
padding: 5px 8px;
color: #0073aa;
background: #e5e5e5 none repeat scroll 0 0;
border: 1px solid #ccc;
text-decoration: none;
transition-duration: 0.05s;
transition-property: border, background, color;
transition-timing-function: ease-in-out;
}
 
.page-navigation a[data-selected] {
background-color: #00a0d2;
color: #fff;
}
</style>

<script>

jQuery(document).ready(function(){
	jQuery('#lz_bl_table').paginate({ limit: 11, navigationWrapper: jQuery('#lz_bl_nav')});
	jQuery('#lz_wl_table').paginate({ limit: 11, navigationWrapper: jQuery('#lz_wl_nav')});
	lz_multiple_check();
	lz_shift_check_all('lz_shift_select_logs');
});

// Delete a Blacklist / Whitelist IP Range
function del_confirm(field, todo_id, msg){
	var ret = confirm(msg);
	
	if(ret){
		jQuery('#lz_bl_wl_todo').attr('name', field);
		jQuery('#lz_bl_wl_todo').val(todo_id);
		jQuery('#lz_bl_wl_form').submit();
	}
	
	return false;
	
}

// Delete all Blacklist / Whitelist IP Ranges
function del_confirm_all(msg){
	var ret = confirm(msg);
	
	if(ret){
		return true;
	}
	
	return false;
	
}

//Check all the failed log attempts
function lz_multiple_check(){
	jQuery("#lz_check_all_logs").on("click", function(event){
		if(this.checked == true){
			jQuery(".lz_check_all_logs").prop("checked", true);
		}else{
			jQuery(".lz_check_all_logs").prop("checked", false);
		}
	});
}

//To select the installations/backups using shift key
function lz_shift_check_all(check_class){ 

    var checkboxes = jQuery("."+check_class);
    var lastChecked = null;

    checkboxes.click(function(event){
        if(!lastChecked){
            lastChecked = this;
            return;
        }

        if(event.shiftKey){
            var start = checkboxes.index(this);
            var end = checkboxes.index(lastChecked);
			
            checkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop("checked", this.checked);
        }

        lastChecked = this;
    });
};

</script>
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Blacklist IP','loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php echo __('Enter the IP you want to blacklist from login','loginizer'); ?>
	
		<form action="" method="post">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="start_ip"><?php echo __('Start IP','loginizer'); ?></label></th>
				<td>
					<input type="text" size="25" value="<?php echo(lz_optpost('start_ip')); ?>" name="start_ip" id="start_ip"/> <?php echo __('Start IP of the range','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="end_ip"><?php echo __('End IP (Optional)','loginizer'); ?></label></th>
				<td>
					<input type="text" size="25" value="<?php echo(lz_optpost('end_ip')); ?>" name="end_ip" id="end_ip"/> <?php echo __('End IP of the range. <br />If you want to blacklist single IP leave this field blank.','loginizer'); ?> <br />
				</td>
			</tr>
		</table><br />
		<input name="blacklist_iprange" class="button button-primary action" value="<?php echo __('Add Blacklist IP Range','loginizer'); ?>" type="submit" />
		<input style="float:right" name="del_all_blacklist" onclick="return del_confirm_all('<?php echo __('Are you sure you want to delete all Blacklist IP Range(s) ?','loginizer'); ?>')" class="button action" value="<?php echo __('Delete All Blacklist IP Range(s)','loginizer'); ?>" type="submit" />
		</form>
		</div>
		
		<div id="lz_bl_nav" style="margin: 5px 10px; text-align:right"></div>
		
		<!--Brute Force Blacklist Import CSV Form-->
		<div class="inside" id="blacklist_csv" style="display:none;">
			<form action="" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field('loginizer-options'); ?>
				<input type="hidden" value="blacklist" name="lz_csv_type" />
				<h3><?php echo __('Import Blacklist IPs (CSV)', 'loginizer'); ?>:</h3>
				<input type="file" name="lz_import_file_csv" value="Import CSV" />
				<br><br>
				<input name="lz_import_csv" class="button button-primary action" value="<?php echo __('Submit', 'loginizer'); ?>" type="submit" />
			</form>
		</div>
		<!---->
		
		<!--Brute Force Blacklist Export CSV Form-->
		<div class="inside" style="float:right;">
			<form action="" method="post">
				<?php wp_nonce_field('loginizer-options'); ?>
				<input type="hidden" value="blacklist" name="lz_csv_type" />
				<input class="button button-primary action" value="<?php echo __('Import CSV', 'loginizer'); ?>" type="button" onclick="jQuery('#blacklist_csv').toggle();"/>
				<input name="lz_export_csv" onclick="lz_export_ajax('blacklist'); return false;" class="button button-primary action" value="<?php echo __('Export CSV', 'loginizer'); ?>" type="submit" />
			</form>
		
		</div>
		<!---->
		
		<table id="lz_bl_table" class="wp-list-table fixed striped users" border="0" width="95%" cellpadding="10" align="center">
			<tr>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Start IP','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('End IP','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Date (DD/MM/YYYY)','loginizer'); ?></th>
				<th scope="row" valign="top" style="background:#EFEFEF;" width="100"><?php echo __('Options','loginizer'); ?></th>
			</tr>
			<?php
				if(empty($loginizer['blacklist'])){
					echo '
					<tr>
						<td colspan="4">
							'.__('No Blacklist IPs. You will see blacklisted IP ranges here.', 'loginizer').'
						</td>
					</tr>';
				}else{
					foreach($loginizer['blacklist'] as $ik => $iv){
						echo '
						<tr>
							<td>
								'.$iv['start'].'
							</td>
							<td>
								'.$iv['end'].'
							</td>
							<td>
								'.date('d/m/Y', $iv['time']).'
							</td>
							<td>
								<a class="submitdelete" href="javascript:void(0)" onclick="return del_confirm(\'bdelid\', '.$ik.', \'Are you sure you want to delete this IP range ?\')">Delete</a>
							</td>
						</tr>';
					}
				}
			?>
		</table>
		<br />
		<form action="" method="post" id="lz_bl_wl_form">
		<?php wp_nonce_field('loginizer-options'); ?>
		<input type="hidden" value="" name="" id="lz_bl_wl_todo"/> 
		</form>
	</div>
	
	<br />
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Whitelist IP', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php echo __('Enter the IP you want to whitelist for login','loginizer'); ?>
		<form action="" method="post">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="start_ip_w"><?php echo __('Start IP','loginizer'); ?></label></th>
				<td>
					<input type="text" size="25" value="<?php echo(lz_optpost('start_ip_w')); ?>" name="start_ip_w" id="start_ip_w"/> <?php echo __('Start IP of the range','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="end_ip_w"><?php echo __('End IP (Optional)','loginizer'); ?></label></th>
				<td>
					<input type="text" size="25" value="<?php echo(lz_optpost('end_ip_w')); ?>" name="end_ip_w" id="end_ip_w"/> <?php echo __('End IP of the range. <br />If you want to whitelist single IP leave this field blank.','loginizer'); ?> <br />
				</td>
			</tr>
		</table><br />
		<input name="whitelist_iprange" class="button button-primary action" value="<?php echo __('Add Whitelist IP Range','loginizer'); ?>" type="submit" />		
		<input style="float:right" name="del_all_whitelist" onclick="return del_confirm_all('<?php echo __('Are you sure you want to delete all Whitelist IP Range(s) ?','loginizer'); ?>')" class="button action" value="<?php echo __('Delete All Whitelist IP Range(s)','loginizer'); ?>" type="submit" />
		</form>
		</div>
		
		<div id="lz_wl_nav" style="margin: 5px 10px; text-align:right"></div>
		
		<!--Brute Force Whitelist Import CSV Form-->
		<div class="inside" id="lz_whitelist_csv_div" style="display:none;">
			<form action="" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field('loginizer-options'); ?>
				<input type="hidden" value="whitelist" name="lz_csv_type" />
				<h3><?php echo __('Import Whitelist IPs (CSV)', 'loginizer'); ?>:</h3>
				<input type="file" name="lz_import_file_csv" value="Import CSV" />
				<br><br>
				<input name="lz_import_csv" class="button button-primary action" value="<?php echo __('Submit', 'loginizer'); ?>" type="submit" />
			</form>
		</div>
		<!---->
		
		<!--Brute Force Whitelist Export CSV Form-->
		<div class="inside" style="float:right;">
			<form action="" method="post">
				<?php wp_nonce_field('loginizer-options'); ?>
				<input type="hidden" value="whitelist" name="lz_csv_type" />
				<input class="button button-primary action" value="<?php echo __('Import CSV', 'loginizer'); ?>" type="button" onclick="jQuery('#lz_whitelist_csv_div').toggle();"/>
				<input name="lz_export_csv" onclick="lz_export_ajax('whitelist'); return false;" class="button button-primary action" value="<?php echo __('Export CSV', 'loginizer'); ?>" type="submit" />
			</form>
		</div>
		<!---->
		
		<table id="lz_wl_table" class="wp-list-table fixed striped users" border="0" width="95%" cellpadding="10" align="center">
		<tr>
			<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Start IP','loginizer'); ?></th>
			<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('End IP','loginizer'); ?></th>
			<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Date (DD/MM/YYYY)','loginizer'); ?></th>
			<th scope="row" valign="top" style="background:#EFEFEF;" width="100"><?php echo __('Options','loginizer'); ?></th>
		</tr>
		<?php
			if(empty($loginizer['whitelist'])){
				echo '
				<tr>
					<td colspan="4">
						'.__('No Whitelist IPs. You will see whitelisted IP ranges here.', 'loginizer').'
					</td>
				</tr>';
			}else{
				foreach($loginizer['whitelist'] as $ik => $iv){
					echo '
					<tr>
						<td>
							'.$iv['start'].'
						</td>
						<td>
							'.$iv['end'].'
						</td>
						<td>
							'.date('d/m/Y', $iv['time']).'
						</td>
						<td>
							<a class="submitdelete" href="javascript:void(0)" onclick="return del_confirm(\'delid\', '.$ik.', \'Are you sure you want to delete this IP range ?\')">Delete</a>
						</td>
					</tr>';
				}
			}
		?>
		</table>
		<br />
	
	</div>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Error Messages', 'loginizer'); ?></span>
		</h2>
		</div>

		<div class="inside">

			<form action="" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field('loginizer-options'); ?>
				<table class="form-table">
					<tr>
						<th scope="row" valign="top"><label for="msg_inv_userpass"><?php echo __('Failed Login Attempt','loginizer'); ?></label></th>
						<td>
							<input type="text" size="25" value="<?php echo esc_attr(@$saved_msgs['inv_userpass']); ?>" name="msg_inv_userpass" id="msg_inv_userpass" />
							<?php echo __('Default: <em>&quot;' . $loginizer['d_msg']['inv_userpass']. '&quot;</em>', 'loginizer'); ?><br />
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="msg_ip_blacklisted"><?php echo __('Blacklisted IP','loginizer'); ?></label></th>
						<td>
							<input type="text" size="25" value="<?php echo esc_attr(@$saved_msgs['ip_blacklisted']); ?>" name="msg_ip_blacklisted" id="msg_ip_blacklisted" />
							<?php echo __('Default: <em>&quot;' . $loginizer['d_msg']['ip_blacklisted']. '&quot;</em>', 'loginizer'); ?><br />
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="msg_attempts_left"><?php echo __('Attempts Left','loginizer'); ?></label></th>
						<td>
							<input type="text" size="25" value="<?php echo esc_attr(@$saved_msgs['attempts_left']); ?>" name="msg_attempts_left" id="msg_attempts_left" />
							<?php echo __('Default: <em>&quot;' . $loginizer['d_msg']['attempts_left']. '&quot;</em>', 'loginizer'); ?><br />
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="msg_lockout_err"><?php echo __('Lockout Error','loginizer'); ?></label></th>
						<td>
							<input type="text" size="25" value="<?php echo esc_attr(@$saved_msgs['lockout_err']); ?>" name="msg_lockout_err" id="msg_lockout_err" />
							<?php echo __('Default: <em>&quot;' . strip_tags($loginizer['d_msg']['lockout_err']). '&quot;</em>', 'loginizer'); ?><br />
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="msg_minutes_err"><?php echo __('Minutes','loginizer'); ?></label></th>
						<td>
							<input type="text" size="25" value="<?php echo esc_attr(@$saved_msgs['minutes_err']); ?>" name="msg_minutes_err" id="msg_minutes_err" />
							<?php echo __('Default: <em>&quot;' . strip_tags($loginizer['d_msg']['minutes_err']). '&quot;</em>', 'loginizer'); ?><br />
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top"><label for="msg_hours_err"><?php echo __('Hours','loginizer'); ?></label></th>
						<td>
							<input type="text" size="25" value="<?php echo esc_attr(@$saved_msgs['hours_err']); ?>" name="msg_hours_err" id="msg_hours_err" />
							<?php echo __('Default: <em>&quot;' . strip_tags($loginizer['d_msg']['hours_err']). '&quot;</em>', 'loginizer'); ?><br />
						</td>
					</tr>
				</table><br />
				<input name="save_err_msgs_lz" class="button button-primary action" value="<?php echo __('Save Error Messages','loginizer'); ?>" type="submit" />
			</form>
		</div>
	</div>
<?php

loginizer_page_footer();

}

add_action('wp_ajax_loginizer_export', 'loginizer_export');

// Export CSV
function loginizer_export(){

	// Some AJAX security
	check_ajax_referer('loginizer_admin_ajax', 'nonce');
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	$lz_csv_type = lz_optpost('lz_csv_type');
	
	switch($lz_csv_type){
		
		case 'blacklist':
		$csv_array = get_option('loginizer_blacklist');
		$filename = 'loginizer-blacklist';
		break;
		
		case 'whitelist':
		$csv_array = get_option('loginizer_whitelist');
		$filename = 'loginizer-whitelist';
		break;
	}
	
	if(empty($csv_array)){
		echo -1;
		echo __('No data to export', 'loginizer');
		wp_die();
	}
		
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$filename.'.csv');
	
	$allowed_fields = array('start' => 'Start IP', 'end' => 'End IP', 'time' => 'Time');

	$file = fopen("php://output","w");
	
	fputcsv($file, array_values($allowed_fields));

	foreach($csv_array as $ik => $iv){
		
		$iv['start'] = $iv['start'];
		$iv['end'] = $iv['end'];
		$iv['time'] = date('d/m/Y', $iv['time']);
		
		$row = array();
		foreach($allowed_fields as $ak => $av){
			$row[$ak] = $iv[$ak];
		}
		
		fputcsv($file, $row);
	}

	fclose($file);
	
	wp_die();
        
}

add_action('wp_ajax_loginizer_failed_login_export', 'loginizer_failed_login_export');

//Export Failed Login Attempts
function loginizer_failed_login_export(){
	
	global $wpdb;
	// Some AJAX security
	check_ajax_referer('loginizer_admin_ajax', 'nonce');
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	$csv_array = lz_selectquery("SELECT * FROM `".$wpdb->prefix."loginizer_logs` ORDER BY `time` DESC", 1);
	$filename = 'loginizer-failed-login-attempts';
	
	if(empty($csv_array)){
		echo -1;
		echo __('No data to export', 'loginizer');
		wp_die();
	}
		
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$filename.'.csv');
	
	$allowed_fields = array('ip' => 'IP', 'attempted_username' => 'Attempted Username', 'last_f_attemp' => 'Last Failed Attempt', 'f_attempts_count' => 'Failed Attempts Count', 'lockouts_count' => 'Lockouts Count', 'url_attacked' => 'URL Attacked');

	$file = fopen("php://output","w");
	
	fputcsv($file, array_values($allowed_fields));
	
	foreach($csv_array as $failed_attempts){
		
		$row = array($failed_attempts['ip'], $failed_attempts['username'], date('d/M/Y H:i:s P', $failed_attempts['time']), $failed_attempts['count'], $failed_attempts['lockout'], $failed_attempts['url']);
		fputcsv($file, $row);
	}


	fclose($file);
	
	wp_die();

}
	
// IP range validations
function loginizer_iprange_validate($start_ip, $end_ip, $cur_list, &$error = array(), $line_count = ''){
	
	$line_error = '';
	if(!empty($line_count)){
		$line_error = ' '.__('Line no.', 'loginizer').' '.$line_count;
	}
			
	if(empty($start_ip)){
		$cur_error[] = __('Please enter the Start IP', 'loginizer').$line_error;
	}

	// If no end IP we consider only 1 IP
	if(empty($end_ip)){
		$end_ip = $start_ip;
	}
	
	if(!lz_valid_ip($start_ip)){
		$cur_error[] = __('Please provide a valid start IP', 'loginizer').$line_error;
	}
	
	if(!lz_valid_ip($end_ip)){
		$cur_error[] = __('Please provide a valid end IP', 'loginizer').$line_error;
	}
	
	if(inet_ptoi($start_ip) > inet_ptoi($end_ip)){
		
		// BUT, if 0.0.0.1 - 255.255.255.255 is given, it will not work
		if(inet_ptoi($start_ip) >= 0 && inet_ptoi($end_ip) < 0){
			// This is right
		}else{
			$cur_error[] = __('The End IP cannot be smaller than the Start IP', 'loginizer').$line_error;
		}
		
	}
			
	if(!empty($cur_error)){
		
		foreach($cur_error as $rk => $rv){
			$error[] = $rv;
		}
		
		return false;
	}
	
	if(!empty($cur_list)){
		
		foreach($cur_list as $k => $v){
			
			// This is to check if there is any other range exists with the same Start or End IP
			if(( inet_ptoi($start_ip) <= inet_ptoi($v['start']) && inet_ptoi($v['start']) <= inet_ptoi($end_ip) )
				|| ( inet_ptoi($start_ip) <= inet_ptoi($v['end']) && inet_ptoi($v['end']) <= inet_ptoi($end_ip) )
			){
				$cur_error[] = __('The Start IP or End IP submitted conflicts with an existing IP range !', 'loginizer').$line_error;
				break;
			}
			
			// This is to check if there is any other range exists with the same Start IP
			if(inet_ptoi($v['start']) <= inet_ptoi($start_ip) && inet_ptoi($start_ip) <= inet_ptoi($v['end'])){
				$cur_error[] = __('The Start IP is present in an existing range !', 'loginizer').$line_error;
				break;
			}
			
			// This is to check if there is any other range exists with the same End IP
			if(inet_ptoi($v['start']) <= inet_ptoi($end_ip) && inet_ptoi($end_ip) <= inet_ptoi($v['end'])){
				$cur_error[] = __('The End IP is present in an existing range!', 'loginizer').$line_error;
				break;
			}
			
		}
		
	}
			
	if(!empty($cur_error)){
		
		foreach($cur_error as $rk => $rv){
			$error[] = $rv;
		}
		
		return false;
	}
	
	return true;
}

//---------------------
// Admin Menu Pro Pages
//---------------------

// Loginizer - reCaptcha Page
function loginizer_page_recaptcha(){
	
	global $loginizer, $lz_error, $lz_env;
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	if(!loginizer_is_premium() && count($_POST) > 0){
		$lz_error['not_in_free'] = __('This feature is not available in the Free version. <a href="'.LOGINIZER_PRICING_URL.'" target="_blank" style="text-decoration:none; color:green;"><b>Upgrade to Pro</b></a>', 'loginizer');
		return loginizer_page_recaptcha_T();
	}

	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	// Themes
	$lz_env['theme']['light'] = 'Light';
	$lz_env['theme']['dark'] = 'Dark';
	
	// Langs
	$lz_env['lang'][''] = 'Auto Detect';
	$lz_env['lang']['ar'] = 'Arabic';
	$lz_env['lang']['bg'] = 'Bulgarian';
	$lz_env['lang']['ca'] = 'Catalan';
	$lz_env['lang']['zh-CN'] = 'Chinese (Simplified)';
	$lz_env['lang']['zh-TW'] = 'Chinese (Traditional)';
	$lz_env['lang']['hr'] = 'Croatian';
	$lz_env['lang']['cs'] = 'Czech';
	$lz_env['lang']['da'] = 'Danish';
	$lz_env['lang']['nl'] = 'Dutch';
	$lz_env['lang']['en-GB'] = 'English (UK)';
	$lz_env['lang']['en'] = 'English (US)';
	$lz_env['lang']['fil'] = 'Filipino';
	$lz_env['lang']['fi'] = 'Finnish';
	$lz_env['lang']['fr'] = 'French';
	$lz_env['lang']['fr-CA'] = 'French (Canadian)';
	$lz_env['lang']['de'] = 'German';
	$lz_env['lang']['de-AT'] = 'German (Austria)';
	$lz_env['lang']['de-CH'] = 'German (Switzerland)';
	$lz_env['lang']['el'] = 'Greek';
	$lz_env['lang']['iw'] = 'Hebrew';
	$lz_env['lang']['hi'] = 'Hindi';
	$lz_env['lang']['hu'] = 'Hungarain';
	$lz_env['lang']['id'] = 'Indonesian';
	$lz_env['lang']['it'] = 'Italian';
	$lz_env['lang']['ja'] = 'Japanese';
	$lz_env['lang']['ko'] = 'Korean';
	$lz_env['lang']['lv'] = 'Latvian';
	$lz_env['lang']['lt'] = 'Lithuanian';
	$lz_env['lang']['no'] = 'Norwegian';
	$lz_env['lang']['fa'] = 'Persian';
	$lz_env['lang']['pl'] = 'Polish';
	$lz_env['lang']['pt'] = 'Portuguese';
	$lz_env['lang']['pt-BR'] = 'Portuguese (Brazil)';
	$lz_env['lang']['pt-PT'] = 'Portuguese (Portugal)';
	$lz_env['lang']['ro'] = 'Romanian';
	$lz_env['lang']['ru'] = 'Russian';
	$lz_env['lang']['sr'] = 'Serbian';
	$lz_env['lang']['sk'] = 'Slovak';
	$lz_env['lang']['sl'] = 'Slovenian';
	$lz_env['lang']['es'] = 'Spanish';
	$lz_env['lang']['es-419'] = 'Spanish (Latin America)';
	$lz_env['lang']['sv'] = 'Swedish';
	$lz_env['lang']['th'] = 'Thai';
	$lz_env['lang']['tr'] = 'Turkish';
	$lz_env['lang']['uk'] = 'Ukrainian';
	$lz_env['lang']['vi'] = 'Vietnamese';
	
	// Sizes
	$lz_env['size']['normal'] = 'Normal';
	$lz_env['size']['compact'] = 'Compact';
	
	if(isset($_POST['save_lz'])){
		
		// Google Captcha
		$option['captcha_type'] = lz_optpost('captcha_type');
		$option['captcha_key'] = lz_optpost('captcha_key');
		$option['captcha_secret'] = lz_optpost('captcha_secret');
		$option['captcha_theme'] = lz_optpost('captcha_theme');
		$option['captcha_size'] = lz_optpost('captcha_size');
		$option['captcha_lang'] = lz_optpost('captcha_lang');
		
		// No Google Captcha
		$option['captcha_text'] = lz_optpost('captcha_text');
		$option['captcha_time'] = (int) lz_optpost('captcha_time');
		$option['captcha_words'] = (int) lz_optpost('captcha_words');
		$option['captcha_add'] = (int) lz_optpost('captcha_add');
		$option['captcha_subtract'] = (int) lz_optpost('captcha_subtract');
		$option['captcha_multiply'] = (int) lz_optpost('captcha_multiply');
		$option['captcha_divide'] = (int) lz_optpost('captcha_divide');
		
		// Checkboxes
		$option['captcha_user_hide'] = (int) lz_optpost('captcha_user_hide');
		$option['captcha_no_css_login'] = (int) lz_optpost('captcha_no_css_login');
		$option['captcha_login'] = (int) lz_optpost('captcha_login');
		$option['captcha_lostpass'] = (int) lz_optpost('captcha_lostpass');
		$option['captcha_resetpass'] = (int) lz_optpost('captcha_resetpass');
		$option['captcha_register'] = (int) lz_optpost('captcha_register');
		$option['captcha_comment'] = (int) lz_optpost('captcha_comment');
		$option['captcha_wc_checkout'] = (int) lz_optpost('captcha_wc_checkout');
		
		// Are we to use Math Captcha ?
		if(isset($_POST['captcha_no_google'])){
			
			$option['captcha_no_google'] = 1;
			
			// Make the checks
			if(strlen($option['captcha_text']) < 1){
				$lz_error['captcha_text'] = __('The Captcha key was not submitted', 'loginizer');
			}
			
		}else{
		
			// Make the checks
			if(strlen($option['captcha_key']) < 32 || strlen($option['captcha_key']) > 50){
				$lz_error['captcha_key'] = __('The reCAPTCHA key is invalid', 'loginizer');
			}
			
			// Is secret valid ?
			if(strlen($option['captcha_secret']) < 32 || strlen($option['captcha_secret']) > 50){
				$lz_error['captcha_secret'] = __('The reCAPTCHA secret is invalid', 'loginizer');
			}
			
			// Is theme valid ?
			if(empty($lz_env['theme'][$option['captcha_theme']])){
				$lz_error['captcha_theme'] = __('The reCAPTCHA theme is invalid', 'loginizer');
			}
			
			// Is size valid ?
			if(empty($lz_env['size'][$option['captcha_size']])){
				$lz_error['captcha_size'] = __('The reCAPTCHA size is invalid', 'loginizer');
			}
			
			// Is lang valid ?
			if(empty($lz_env['lang'][$option['captcha_lang']])){
				$lz_error['captcha_lang'] = __('The reCAPTCHA language is invalid', 'loginizer');
			}
			
		}
		
		// Is there an error ?
		if(!empty($lz_error)){
			return loginizer_page_recaptcha_T();
		}
		
		// Save the options
		update_option('loginizer_captcha', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Clear this
	if(isset($_POST['clear_captcha_lz'])){
		
		// Save the options
		update_option('loginizer_captcha', '');
		
		// Mark as saved
		$GLOBALS['lz_cleared'] = true;
		
	}
	
	// Call the theme
	loginizer_page_recaptcha_T();
	
}

// Loginizer - reCaptcha Page Theme
function loginizer_page_recaptcha_T(){
	
	global $loginizer, $lz_error, $lz_env;
	
	// Universal header
	loginizer_page_header('reCAPTCHA Settings');
	
	loginizer_feature_available('reCAPTCHA');
	
	// Saved ?
	if(!empty($GLOBALS['lz_saved'])){
		echo '<div id="message" class="updated"><p>'. __('The settings were saved successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Cleared ?
	if(!empty($GLOBALS['lz_cleared'])){
		echo '<div id="message" class="updated"><p>'. __('reCAPTCHA has been disabled !', 'loginizer'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($lz_error)){
		lz_report_error($lz_error);echo '<br />';
	}
	
	?>

<style>
input[type="text"], textarea, select {
    width: 70%;
}
</style>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('reCAPTCHA Settings', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr class="lz_google_cap">
				<td scope="row" valign="top" style="width:300px !important; padding-left:0px"><label><b><?php echo __('reCAPTCHA type', 'loginizer'); ?></b></label><br>
				<?php echo __('Choose the type of reCAPTCHA', 'loginizer'); ?><br />
				<?php echo __('<a href="https://g.co/recaptcha/sitetypes/" target="_blank">See Site Types for more details</a>', 'loginizer'); ?>
				</td>
				<td>
					<input type="radio" value="v3" onchange="google_recaptcha_type(this)" <?php echo lz_POSTradio('captcha_type', 'v3', $loginizer['captcha_type']); ?> name="captcha_type" id="captcha_type_v3" /> <label for="captcha_type_v3"><?php echo __('reCAPTCHA v3', 'loginizer'); ?></label><br /><br />
					<input type="radio" value="" onchange="google_recaptcha_type(this)" <?php echo lz_POSTradio('captcha_type', '', $loginizer['captcha_type']); ?> name="captcha_type" id="captcha_type_v2" /> <label for="captcha_type_v2"><?php echo __('reCAPTCHA v2 - Checkbox', 'loginizer'); ?></label><br /><br />
					<input type="radio" value="v2_invisible" onchange="google_recaptcha_type(this)" <?php echo lz_POSTradio('captcha_type', 'v2_invisible', $loginizer['captcha_type']); ?> name="captcha_type" id="captcha_type_v2_invisible" /> <label for="captcha_type_v2_invisible"><?php echo __('reCAPTCHA v2 - Invisible', 'loginizer'); ?></label><br />
				</td>
			</tr>
			<tr class="lz_google_cap">
				<td scope="row" valign="top" style="width:300px !important; padding-left:0px"><label><b><?php echo __('Site Key', 'loginizer'); ?></b></label><br>
				<?php echo __('Make sure you enter the correct keys as per the reCAPTCHA type selected above', 'loginizer'); ?>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('captcha_key', $loginizer['captcha_key']); ?>" name="captcha_key" /><br />
					<?php echo __('Get the Site Key and Secret Key from <a href="https://www.google.com/recaptcha/admin/" target="_blank">Google</a>', 'loginizer'); ?>
				</td>
			</tr>
			<tr class="lz_google_cap">
				<th scope="row" valign="top"><label><?php echo __('Secret Key', 'loginizer'); ?></label></th>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('captcha_secret', $loginizer['captcha_secret']); ?>" name="captcha_secret" />
				</td>
			</tr>
			<tr class="lz_google_cap">
				<th scope="row" valign="top"><label><?php echo __('Theme', 'loginizer'); ?></label></th>
				<td>
					<select name="captcha_theme">
						<?php
							foreach($lz_env['theme'] as $k => $v){
								echo '<option '.lz_POSTselect('captcha_theme', $k, ($loginizer['captcha_theme'] == $k ? true : false)).' value="'.$k.'">'.$v.'</value>';								
							}
						?>
					</select>
				</td>
			</tr>
			<tr class="lz_google_cap">
				<th scope="row" valign="top"><label><?php echo __('Language', 'loginizer'); ?></label></th>
				<td>
					<select name="captcha_lang">
						<?php
							foreach($lz_env['lang'] as $k => $v){
								echo '<option '.lz_POSTselect('captcha_lang', $k, ($loginizer['captcha_lang'] == $k ? true : false)).' value="'.$k.'">'.$v.'</value>';								
							}
						?>
					</select>
				</td>
			</tr>
			<tr class="lz_google_cap lz_google_cap_size">
				<th scope="row" valign="top"><label><?php echo __('Size', 'loginizer'); ?></label></th>
				<td>
					<select name="captcha_size">
						<?php
							foreach($lz_env['size'] as $k => $v){
								echo '<option '.lz_POSTselect('captcha_size', $k, ($loginizer['captcha_size'] == $k ? true : false)).' value="'.$k.'">'.$v.'</value>';								
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="padding-left:0px">
					<label><b><?php echo __('Don\'t use Google reCAPTCHA', 'loginizer'); ?></b></label><br>
					<?php echo __('If selected, '.$loginizer['prefix'].' will use a simple Math Captcha instead of Google reCAPTCHA', 'loginizer'); ?>
				</td>
				<td>
					<input type="checkbox" onclick="no_google_recaptcha(this)" id="captcha_no_google" value="1" name="captcha_no_google" <?php echo lz_POSTchecked('captcha_no_google', (empty($loginizer['captcha_no_google']) ? false : true)); ?> />
				</td>
			</tr>
			<tr class="lz_math_cap">
				<td scope="row" valign="top" style="width:300px !important; padding-left:0px">
					<label><b><?php echo __('Captcha Text', 'loginizer'); ?></b></label><br>
					<?php echo __('The text to be shown for the Captcha Field', 'loginizer'); ?>
				</td>
				<td>
					<input type="text" size="30" value="<?php echo lz_optpost('captcha_text', @$loginizer['captcha_text']); ?>" name="captcha_text" />
				</td>
			</tr>
			<tr class="lz_math_cap">
				<td scope="row" valign="top" style="padding-left:0px">
					<label><b><?php echo __('Captcha Time', 'loginizer'); ?></b></label><br>
					<?php echo __('Enter the number of seconds, a user has to enter captcha value.', 'loginizer'); ?>
				</td>
				<td>
					<input type="text" size="30" value="<?php echo lz_optpost('captcha_time', @$loginizer['captcha_time']); ?>" name="captcha_time" />
				</td>
			</tr>
			<tr class="lz_math_cap">
				<td scope="row" valign="top" style="padding-left:0px">
					<label><b><?php echo __('Display Captcha in Words', 'loginizer'); ?></b></label><br>
					<?php echo __('If selected the Captcha will be displayed in words rather than numbers', 'loginizer'); ?>
				</td>
				<td>
					<input type="checkbox" value="1" name="captcha_words" <?php echo lz_POSTchecked('captcha_words', (empty($loginizer['captcha_words']) ? false : true));?> />
				</td>
			</tr>
			<tr class="lz_math_cap">
				<td scope="row" valign="top" style="vertical-align: top !important; padding-left:0px">
					<label><b><?php echo __('Mathematical operations', 'loginizer'); ?></b></label><br>
					<?php echo __('The Mathematical operations to use for Captcha', 'loginizer'); ?>
				</td>
				<td valign="top">
					<table class="wp-list-table fixed users" cellpadding="8" cellspacing="1">
						<?php echo '
						<tr>
							<td>'.__('Addition (+)', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_add" '.lz_POSTchecked('captcha_add', (empty($loginizer['captcha_add']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Subtraction (-)', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_subtract" '.lz_POSTchecked('captcha_subtract', (empty($loginizer['captcha_subtract']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Multiplication (x)', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_multiply" '.lz_POSTchecked('captcha_multiply', (empty($loginizer['captcha_multiply']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Division (รท)', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_divide" '.lz_POSTchecked('captcha_divide', (empty($loginizer['captcha_divide']) ? false : true)).' /></td>
						</tr>';
						?>
					</table>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label><?php echo __('Show Captcha On', 'loginizer'); ?></label></th>
				<td valign="top">
					<table class="wp-list-table fixed users" cellpadding="8" cellspacing="1">
						<?php echo '
						<tr>
							<td>'.__('Login Form', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_login" '.lz_POSTchecked('captcha_login', (empty($loginizer['captcha_login']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Lost Password Form', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_lostpass" '.lz_POSTchecked('captcha_lostpass', (empty($loginizer['captcha_lostpass']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Reset Password Form', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_resetpass" '.lz_POSTchecked('captcha_resetpass', (empty($loginizer['captcha_resetpass']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Registration Form', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_register" '.lz_POSTchecked('captcha_register', (empty($loginizer['captcha_register']) ? false : true)).' /></td>
						</tr>
						<tr>
							<td>'.__('Comment Form', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_comment" '.lz_POSTchecked('captcha_comment', (empty($loginizer['captcha_comment']) ? false : true)).' /></td>
						</tr>';
						
						if(!defined('SITEPAD')){
						
						echo '<tr>
							<td>'.__('WooCommerce Checkout', 'loginizer').'</td>
							<td><input type="checkbox" value="1" name="captcha_wc_checkout" '.lz_POSTchecked('captcha_wc_checkout', (empty($loginizer['captcha_wc_checkout']) ? false : true)).' /></td>
						</tr>';
						
						}
						
						?>
					</table>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label><?php echo __('Hide CAPTCHA for logged in Users', 'loginizer'); ?></label></th>
				<td>
					<input type="checkbox" value="1" name="captcha_user_hide" <?php echo lz_POSTchecked('captcha_user_hide', (empty($loginizer['captcha_user_hide']) ? false : true)); ?> />
				</td>
			</tr>
			<tr class="lz_google_cap">
				<th scope="row" valign="top"><label><?php echo __('Disable CSS inserted on Login Page', 'loginizer'); ?></label></th>
				<td>
					<input type="checkbox" value="1" name="captcha_no_css_login" <?php echo lz_POSTchecked('captcha_no_css_login', (empty($loginizer['captcha_no_css_login']) ? false : true)); ?> />
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings','loginizer'); ?>" type="submit" />
		<input style="float:right" name="clear_captcha_lz" class="button action" value="<?php echo __('Disable reCAPTCHA','loginizer'); ?>" type="submit" /></center>
		</form>
	
		</div>
	</div>
	<br />

<script type="text/javascript">

function no_google_recaptcha(obj){
	
	if(obj.checked){
		jQuery(".lz_google_cap").hide();
		jQuery(".lz_math_cap").show();
	}else{
		jQuery(".lz_google_cap").show();
		jQuery(".lz_math_cap").hide();
	}
	
	var cur_captcha_type = jQuery("input:radio[name='captcha_type']:checked").val();
	
	if(cur_captcha_type == 'v3' || cur_captcha_type == 'v2_invisible'){
		jQuery(".lz_google_cap_size").hide();
	}else{
		jQuery(".lz_google_cap_size").show();
	}
	
}

no_google_recaptcha(jQuery("#captcha_no_google")[0]);

function google_recaptcha_type(obj){
	if(obj.value == 'v3' || obj.value == 'v2_invisible'){
		jQuery(".lz_google_cap_size").hide();
	}else{
		jQuery(".lz_google_cap_size").show();
	}
}


</script>
	
	<?php
	loginizer_page_footer();
	
}


// Loginizer - Two Factor Auth Page
function loginizer_page_2fa(){
	
	global $loginizer, $lz_error, $lz_env, $lz_roles, $lz_options, $saved_msgs;
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	if(!loginizer_is_premium() && count($_POST) > 0){
		$lz_error['not_in_free'] = __('This feature is not available in the Free version. <a href="'.LOGINIZER_PRICING_URL.'" target="_blank" style="text-decoration:none; color:green;"><b>Upgrade to Pro</b></a>', 'loginizer');
		return loginizer_page_2fa_T();
	}

	$lz_roles = get_editable_roles();
	
	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	// Settings submitted
	if(isset($_POST['save_lz'])){
		
		// In the future there can be more settings
		$option['2fa_app'] = (int) lz_optpost('2fa_app');
		$option['2fa_email'] = (int) lz_optpost('2fa_email');
		$option['question'] = (int) lz_optpost('question');
		$option['2fa_email_force'] = (int) lz_optpost('2fa_email_force');
		
		// Any roles to apply to ?
		foreach($lz_roles as $k => $v){
			
			if(lz_optpost('2fa_roles_'.$k)){
				$option['2fa_roles'][$k] = 1;
			}
			
		}
		
		// If its all, then blank it
		if(lz_optpost('2fa_roles_all') || empty($option['2fa_roles'])){
			$option['2fa_roles'] = '';
		}
		
		// Is there an error ?
		if(!empty($lz_error)){
			return loginizer_page_2fa_T();
		}
		
		// Save the options
		update_option('loginizer_2fa', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Reset a users 2FA
	if(isset($_POST['reset_user_lz'])){
		
		$_username = lz_optpost('lz_user_2fa_disable');
		
		// Try to get the user
		$user_search = get_user_by('login', $_username);
		
		// If not found then search by email
		if(empty($user_search)){
			$user_search = get_user_by('email', $_username);
		}
		
		// If not found then give error
		if(empty($user_search)){
			$lz_error['2fa_user_not'] = __('There is no such user with the email or username you submitted', 'loginizer');
			return loginizer_page_2fa_T();
		}
		
		// Get the user prefences
		$user_pref = get_user_meta($user_search->ID, 'loginizer_user_settings');
		
		// Blank it
		$user_pref['pref'] = 'none';
		
		// Save it
		update_user_meta($user_search->ID, 'loginizer_user_settings', $user_pref);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = __('The user\'s 2FA settings have been reset', 'loginizer');
		
	}
	
	if(isset($_POST['save_2fa_email_template_lz'])){
		
		// In the future there can be more settings
		$option['2fa_email_sub'] = lz_optpost('lz_2fa_email_sub');
		$option['2fa_email_msg'] = lz_optpost('lz_2fa_email_msg');
		
		// Is there an error ?
		if(!empty($lz_error)){
			return loginizer_page_2fa_T();
		}
		
		// Save the options
		update_option('loginizer_2fa_email_template', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Save the messages
	if(isset($_POST['save_msgs_lz'])){
		
		$msgs['otp_app'] = lz_optpost('msg_otp_app');
		$msgs['otp_email'] = lz_optpost('msg_otp_email');
		$msgs['otp_field'] = lz_optpost('msg_otp_field');
		$msgs['otp_question'] = lz_optpost('msg_otp_question');
		$msgs['otp_answer'] = lz_optpost('msg_otp_answer');
		
		// Update them
		update_option('loginizer_2fa_msg', $msgs);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = __('Messages were saved successfully', 'loginizer');
		
	}
	
	// Delete a Whitelist IP range
	if(isset($_POST['delid'])){
		
		$delid = (int) lz_optreq('delid');
		
		// Unset and save
		$whitelist = $loginizer['2fa_whitelist'];
		unset($whitelist[$delid]);
		update_option('loginizer_2fa_whitelist', $whitelist);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = __('The Whitelist IP range has been deleted successfully', 'loginizer');
		
	}
	
	// Delete all Blackist IP ranges
	if(isset($_POST['del_all_whitelist'])){
		
		// Unset and save
		update_option('loginizer_2fa_whitelist', array());
		
		// Mark as saved
		$GLOBALS['lz_saved'] = __('The Whitelist IP range(s) have been cleared successfully', 'loginizer');
		
	}
	
	// Add IP range to 2FA whitelist
	if(isset($_POST['2fa_whitelist_iprange'])){

		$start_ip = lz_optpost('start_ip_w_2fa');
		$end_ip = lz_optpost('end_ip_w_2fa');
		
		if(empty($start_ip)){
			$lz_error[] = __('Please enter the Start IP', 'loginizer');
			return loginizer_page_2fa_T();
		}
		
		// If no end IP we consider only 1 IP
		if(empty($end_ip)){
			$end_ip = $start_ip;
		}
				
		if(!lz_valid_ip($start_ip)){
			$lz_error[] = __('Please provide a valid start IP', 'loginizer');
		}
		
		if(!lz_valid_ip($end_ip)){
			$lz_error[] = __('Please provide a valid end IP', 'loginizer');
		}
			
		if(inet_ptoi($start_ip) > inet_ptoi($end_ip)){
			
			// BUT, if 0.0.0.1 - 255.255.255.255 is given, it will not work
			if(inet_ptoi($start_ip) >= 0 && inet_ptoi($end_ip) < 0){
				// This is right
			}else{
				$lz_error[] = __('The End IP cannot be smaller than the Start IP', 'loginizer');
			}
			
		}
		
		if(empty($lz_error)){
			
			$whitelist = $loginizer['2fa_whitelist'];
			
			foreach($whitelist as $k => $v){
				
				// This is to check if there is any other range exists with the same Start or End IP
				if(( inet_ptoi($start_ip) <= inet_ptoi($v['start']) && inet_ptoi($v['start']) <= inet_ptoi($end_ip) )
					|| ( inet_ptoi($start_ip) <= inet_ptoi($v['end']) && inet_ptoi($v['end']) <= inet_ptoi($end_ip) )
				){
					$lz_error[] = __('The Start IP or End IP submitted conflicts with an existing IP range !', 'loginizer');
					break;
				}
				
				// This is to check if there is any other range exists with the same Start IP
				if(inet_ptoi($v['start']) <= inet_ptoi($start_ip) && inet_ptoi($start_ip) <= inet_ptoi($v['end'])){
					$lz_error[] = __('The Start IP is present in an existing range !', 'loginizer');
					break;
				}
				
				// This is to check if there is any other range exists with the same End IP
				if(inet_ptoi($v['start']) <= inet_ptoi($end_ip) && inet_ptoi($end_ip) <= inet_ptoi($v['end'])){
					$lz_error[] = __('The End IP is present in an existing range!', 'loginizer');
					break;
				}
				
			}
			
			$newid = ( empty($whitelist) ? 0 : max(array_keys($whitelist)) ) + 1;
			
			if(empty($lz_error)){
				
				$whitelist[$newid] = array();
				$whitelist[$newid]['start'] = $start_ip;
				$whitelist[$newid]['end'] = $end_ip;
				$whitelist[$newid]['time'] = time();
				
				update_option('loginizer_2fa_whitelist', $whitelist);
		
				// Mark as saved
				$GLOBALS['lz_saved'] = __('Whitelist IP range for Two Factor Authentication added successfully', 'loginizer');
				
			}
			
		}
	}
	
	
	$lz_options = get_option('loginizer_2fa_email_template');
	$saved_msgs = get_option('loginizer_2fa_msg');
	$loginizer['2fa_whitelist'] = get_option('loginizer_2fa_whitelist');
	
	// Call theme
	loginizer_page_2fa_T();
	
}


// Loginizer - Two Factor Auth Page
function loginizer_page_2fa_T(){
	
	global $loginizer, $lz_error, $lz_env, $lz_roles, $lz_options, $saved_msgs;
	
	// Universal header
	loginizer_page_header('Two Factor Authentication');
	
	loginizer_feature_available('Two-Factor Authentication');
	
	// Saved ?
	if(!empty($GLOBALS['lz_saved'])){
		echo '<div id="message" class="updated"><p>'. __(is_string($GLOBALS['lz_saved']) ? $GLOBALS['lz_saved'] : 'The settings were saved successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($lz_error)){
		lz_report_error($lz_error);echo '<br />';
	}

	?>

<style>
input[type="text"], textarea, select {
    width: 70%;
}

.form-table label{
	font-weight:bold;
}

.exp{
	font-size:12px;
}
</style>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Two Factor Authentication Settings', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i><?php echo __('Please choose from the following Two Factor Authentication methods. Each user can choose any one method from the ones enabled by you. You can enable all or anyone that you would like.', 'loginizer'); ?></i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:70% !important">
					<label><?php echo __('OTP via App', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('After entering the correct login credentials, the user will be asked for the OTP. The OTP will be obtained from the users mobile app e.g. <b>Google Authenticator, Authy, etc.</b>', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" name="2fa_app" <?php echo lz_POSTchecked('2fa_app', (empty($loginizer['2fa_app']) ? false : true), 'save_lz'); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label><?php echo __('OTP via Email', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('After entering the correct login credentials, the user will be asked for the OTP. The OTP will be emailed to the user.', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" name="2fa_email" <?php echo lz_POSTchecked('2fa_email', (empty($loginizer['2fa_email']) ? false : true), 'save_lz'); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label><?php echo __('User Defined Question & Answer', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('In this method the user will be asked to set a secret personal question and answer. After entering the correct login credentials, the user will be asked to answer the question set by them, thus increasing the security', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" name="question" <?php echo lz_POSTchecked('question', (empty($loginizer['question']) ? false : true), 'save_lz'); ?> />
				</td>
			</tr>
		</table><br />
		
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" style="width:70% !important">
					<label><?php echo __('Force OTP via Email', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If the user does not have any 2FA method selected, this will enforce the OTP via Email for the users.', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" name="2fa_email_force" <?php echo lz_POSTchecked('2fa_email_force', (empty($loginizer['2fa_email_force']) ? false : true), 'save_lz'); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:70% !important">
					<label><?php echo __('Apply 2FA to Roles', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Select the Roles to which 2FA should be applied.', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" onchange="lz_roles_handle()" name="2fa_roles_all" id="2fa_roles_all" <?php echo lz_POSTchecked('2fa_roles_all', (empty($loginizer['2fa_roles']) ? true : false), 'save_lz'); ?> /> All<br />
					<?php
					
					foreach($lz_roles as $k => $v){
						echo '<span class="lz_roles"><input type="checkbox" value="1" name="2fa_roles_'.$k.'" '.lz_POSTchecked('2fa_roles_'.$k, (empty($loginizer['2fa_roles'][$k]) ? false : true), 'save_lz').' /> '.$v['name'].'<br /></span>';
					}
					
					?>
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /></center>
		</form>
	
		</div>
	</div>

<script type="text/javascript">

function lz_roles_handle(){
	
	var obj = jQuery("#2fa_roles_all")[0];
	
	if(obj.checked){
		jQuery(".lz_roles").hide();
	}else{
		jQuery(".lz_roles").show();
	}
	
}

lz_roles_handle();

</script>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('OTP via Email Template', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td colspan="2" valign="top">
					<?php echo __('Customize the email template to be used when sending the OTP to login via Email for 2FA.', 'loginizer'); ?><br>
					<?php echo __('If you do not make changes below the default email template will be used !', 'loginizer'); ?>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:350px !important">
					<label><?php echo __('Email Subject', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the default subject', 'loginizer'); ?></span>
					<br />Default : <?php echo @$loginizer['2fa_email_d_sub']; ?>
				</td>
				<td valign="top">
					<input type="text" size="40" value="<?php echo lz_optpost('lz_2fa_email_sub', @$lz_options['2fa_email_sub']); ?>" name="lz_2fa_email_sub" />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label><?php echo __('Email Body', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the default message', 'loginizer'); ?></span>
					<br />Default : <pre style="font-size:10px"><?php echo @$loginizer['2fa_email_d_msg']; ?></pre>
				</td>
				<td valign="top">
					<textarea rows="10" name="lz_2fa_email_msg"><?php echo lz_optpost('lz_2fa_email_msg', @$lz_options['2fa_email_msg']); ?></textarea>
					<br />
					Variables :
					<br />$otp - The OTP for login
					<br />$site_name - The Site Name
					<br />$site_url - The Site URL
					<br />$email  - Users Email
					<br />$display_name  - Users Display Name
					<br />$user_login  - Username
					<br />$first_name  - Users First Name
					<br />$last_name  - Users Last Name
				</td>
			</tr>
		</table><br />
		<center><input name="save_2fa_email_template_lz" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /></center>
		</form>
	
		</div>
	</div>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Custom Messages for OTP', 'loginizer'); ?></span>
		</h2>
		</div>

		<div class="inside">

			<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
				<?php wp_nonce_field('loginizer-options'); ?>
				<table class="form-table">
					<tr>
						<td colspan="2" valign="top">
							<?php echo __('Customize the title for OTP field displayed to the user on the login form.', 'loginizer'); ?><br>
							<?php echo __('If you do not make changes below the default messages will be used !', 'loginizer'); ?>
						</td>
					</tr>
					<tr>
						<td scope="row" valign="top" style="width:350px !important">
							<label for="msg_otp_app"><?php echo __('OTP via APP','loginizer'); ?></label><br />
							<?php echo __('Default: <em>&quot;' . $loginizer['2fa_d_msg']['otp_app']. '&quot;</em>', 'loginizer'); ?>
						</td>
						<td>
							<input type="text" size="50" value="<?php echo esc_attr(@$saved_msgs['otp_app']); ?>" name="msg_otp_app" id="msg_otp_app" style="width:auto !important;" />
							<br />
						</td>
					</tr>
					<tr>
						<td scope="row" valign="top" style="width:350px !important">
							<label for="msg_otp_email"><?php echo __('OTP via Email','loginizer'); ?></label><br />
							<?php echo __('Default: <em>&quot;' . $loginizer['2fa_d_msg']['otp_email']. '&quot;</em>', 'loginizer'); ?>
						</td>
						<td>
							<input type="text" size="50" value="<?php echo esc_attr(@$saved_msgs['otp_email']); ?>" name="msg_otp_email" id="msg_otp_email" style="width:auto !important;" />
							<br />
						</td>
					</tr>
					<tr>
						<td scope="row" valign="top" style="width:350px !important">
							<label for="msg_otp_field"><?php echo __('Title for OTP field','loginizer'); ?></label><br />
							<?php echo __('Default: <em>&quot;' . $loginizer['2fa_d_msg']['otp_field']. '&quot;</em>', 'loginizer'); ?>
						</td>
						<td>
							<input type="text" size="50" value="<?php echo esc_attr(@$saved_msgs['otp_field']); ?>" name="msg_otp_field" id="msg_otp_field" style="width:auto !important;" />
							<br />
						</td>
					</tr>
					<tr>
						<td scope="row" valign="top" style="width:350px !important">
							<label for="msg_otp_question"><?php echo __('Title for Security Question','loginizer'); ?></label><br />
							<?php echo __('Default: <em>&quot;' . $loginizer['2fa_d_msg']['otp_question']. '&quot;</em>', 'loginizer'); ?>
						</td>
						<td>
							<input type="text" size="50" value="<?php echo esc_attr(@$saved_msgs['otp_question']); ?>" name="msg_otp_question" id="msg_otp_question" style="width:auto !important;" />
							<br />
						</td>
					</tr>
					<tr>
						<td scope="row" valign="top" style="width:350px !important">
							<label for="msg_otp_answer"><?php echo __('Title for Security Answer','loginizer'); ?></label><br />
							<?php echo __('Default: <em>&quot;' . $loginizer['2fa_d_msg']['otp_answer']. '&quot;</em>', 'loginizer'); ?>
						</td>
						<td>
							<input type="text" size="50" value="<?php echo esc_attr(@$saved_msgs['otp_answer']); ?>" name="msg_otp_answer" id="msg_otp_answer" style="width:auto !important;" />
							<br />
						</td>
					</tr>
				</table><br />
				<center><input name="save_msgs_lz" class="button button-primary action" value="<?php echo __('Save Messages','loginizer'); ?>" type="submit" /></center>
			</form>
		</div>
	</div>
	
	<!--Bypass a single user-->
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Disable Two Factor Authentication for a User', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i><?php echo __('Here you can disable the Two Factor Authentication settings of a user. In the event a user has forgotten his secret answer or lost his Device App, he will not be able to login. You can reset such a users settings from here.', 'loginizer'); ?></i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label><?php echo __('Username / Email', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('The username or email of the user whose 2FA you would like to disable', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('lz_user_2fa_disable', ''); ?>" name="lz_user_2fa_disable" />
				</td>
			</tr>
		</table><br />
		
		<center><input name="reset_user_lz" class="button button-primary action" value="<?php echo __('Reset 2FA for User', 'loginizer'); ?>" type="submit" /></center>
		</form>
	
		</div>
	</div>
	
	<br />
	
<?php
	
	wp_enqueue_script('jquery-paginate', LOGINIZER_URL.'/jquery-paginate.js', array('jquery'), '1.10.15');
	
?>

<style>
.page-navigation a {
margin: 5px 2px;
display: inline-block;
padding: 5px 8px;
color: #0073aa;
background: #e5e5e5 none repeat scroll 0 0;
border: 1px solid #ccc;
text-decoration: none;
transition-duration: 0.05s;
transition-property: border, background, color;
transition-timing-function: ease-in-out;
}
 
.page-navigation a[data-selected] {
background-color: #00a0d2;
color: #fff;
}
</style>

<script>

jQuery(document).ready(function(){
	jQuery('#lz_wl_2fa_table').paginate({ limit: 11, navigationWrapper: jQuery('#lz_wl_2fa_nav')});
});

// Delete a 2FA Whitelist IP Range
function del_2fa_confirm(field, todo_id, msg){
	var ret = confirm(msg);
	
	if(ret){
		jQuery('#lz_wl_2fa_todo').attr('name', field);
		jQuery('#lz_wl_2fa_todo').val(todo_id);
		jQuery('#lz_wl_2fa_form').submit();
	}
	
	return false;
	
}

// Delete all 2FA Whitelist IP Ranges
function del_2fa_confirm_all(msg){
	var ret = confirm(msg);
	
	if(ret){
		return true;
	}
	
	return false;
	
}

</script>
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Disable Two Factor Authentication for IP', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php echo __('Enter the IP you want to whitelist for two factor authentication', 'loginizer'); ?>
		<form action="" method="post" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="start_ip_w_2fa"><?php echo __('Start IP','loginizer'); ?></label></th>
				<td>
					<input type="text" size="25" style="width:auto;" value="<?php echo(lz_optpost('start_ip_w_2fa')); ?>" name="start_ip_w_2fa" id="start_ip_w_2fa"/> <?php echo __('Start IP of the range','loginizer'); ?> <br />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="end_ip_w_2fa"><?php echo __('End IP (Optional)','loginizer'); ?></label></th>
				<td>
					<input type="text" size="25" style="width:auto;" value="<?php echo(lz_optpost('end_ip_w_2fa')); ?>" name="end_ip_w_2fa" id="end_ip_w_2fa"/> <?php echo __('End IP of the range. <br />If you want to whitelist single IP leave this field blank.','loginizer'); ?> <br />
				</td>
			</tr>
		</table><br />
		<input name="2fa_whitelist_iprange" class="button button-primary action" value="<?php echo __('Add Whitelist IP Range','loginizer'); ?>" type="submit" />		
		<input style="float:right" name="del_all_whitelist" onclick="return del_2fa_confirm_all('<?php echo __('Are you sure you want to delete all Whitelist IP Range(s) for 2FA ?','loginizer'); ?>')" class="button action" value="<?php echo __('Delete All Whitelist IP Range(s) for 2FA','loginizer'); ?>" type="submit" />
		</form>
		</div>
		
		<div id="lz_wl_2fa_nav" style="margin: 5px 10px; text-align:right"></div>
		<table id="lz_wl_2fa_table" class="wp-list-table fixed striped users" border="0" width="95%" cellpadding="10" align="center">
		<tr>
			<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Start IP','loginizer'); ?></th>
			<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('End IP','loginizer'); ?></th>
			<th scope="row" valign="top" style="background:#EFEFEF;"><?php echo __('Date (DD/MM/YYYY)','loginizer'); ?></th>
			<th scope="row" valign="top" style="background:#EFEFEF;" width="100"><?php echo __('Options','loginizer'); ?></th>
		</tr>
		<?php
			if(empty($loginizer['2fa_whitelist'])){
				echo '
				<tr>
					<td colspan="4">
						'.__('No Whitelist IPs for Two Factor Authentication. You will see whitelisted IP ranges here.', 'loginizer').'
					</td>
				</tr>';
			}else{
				foreach($loginizer['2fa_whitelist'] as $ik => $iv){
					echo '
					<tr>
						<td>
							'.$iv['start'].'
						</td>
						<td>
							'.$iv['end'].'
						</td>
						<td>
							'.date('d/m/Y', $iv['time']).'
						</td>
						<td>
							<a class="submitdelete" href="javascript:void(0)" onclick="return del_2fa_confirm(\'delid\', '.$ik.', \'Are you sure you want to delete this IP range for 2FA ?\')">Delete</a>
						</td>
					</tr>';
				}
			}
		?>
		</table>
		<br />
		<form action="" method="post" id="lz_wl_2fa_form">
		<?php wp_nonce_field('loginizer-options'); ?>
		<input type="hidden" value="" name="" id="lz_wl_2fa_todo"/> 
		</form>
		<br />
	
	</div>

	<?php
	loginizer_page_footer();
	
}

// Loginizer - PasswordLess Page
function loginizer_page_passwordless(){
	
	global $loginizer, $lz_error, $lz_env;
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	if(!loginizer_is_premium() && count($_POST) > 0){
		$lz_error['not_in_free'] = __('This feature is not available in the Free version. <a href="'.LOGINIZER_PRICING_URL.'" target="_blank" style="text-decoration:none; color:green;"><b>Upgrade to Pro</b></a>', 'loginizer');
		return loginizer_page_passwordless_T();
	}

	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	if(isset($_POST['save_lz'])){
		
		// In the future there can be more settings
		$option['email_pass_less'] = (int) lz_optpost('email_pass_less');
		$option['passwordless_sub'] = lz_optpost('lz_passwordless_sub');
		$option['passwordless_msg'] = lz_optpost('lz_passwordless_msg');
		$option['passwordless_html'] = (int) lz_optpost('lz_passwordless_html');
		
		// Is there an error ?
		if(!empty($lz_error)){
			return loginizer_page_passwordless_T();
		}
		
		// Save the options
		update_option('loginizer_epl', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Call theme
	loginizer_page_passwordless_T();
}

// Loginizer - PasswordLess Page Theme
function loginizer_page_passwordless_T(){
	
	global $loginizer, $lz_error, $lz_env;
	
	$lz_options = get_option('loginizer_epl');
	
	// Universal header
	loginizer_page_header('PasswordLess Settings');
	
	loginizer_feature_available('PasswordLess Login');
	
	// Saved ?
	if(!empty($GLOBALS['lz_saved'])){
		echo '<div id="message" class="updated"><p>'. __('The settings were saved successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($lz_error)){
		lz_report_error($lz_error);echo '<br />';
	}

	?>

<style>
input[type="text"], textarea, select {
    width: 90%;
}

.form-table label{
	font-weight:bold;
}

.form-table td{
	vertical-align:top;
}

.exp{
	font-size:12px;
}
</style>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('PasswordLess Settings', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<th scope="row" valign="top" style="width:350px !important"><label for="email_pass_less"><?php echo __('Enable PasswordLess Login', 'loginizer'); ?></label></th>
				<td>
					<input type="checkbox" value="1" name="email_pass_less" id="email_pass_less" <?php echo lz_POSTchecked('email_pass_less', (empty($loginizer['email_pass_less']) ? false : true)); echo (defined('SITEPAD') ? 'disabled="disabled"' : '') ?> />
				</td>
			</tr>
			<tr>
				<td colspan="2" valign="top">
					<?php echo __('If enabled, the login screen will just ask for the username <b>OR</b> email address of the user. If such a user exists, an email with a <b>One Time Login </b> link will be sent to the email address of the user. The link will be valid for 10 minutes only.', 'loginizer'); ?><br><br>
					<?php echo __('If a wrong username/email is given, the brute force checker will prevent any brute force attempt !', 'loginizer'); ?>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label for="lz_passwordless_sub"><?php echo __('Email Subject', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the default subject', 'loginizer'); ?></span>
					<br />Default : <?php echo @$loginizer['pl_d_sub']; ?>
				</td>
				<td valign="top">
					<input type="text" size="40" value="<?php echo lz_optpost('lz_passwordless_sub', @$lz_options['passwordless_sub']); ?>" name="lz_passwordless_sub" id="lz_passwordless_sub" />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label for="lz_passwordless_msg"><?php echo __('Email Body', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the default message', 'loginizer'); ?></span>
					<br />Default : <pre style="font-size:10px"><?php echo @$loginizer['pl_d_msg']; ?></pre>
				</td>
				<td valign="top">
					<textarea rows="10" name="lz_passwordless_msg" id="lz_passwordless_msg"><?php echo lz_optpost('lz_passwordless_msg', @$lz_options['passwordless_msg']); ?></textarea>
					<br />
					Variables :
					<br />$email  - Users Email
					<br />$site_name - The Site Name
					<br />$site_url - The Site URL
					<br />$login_url - The Login URL
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top" style="width:350px !important"><label for="lz_passwordless_html"><?php echo __('Send email as HTML', 'loginizer'); ?></label></th>
				<td>
					<input type="checkbox" value="1" name="lz_passwordless_html" id="lz_passwordless_html" <?php echo lz_POSTchecked('lz_passwordless_html', (empty($loginizer['passwordless_html']) ? false : true)); ?> />
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /></center>
		</form>
	
		</div>
	</div>
	<br />

	<?php
	loginizer_page_footer();
	
}

// Loginizer - Security Settings Page
function loginizer_page_security(){
	
	global $loginizer, $lz_error, $lz_env, $wpdb;
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	if(!loginizer_is_premium() && count($_POST) > 0){
		$lz_error['not_in_free'] = __('This feature is not available in the Free version. <a href="'.LOGINIZER_PRICING_URL.'" target="_blank" style="text-decoration:none; color:green;"><b>Upgrade to Pro</b></a>', 'loginizer');
		return loginizer_page_security_T();
	}

	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	if(isset($_POST['save_lz'])){
		
		$option['login_slug'] = lz_optpost('login_slug');
		$option['rename_login_secret'] = (int) lz_optpost('rename_login_secret');
		$option['xmlrpc_slug'] = lz_optpost('xmlrpc_slug');
		$option['xmlrpc_disable'] = (int) lz_optpost('xmlrpc_disable');
		$option['pingbacks_disable'] = (int) lz_optpost('pingbacks_disable');
		
		// Login Slug Valid ?
		if(!empty($option['login_slug'])){
			if(strlen($option['login_slug']) <= 4 || strlen($option['login_slug']) > 50){
				$lz_error['login_slug'] = __('The Login slug length must be greater than <b>4</b> chars and upto <b>50</b> chars long', 'loginizer');
			}
		}
		
		// XML-RPC Slug Valid ?
		if(!empty($option['xmlrpc_slug'])){
			if(strlen($option['xmlrpc_slug']) <= 4 || strlen($option['xmlrpc_slug']) > 50){
				$lz_error['xmlrpc_slug'] = __('The XML-RPC slug length must be greater than <b>4</b> chars and upto <b>50</b> chars long', 'loginizer');
			}
		}
		
		// Is there an error ?
		if(!empty($lz_error)){
			return loginizer_page_security_T();
		}
		
		// Save the options
		update_option('loginizer_security', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Reset the username
	if(isset($_POST['save_lz_admin'])){
		
		// Get the new username
		$current_username = lz_optpost('current_username');
		$new_username = lz_optpost('new_username');
		
		if(empty($current_username)){
			$lz_error['current_username_empty'] = __('Current username is required', 'loginizer');
			return loginizer_page_security_T();
		}
		
		if(empty($new_username)){
			$lz_error['new_username_empty'] = __('New username is required', 'loginizer');
			return loginizer_page_security_T();
		}
		
		// Is the starting of the username having 'admin' ?
		if(@strtolower(substr($new_username, 0, 5)) == 'admin'){
			$lz_error['user_exists'] = __('The username begins with <b>admin</b>. Please change it !', 'loginizer');
			return loginizer_page_security_T();
		}
		
		// Lets check if there is such a user
		$found = get_user_by('login', $new_username);
		
		// Found one !
		if(!empty($found->ID)){
			$lz_error['user_exists'] = __('The new username is already assigned to another user', 'loginizer');
			return loginizer_page_security_T();
		}
	
		$old_user = get_user_by('login', $current_username);
		
		if(empty($old_user->ID)){
			$lz_error['current_username_invalid'] = __('No user found with the current username provided', 'loginizer');
			return loginizer_page_security_T();
		}
		
		if(empty($old_user->caps['administrator'])){
			$lz_error['user_not_admin'] = __('The user is not an administrator. Only administrator user\'s username can be changed.', 'loginizer');
			return loginizer_page_security_T();
		}
		
		// Update the username
		$update_data = array('user_login' => $new_username);
		$where_data = array('ID' => $old_user->ID);
		
		$format = array('%s');
		$where_format = array('%d');
		
		$wpdb->update($wpdb->prefix.'users', $update_data, $where_data, $format, $where_format);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Change the wp-admin slug
	if(isset($_POST['save_lz_wp_admin'])){
		
		// Get the new username
		$option['admin_slug'] = lz_optpost('admin_slug');
		$option['restrict_wp_admin'] = (int) lz_optpost('restrict_wp_admin');
		$option['wp_admin_msg'] = @stripslashes($_POST['wp_admin_msg']);
		$lz_wp_admin_docs = (int) lz_optpost('lz_wp_admin_docs');
		
		// Did you agree to this ?
		if(!empty($option['admin_slug']) && empty($lz_wp_admin_docs)){
			$lz_error['lz_wp_admin_docs'] = __('You have not confirmed that you have read the guide and configured .htaccess. Please read the guide, configure .htaccess and then save these settings and check this checkbox', 'loginizer');
			return loginizer_page_security_T();
		}
		
		// Length
		if(!empty($option['admin_slug']) && (strlen($option['admin_slug']) <= 4 || strlen($option['admin_slug']) > 50)){
			$lz_error['admin_slug'] = __('The new Admin slug length must be greater than <b>4</b> chars and upto <b>50</b> chars long', 'loginizer');
			return loginizer_page_security_T();
		}
		
		// Only regular characters
		if(preg_match('/[^\w\d\-_]/is', $option['admin_slug'])){
			$lz_error['admin_slug_chars'] = __('Special characters are not allowed', 'loginizer');
			return loginizer_page_security_T();
		}
		
		// Update the option
		update_option('loginizer_wp_admin', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	
	// Save blacklisted usernames
	if(isset($_POST['save_lz_bl_users'])){
		
		$usernames = isset($_POST['lz_bl_users']) && is_array($_POST['lz_bl_users']) ? $_POST['lz_bl_users'] : array();
		
		// Process the usernames i.e. remove blanks
		foreach($usernames as $k => $v){
			$v = trim($v);
			
			// Unset blank values
			if(empty($v)){
				unset($usernames[$k]);
			}
			
			// Disallow these special characters to avoid XSS or any other security vulnerability
			if(preg_match('/[\<\>\"\']/', $v)){
				unset($usernames[$k]);
			}
		}
		
		// Update the blacklist
		update_option('loginizer_username_blacklist', array_values($usernames));
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	
	// Save blacklisted domains
	if(isset($_POST['save_lz_bl_domains'])){
		
		$domains = isset($_POST['lz_bl_domains']) && is_array($_POST['lz_bl_domains']) ? $_POST['lz_bl_domains'] : array();
		
		// Process the domains i.e. remove blanks
		foreach($domains as $k => $v){
			$v = trim($v);
			
			// Unset blank values
			if(empty($v)){
				unset($domains[$k]);
			}
			
			// Disallow these special characters to avoid XSS or any other security vulnerability
			if(preg_match('/[\<\>\"\']/', $v)){
				unset($domains[$k]);
			}
		}
		
		// Update the blacklist
		update_option('loginizer_domains_blacklist', array_values($domains));
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Call theme
	loginizer_page_security_T();
	
}

// Loginizer - Security Settings Page Theme
function loginizer_page_security_T(){
	
	global $loginizer, $lz_error, $lz_env;
	
	// Universal header
	loginizer_page_header('Security Settings');
	
	loginizer_feature_available('Security Settings');
	
	// Saved ?
	if(!empty($GLOBALS['lz_saved'])){
		echo '<div id="message" class="updated"><p>'. __('The settings were saved successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($lz_error)){
		lz_report_error($lz_error);echo '<br />';
	}
	
	$current_admin = get_user_by('id', 1);

	?>

<style>
input[type="text"], textarea, select {
    width: 70%;
}

.form-table label{
	font-weight:bold;
}

.exp{
	font-size:12px;
}
</style>

<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Rename Login Page', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i>You can rename your Login page from <b><?php echo $loginizer['login_basename']; ?></b> to anything of your choice e.g. mylogin. This would make it very difficult for automated attack bots to know where to login !</i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important">
					<label><?php echo __('New Login Slug', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the original login URL', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_POSTval('login_slug', $loginizer['login_slug']); ?>" name="login_slug" />
				</td>
			</tr>
	
<?php

if(!defined('SITEPAD')){

?>
			<tr>
				<td scope="row" valign="top" style="width:200px !important">
					<label><?php echo __('Access Secretly Only', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If set, then all Login URL\'s will still point to '.$loginizer['login_basename'].' and users will have to access the New Login Slug by typing it in the browser.', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" name="rename_login_secret" <?php echo lz_POSTchecked('rename_login_secret', (empty($loginizer['rename_login_secret']) ? false : true)); ?> />
				</td>
			</tr>
	
<?php

}

?>
		</table><br />
		<center><input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /></center>
	
		</div>
	</div>
	<br />
	
	<?php
	
	if(!defined('SITEPAD')){

	?>

	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('XML-RPC Settings', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i><?php echo __('WordPress\'s XML-RPC feature allows external services to access and modify content on the site. Services like the Jetpack plugin, the WordPress mobile app, pingbacks, etc make use of the XML-RPC feature. If this site does not use a service that requires XML-RPC, please <b>disable</b> the XML-RPC feature as it prevents attackers from using the feature to attack the site. If your service can use a custom XML-RPC URL, you can also <b>rename</b> the XML-RPC page to a <b>custom slug</b>.', 'loginizer'); ?></i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important">
					<label><?php echo __('Disable XML-RPC', 'loginizer'); ?></label>
				</td>
				<td>
					<input type="checkbox" value="1" name="xmlrpc_disable" <?php echo lz_POSTchecked('xmlrpc_disable', (empty($loginizer['xmlrpc_disable']) ? false : true)); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important">
					<label><?php echo __('Disable Pingbacks', 'loginizer'); ?></label>
				</td>
				<td>
					<input type="checkbox" value="1" name="pingbacks_disable" <?php echo lz_POSTchecked('pingbacks_disable', (empty($loginizer['pingbacks_disable']) ? false : true)); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top">
					<label><?php echo __('New XML-RPC Slug', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the original XML-RPC URL', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('xmlrpc_slug', $loginizer['xmlrpc_slug']); ?>" name="xmlrpc_slug" />
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /></center>
	
		</div>
	</div>
	<br />
	
	<?php
	
	}

	?>
	
</form>
	
<?php

if(!defined('SITEPAD')){

?>

<script type="text/javascript">


function dirname(path) {
  return path.replace(/\\/g, '/').replace(/\/[^/]*\/?$/, '');
}

function lz_test_wp_admin(){
	
	var data = new Object();
	data["action"] = "loginizer_wp_admin";
	data["nonce"]	= "<?php echo wp_create_nonce('loginizer_admin_ajax');?>";
	
	var new_ajaxurl = dirname(dirname(ajaxurl))+'/'+jQuery('#lz_admin_slug').val()+'/admin-ajax.php';
	
	// AJAX and on success function
	jQuery.post(new_ajaxurl, data, function(response){
		
		if(response['result'] == 1){
			alert("<?php echo __('Everything seems to be good. You can proceed to save the settings !', 'loginizer'); ?>");
		}		
	
	// Throw an error for failures
	}).fail(function() {
		alert("<?php echo __('There was an error connecting to WordPress with the new Admin Slug. Did you configure everything properly ?', 'loginizer'); ?>");
	});
	//jQuery.ajax('<input type="text" size="30" value="" name="lz_bl_users[]" class="lz_bl_users" />');
	return false;
};

</script>

<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Rename wp-admin access', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<?php 
				if(preg_match('/(apache|litespeed|lsws)/is', $_SERVER["SERVER_SOFTWARE"])){
					// Supported. Do nothing
				}else{
					echo '<tr>
					<td scope="row" valign="top" colspan="2">
						<div style="color:#a94442; background-color:#f2dede; border-color:#ebccd1; padding:15px; border:1px solid transparent; border-radius:4px;">'.__('Rename wp-admin access feature is supported only on Apache and Litespeed', 'loginizer').'</div>
					</td>
					</tr>';
				}
			?>
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i>You can rename your WordPress Admin access URL <b>wp-admin</b> to anything of your choice e.g. my-admin. This will require you to change .htaccess, so please follow <a href="<?php echo LOGINIZER_DOCS;?>Renaming_the_WP-Admin_Area" target="_blank">our guide</a> on how to do so !</i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important">
					<label><?php echo __('New wp-admin Slug', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Set blank to reset to the original wp-admin URL', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('admin_slug', $loginizer['admin_slug']); ?>" name="admin_slug" id="lz_admin_slug" />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:200px !important">
					<label><?php echo __('Disable wp-admin access', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If set, then only the new admin slug will work and access to the Old Admin Slug i.e. wp-admin will be disabled. If anyone accesses wp-admin, a warning will be shown.<br><label>NOTE: Please use this option cautiously !</label>', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" id="lz_restrict_wp_admin" onchange="lz_wp_admin_msg_toggle()" value="1" name="restrict_wp_admin" <?php echo lz_POSTchecked('restrict_wp_admin', (empty($loginizer['restrict_wp_admin']) ? false : true)); ?> />
				</td>
			</tr>
			<tr id="lz_wp_admin_msg_row" style="display:none">
				<td scope="row" valign="top">
					<label><?php echo __('WP-Admin Error Message', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('Error message to show if someone accesses wp-admin', 'loginizer'); ?></span> Default : <?php echo $loginizer['wp_admin_d_msg']; ?>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_htmlizer(!empty($_POST['wp_admin_msg']) ? stripslashes($_POST['wp_admin_msg']) : @$loginizer['wp_admin_msg']); ?>" name="wp_admin_msg" id="lz_wp_admin_msg" />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:200px !important">
					<label><?php echo __('I have setup .htaccess', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('You need to confirm that you have configured .htaccess as per <a href="'.LOGINIZER_DOCS.'Renaming_the_WP-Admin_Area" target="_blank">our guide</a> so that we can safely enable this feature', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="checkbox" value="1" name="lz_wp_admin_docs" />
					<input type="button" onclick="lz_test_wp_admin()" class="button" style="background: #5cb85c; color:white; border:#5cb85c" value="<?php echo __('Test New WP-Admin Slug', 'loginizer'); ?>" />
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz_wp_admin" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /></center>
	
		</div>
	</div>
	<br />
</form>

<script type="text/javascript">

function lz_wp_admin_msg_toggle(){
	var ele = jQuery('#lz_restrict_wp_admin')[0];
	if(ele.checked){
		jQuery('#lz_wp_admin_msg_row').show();
	}else{
		jQuery('#lz_wp_admin_msg_row').hide();
	}
};

lz_wp_admin_msg_toggle();

</script>
	

<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Change Admin Username', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i><?php echo __('You can change the Admin Username from here to anything of your choice e.g. iamtheboss. This would make it very difficult for automated attack bots to know what is the admin username !', 'loginizer'); ?></i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important">
					<label for="current_username"><?php echo __('Current Username', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('The current username you want to change', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('current_username', (!empty($current_admin->user_login) ? $current_admin->user_login : '')); ?>" name="current_username" id="current_username" />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important">
					<label for="new_username"><?php echo __('New Username', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('The new username you want to set', 'loginizer'); ?></span>
				</td>
				<td>
					<input type="text" size="50" value="<?php echo lz_optpost('new_username', ''); ?>" name="new_username" id="new_username" />
				</td>
			</tr>
		</table><br />
		<i><?php echo __('Note: Username can be changed only for administrator users.'); ?></i>
		<center><input name="save_lz_admin" class="button button-primary action" value="<?php echo __('Set the Username', 'loginizer'); ?>" type="submit" /></center>
	
		</div>
	</div>
</form>

<script type="text/javascript">
function add_lz_bl_users(){
	jQuery("#lz_bl_users").append('<input type="text" size="30" value="" name="lz_bl_users[]" class="lz_bl_users" />');
	return false;
};
</script>

<style>
.lz_bl_users, .lz_bl_domains{
	margin-bottom:20px;
}
</style>

<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Username Auto Blacklist', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i><?php echo __('Attackers generally use common usernames like <b>admin, administrator, or variations of your domain name / business name</b>. You can specify such username here and Loginizer will auto-blacklist the IP Address(s) of clients who try to use such username(s).', 'loginizer'); ?></i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important; vertical-align:top !important;">
					<label><?php echo __('Username(s)', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('You can use - <b>*</b> (Star)- as a wild card as well. Blank fields will be ignored', 'loginizer'); ?></span>
				</td>
				<td>
					<div id="lz_bl_users">
					<?php
					
					$usernames = isset($_POST['lz_bl_users']) && is_array($_POST['lz_bl_users']) ? $_POST['lz_bl_users'] : $loginizer['username_blacklist'];
					
					if(empty($usernames)){
						$usernames[] = '';
					}
					
					foreach($usernames as $_user){
						
						// Disallow these special characters to avoid XSS or any other security vulnerability
						if(preg_match('/[\<\>\"\']/', $_user)){
							continue;
						}
						
						echo '<input type="text" size="30" value="'.$_user.'" name="lz_bl_users[]" class="lz_bl_users" />';
					}
					
					?>
					</div>
					<br />
					<input class="button" type="button" value="<?php echo __('Add New Username', 'loginizer'); ?>" onclick="return add_lz_bl_users();" style="float:right" />
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz_bl_users" class="button button-primary action" value="<?php echo __('Save Username(s)', 'loginizer'); ?>" type="submit" /></center>
	
		</div>
	</div>
</form>

<script type="text/javascript">
function add_lz_bl_domains(){
	jQuery("#lz_bl_domains").append('<input type="text" size="30" value="" name="lz_bl_domains[]" class="lz_bl_domains" />');
	return false;
};
</script>


<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('New Registration Domain Blacklist', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" colspan="2">
					<i>If you would like to ban new registrations from a particular domain, you can use this utility to do so.</i>
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:40% !important; vertical-align:top !important;">
					<label><?php echo __('Domain(s)', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('You can use - <b>*</b> (Star)- as a wild card as well. Blank fields will be ignored', 'loginizer'); ?></span>
				</td>
				<td>
					<div id="lz_bl_domains">
					<?php
					
					$domains = isset($_POST['lz_bl_domains']) && is_array($_POST['lz_bl_domains']) ? $_POST['lz_bl_domains'] : $loginizer['domains_blacklist'];
					
					if(empty($domains)){
						$domains[] = '';
					}
					
					foreach($domains as $_domain){
						
						// Disallow these special characters to avoid XSS or any other security vulnerability
						if(preg_match('/[\<\>\"\']/', $_domain)){
							continue;
						}
						
						echo '<input type="text" size="30" value="'.$_domain.'" name="lz_bl_domains[]" class="lz_bl_domains" />';
					}
					
					?>
					</div>
					<br />
					<input class="button" type="button" value="<?php echo __('Add New Domain', 'loginizer'); ?>" onclick="return add_lz_bl_domains();" style="float:right" />
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz_bl_domains" class="button button-primary action" value="<?php echo __('Save Domains(s)', 'loginizer'); ?>" type="submit" /></center>
	
		</div>
	</div>	
</form>

<?php

}
	
	loginizer_page_footer();
	
}

// Loginizer - Checksum load data
function loginizer_page_checksums_L(&$files, &$_ignores){
	
	global $loginizer, $lz_error, $lz_env;
	
	// Load any mismatched files and ignores
	$files = get_option('loginizer_checksums_diff');
	$_ignores = get_option('loginizer_checksums_ignore');
	$_ignores = is_array($_ignores) ? $_ignores : array(); // SHOULD ALWAYS BE PURE
	$ignores = array();
	
	foreach($_ignores as $ik => $iv){
		$ignores[$iv] = array();
		if(!empty($files[$iv])){
			$ignores[$iv] = $files[$iv];
		}
	}
	
	$lz_env['files'] = $files;
	$lz_env['ignores'] = $ignores;

}
	
// Loginizer - PasswordLess Page
function loginizer_page_checksums(){
	
	global $loginizer, $lz_error, $lz_env;
	
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	if(!loginizer_is_premium() && count($_POST) > 0){
		$lz_error['not_in_free'] = __('This feature is not available in the Free version. <a href="'.LOGINIZER_PRICING_URL.'" target="_blank" style="text-decoration:none; color:green;"><b>Upgrade to Pro</b></a>', 'loginizer');
		return loginizer_page_checksums_T();
	}

	/* Make sure post was from this page */
	if(count($_POST) > 0){
		check_admin_referer('loginizer-options');
	}
	
	// Are we to run it ?
	if(isset($_REQUEST['lz_run_checksum'])){
		loginizer_checksums();
	}
	
	loginizer_page_checksums_L($files, $_ignores);
	
	$lz_env['csum_freq'][1] = __('Once a Day', 'loginizer');
	$lz_env['csum_freq'][7] = __('Once a Week', 'loginizer');
	$lz_env['csum_freq'][30] = __('Once a Month', 'loginizer');
	
	if(isset($_POST['save_lz'])){
		
		// In the future there can be more settings
		$option['disable_checksum'] = (int) lz_optpost('disable_checksum');
		$option['no_checksum_email'] = (int) lz_optpost('no_checksum_email');
		$option['checksum_frequency'] = (int) lz_optpost('checksum_frequency');
		$option['checksum_time'] = lz_optpost('checksum_time');
		
		// Is there an error ?
		if(!empty($lz_error)){
			return loginizer_page_checksums_T();
		}
		
		// Save the options
		update_option('loginizer_checksums', $option);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Add or remove from ignore list
	if(isset($_POST['save_lz_csum_ig'])){
		
		if(@is_array($_POST['checksum_del_ignore'])){
			
			foreach($_POST['checksum_del_ignore'] as $k => $v){
				$key = array_search($v, $_ignores);
				if($key !== false){
					unset($_ignores[$key]);
				}
			}
			
			// Save it
			update_option('loginizer_checksums_ignore', $_ignores);
			
		}
		
		if(@is_array($_POST['checksum_add_ignore'])){
			
			foreach($_POST['checksum_add_ignore'] as $k => $v){
				if(!empty($files[$v])){
					$_ignores[] = $v;
				}
			}
			
			// Save it
			update_option('loginizer_checksums_ignore', $_ignores);
			
		}
		
		// Reload
		loginizer_page_checksums_L($files, $_ignores);
		
		// Mark as saved
		$GLOBALS['lz_saved'] = true;
		
	}
	
	// Call theme
	loginizer_page_checksums_T();
}

// Loginizer - PasswordLess Page Theme
function loginizer_page_checksums_T(){
	
	global $loginizer, $lz_error, $lz_env;
	
	// Universal header
	loginizer_page_header('File Checksum Settings');
	
	loginizer_feature_available('File Checksum');
	
	wp_enqueue_script('jquery-clockpicker', LOGINIZER_URL.'/jquery-clockpicker.min.js', array('jquery'), '0.0.7');
	wp_enqueue_style('jquery-clockpicker', LOGINIZER_URL.'/jquery-clockpicker.min.css', array(), '0.0.7');
	
	// Saved ?
	if(!empty($GLOBALS['lz_saved'])){
		echo '<div id="message" class="updated"><p>'. __('The settings were saved successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Did we just run the checksums
	if(isset($_REQUEST['lz_run_checksum'])){
		echo '<div id="message" class="updated"><p>'. __('The Checksum process was executed successfully', 'loginizer'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($lz_error)){
		lz_report_error($lz_error);echo '<br />';
	}

	?>

<style>
input[type="text"], textarea, select {
    width: 70%;
}

.form-table label{
	font-weight:bold;
}

.exp{
	font-size:12px;
}
</style>

<script>
function lz_apply_status(ele, the_class){
	
	var status = ele.checked;
	jQuery(the_class).each(function(){
		this.checked = status;
	});
	
}
</script>

	<div id="" class="postbox">
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Checksum Settings', 'loginizer'); ?></span>
		</h2>
		</div>
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="form-table">
			<tr>
				<td scope="row" valign="top" style="width:400px !important">
					<label><?php echo __('Disable Checksum of WP Core', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If disabled, Loginizer will not check your sites core files against the WordPress checksum list.', 'loginizer'); ?></span>
				</td>
				<td valign="top">
					<input type="checkbox" value="1" name="disable_checksum" <?php echo lz_POSTchecked('disable_checksum', (empty($loginizer['disable_checksum']) ? false : true)); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:400px !important">
					<label><?php echo __('Disable Email of Checksum Results', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If checked, Loginizer will not email you the checksum results.', 'loginizer'); ?></span>
				</td>
				<td valign="top">
					<input type="checkbox" value="1" name="no_checksum_email" <?php echo lz_POSTchecked('no_checksum_email', (empty($loginizer['no_checksum_email']) ? false : true)); ?> />
				</td>
			</tr>
			<tr>
				<td scope="row" valign="top" style="width:400px !important">
					<label><?php echo __('Checksum Frequency', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If Checksum is enabled, at what frequency should the checksums be performed.', 'loginizer'); ?></span>
				</td>
				<td valign="top">					
					<select name="checksum_frequency">
						<?php
							foreach($lz_env['csum_freq'] as $k => $v){
								echo '<option '.lz_POSTselect('checksum_frequency', $k, ($loginizer['checksum_frequency'] == $k ? true : false)).' value="'.$k.'">'.$v.'</value>';								
							}
						?>
					</select>
				</td>
			</tr>
			<tr id="lz_checksum_time">
				<td scope="row" valign="top" style="width:400px !important">
					<label><?php echo __('Time of Day', 'loginizer'); ?></label><br>
					<span class="exp"><?php echo __('If Checksum is enabled, what time of day should Loginizer do the check. Note : The check will be done on or after this time has elapsed as per the accesses being made.', 'loginizer'); ?></span>
				</td>
				<td valign="top">
					<div class="input-group clockpicker" data-autoclose="true">
						<input type="text" name="checksum_time" class="form-control" value="<?php echo (empty($loginizer['checksum_time']) ? '00:00' : $loginizer['checksum_time']);?>">
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-time"></span>
						</span>
					</div>
					<script type="text/javascript">
					jQuery(document).ready(function(){
						(function($) {
							$('.clockpicker').clockpicker({donetext: 'Done'});
						})(jQuery);
					});
					</script>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php echo __('If disabled, Loginizer will not check your sites core files against the WordPress checksum list.', 'loginizer'); ?>
				</td>
			</tr>
		</table><br />
		<center><input name="save_lz" class="button button-primary action" value="<?php echo __('Save Settings', 'loginizer'); ?>" type="submit" /><input name="lz_run_checksum" style="float:right; background: #5cb85c; color:white; border:#5cb85c" class="button button-secondary" value="<?php echo __('Do a Checksum Now', 'loginizer'); ?>" type="submit" /></center>
		</form>
	
		</div>
	</div>
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Mismatching Files', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="wp-list-table fixed striped users" border="0" width="100%" cellpadding="10" align="center">
			<?php
			
			$files = $lz_env['files'];
			
			// Avoid undefined notice for $files
			if(!empty($files)){
				foreach($files as $k => $v){
					if(!empty($lz_env['ignores'][$k])){
						unset($files[$k]);
					}
				}
			}
			
			echo '
			<tr>
				<th style="background:#EFEFEF;">'.__('Relative Path', 'loginizer').'</th>
				<th style="width:240px; background:#EFEFEF;">'.__('Found', 'loginizer').'</th>
				<th style="width:240px; background:#EFEFEF;">'.__('Should be', 'loginizer').'</th>
				<th style="width:10px; background:#EFEFEF;"><input type="checkbox" onchange="lz_apply_status(this, \'.csum_add_ig\');" /></th>
			</tr>';
			
			if(is_array($files) && count($files) > 0){
				
				foreach($files as $k => $v){
					
					echo '
				<tr>
					<td>'.$k.'</td>
					<td>'.$v['cur_md5'].'</td>
					<td>'.$v['md5'].'</td>
					<td><input type="checkbox" name="checksum_add_ignore[]" class="csum_add_ig" value="'.$k.'" /></td>
				</tr>';
					
				}
				
			}else{
				
				echo '
				<tr>
					<td colspan="4" align="center">'.__('This is great ! No file with any wrong checksum has been found.').'</td>
				</tr>';
				
			}
			
			?>
		</table><br />
		<center><input name="save_lz_csum_ig" class="button button-primary action" value="<?php echo __('Add Selected to Ignore List', 'loginizer'); ?>" type="submit" /></center>
		</form>
		</div>
		
	</div>
	<br />
	
	<div id="" class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('Ignore List', 'loginizer'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data" loginizer-premium-only="1">
		<?php wp_nonce_field('loginizer-options'); ?>
		<table class="wp-list-table fixed striped users" border="0" width="100%" cellpadding="10" align="center">
			<?php

			$ignores = $lz_env['ignores'];
			
			echo '
			<tr>
				<th style="background:#EFEFEF;">'.__('Relative Path', 'loginizer').'</th>
				<th style="width:240px; background:#EFEFEF;">'.__('Found', 'loginizer').'</th>
				<th style="width:240px; background:#EFEFEF;">'.__('Should be', 'loginizer').'</th>
				<th style="width:10px; background:#EFEFEF;"><input type="checkbox" onchange="lz_apply_status(this, \'.csum_del_ig\');" /></th>
			</tr>';
	
			// Load any mismatched files
			$files = $ignores;
			
			if(is_array($files) && count($files) > 0){
				
				foreach($files as $k => $v){
					
					echo '
				<tr>
					<td>'.$k.'</td>
					<td>'.$v['cur_md5'].'</td>
					<td>'.$v['md5'].'</td>
					<td><input type="checkbox" name="checksum_del_ignore[]" class="csum_del_ig" value="'.$k.'" /></td>
				</tr>';
					
				}
				
			}else{
				
				echo '
				<tr>
					<td colspan="4" align="center">'.__('No files have been added to the ignore list').'</td>
				</tr>';
				
			}
			
			?>
		</table><br />
		<center><input name="save_lz_csum_ig" class="button button-primary action" value="<?php echo __('Remove Selected from Ignore List', 'loginizer'); ?>" type="submit" /></center>
		</form>
		</div>
		
	</div>
	<br />

	<?php
	loginizer_page_footer();
	
}

function loginizer_dismiss_newsletter(){

	// Some AJAX security
	check_ajax_referer('loginizer_admin_ajax', 'nonce');
	 
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	update_option('loginizer_dismiss_newsletter', time());
	echo 1;
	wp_die();
}

add_action('wp_ajax_loginizer_dismiss_newsletter', 'loginizer_dismiss_newsletter');

function loginizer_newsletter_subscribe(){
	
	$newsletter_dismiss = get_option('loginizer_dismiss_newsletter');
	
	if(!empty($newsletter_dismiss)){
		return;
	}
	
	$env['url'] = 'https://loginizer.com/';
	
	echo '
	<style>
	.newsletter_container{
		color: #000000;
		background: #FFFFFF;
		text-align:center;
	}
	.subscribe_form_row{
		color: #000000;
		padding-bottom:0px !important;
	}
	.subscribe_heading{
		font-size:22px;
	}
	</style>
				
	<div class="notice my-loginizer-dismiss-notice is-dismissible" style="background:#FFF;padding:15px; border: 1px solid #ccd0d4; width:80%;margin-left:0px;margin:auto;">
		<div class="container">
			<div class="col-md-6 col-md-offset-3 text-center newsletter_container">
				<h2 style="font-weight:100; margin-bottom:20px; margin-top:5px;" class="subscribe_heading">Subscribe to our Newsletter</h2>
				<form class="form-inline" action="" method="POST">
					<div class="row subscribe_form_row">
						<div class="col-md-12">
							<input type="email" name="email" size="40" id="subscribe_email" class="" placeholder="email@example.com" value="">&nbsp;
							<input type="button" name="subscribe" id="subscribe_button" class="button button-primary" value="Subscribe" onclick="loginizer_email_subscribe();" style="margin-top:0px;">
						</div>
						<div class="col-md-3">
						</div>
					</div>
				</form>
				<p><b>Note :</b> If a Loginizer account does not exist it will be created.</p>
			</div>
		</div>
	</div><br />
	
	<script type="text/javascript">
		function loginizer_dismiss_newsletter(){
	
			var data = new Object();
			data["action"] = "loginizer_dismiss_newsletter";
			data["nonce"]	= "'.wp_create_nonce('loginizer_admin_ajax').'";
			
			var admin_url = "'.admin_url().'"+"admin-ajax.php";
			jQuery.post(admin_url, data, function(response){
				
			});
			
		}
		
		function loginizer_email_subscribe(){
			var subs_location = "'.$env['url'].'?email="+encodeURIComponent(jQuery("#subscribe_email").val());
			window.open(subs_location, "_blank");
		}
		jQuery(document).on("click", ".my-loginizer-dismiss-notice .notice-dismiss", loginizer_dismiss_newsletter);
	</script>';
	
	return true;
}


// Sorry to see you going
register_uninstall_hook(LOGINIZER_FILE, 'loginizer_deactivation');

function loginizer_deactivation(){

global $wpdb;

	$sql = array();
	$sql[] = "DROP TABLE ".$wpdb->prefix."loginizer_logs;";

	foreach($sql as $sk => $sv){
		$wpdb->query($sv);
	}

	delete_option('loginizer_version');
	delete_option('loginizer_options');
	delete_option('loginizer_last_reset');
	delete_option('loginizer_whitelist');
	delete_option('loginizer_blacklist');
	delete_option('loginizer_msg');
	delete_option('loginizer_2fa_msg');
	delete_option('loginizer_2fa_email_template');
	delete_option('loginizer_security');
	delete_option('loginizer_wp_admin');

}

