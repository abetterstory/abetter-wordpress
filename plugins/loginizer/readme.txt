=== Loginizer ===
Contributors: loginizer, pagelayer, softaculous
Tags: access, admin, Loginizer, login, logs, ban ip, failed login, ip, whitelist ip, blacklist ip, failed attempts, lockouts, hack, authentication, login, security, rename login url, rename login, rename wp-admin, secure wp-admin, rename admin url, secure admin
Requires at least: 3.0
Tested up to: 5.5
Requires PHP: 5.5
Stable tag: 1.5.5
License: LGPLv2.1
License URI: http://www.gnu.org/licenses/lgpl-2.1.html

Loginizer is a WordPress security plugin which helps you fight against bruteforce attacks.

== Description ==

Loginizer is a WordPress plugin which helps you fight against bruteforce attack by blocking login for the IP after it reaches maximum retries allowed. You can blacklist or whitelist IPs for login using Loginizer. You can use various other features like Two Factor Auth, reCAPTCHA, PasswordLess Login, etc. to improve security of your website.

Loginizer is actively used by more than 1000000+ WordPress websites.

You can find our official documentation at <a href="https://loginizer.com/docs">https://loginizer.com/docs</a>. We are also active in our community support forums on <a href="https://wordpress.org/support/plugin/loginizer">wordpress.org</a> if you are one of our free users. Our Premium Support Ticket System is at <a href="https://loginizer.deskuss.com">https://loginizer.deskuss.com</a>

= Get Support and Pro Features =

Get professional support from our experts and pro features to take your site's security to the next level with <a href="https://loginizer.com/pricing">Loginizer-Security</a>.

Pro Features :

* MD5 Checksum - of Core WordPress Files. The admin can check and ignore files as well.
* PasswordLess Login - At the time of Login, the username / email address will be asked and an email will be sent to the email address of that account with a temporary link to login.
* Two Factor Auth via Email - On login, an email will be sent to the email address of that account with a temporary 6 digit code to complete the login.
* Two Factor Auth via App - The user can configure the account with a 2FA App like Google Authenticator, Authy, etc.
* Login Challenge Question - The user can setup a <i>Challenge Question and Answer</i> as an additional security layer. After Login, the user will need to answer the question to complete the login.
* reCAPTCHA - Google's reCAPTCHA v3/v2 can be configured for the Login screen, Comments Section, Registration Form, etc. to prevent automated brute force attacks. Supports WooCommerce as well.
* Rename Login Page - The Admin can rename the login URL (slug) to something different from wp-login.php to prevent automated brute force attacks.
* Rename WP-Admin URL - The Admin area in WordPress is accessed via wp-admin. With loginizer you can change it to anything e.g. site-admin
* Rename Login with Secrecy - If set, then all Login URL's will still point to wp-login.php and users will have to access the New Login Slug by typing it in the browser.
* Disable XML-RPC - An option to simply disable XML-RPC in WordPress. Most of the WordPress users don't need XML-RPC and can disable it to prevent automated brute force attacks.
* Rename XML-RPC - The Admin can rename the XML-RPC to something different from xmlrpc.php to prevent automated brute force attacks.
* Username Auto Blacklist - Attackers generally use common usernames like admin, administrator, or variations of your domain name / business name. You can specify such username here and Loginizer will auto-blacklist the IP Address(s) of clients who try to use such username(s).
* New Registration Domain Blacklist - If you would like to ban new registrations from a particular domain, you can use this utility to do so.
* Change the Admin Username - The Admin can rename the admin username to something more difficult.
* Auto Blacklist IPs - IPs will be auto blacklisted, if certain usernames saved by the Admin are used to login by malicious bots / users.
* Disable Pingbacks - Simple way to disable PingBacks.

Features in Loginizer include:

* Blocks IP after maximum retries allowed
* Extended Lockout after maximum lockouts allowed
* Email notification to admin after max lockouts
* Blacklist IP/IP range
* Whitelist IP/IP range
* Check logs of failed attempts
* Create IP ranges
* Delete IP ranges
* Licensed under LGPLv2.1
* Safe & Secure


== Installation ==

Upload the Loginizer plugin to your blog, Activate it.
That's it. You're done!

== Screenshots ==

1. Login Failed Error message
2. Loginizer Dashboard page
3. Loginizer Brute Force Settings page

== Changelog ==

= 1.5.5 =
* [Bug Fix] Remember me during login was not working with 2FA features. This is fixed. 
* [Task] Loginizer is now supported for translation via WordPress.
* [Task] Added option to fully customize the Lockout error message.

= 1.5.4 =
* [Task] Added option to customize Lockout Error message.

= 1.5.3 =
* [Task] Compatible with WordPress 5.5
* [Bug Fix] Due to a conflict with some plugin the upgrade for Loginizer Premium version did not work. This is fixed.

= 1.5.2 =
* [Task] Some strings were not available in translations. They can now be translated.

= 1.5.1 =
* [Task] Allowed to change the username of any administrator account. Previously it was supported only for user id 1
* [Bug Fix] Fixed some lines that generated PHP notice

= 1.5.0 =
* [Task] Admins can now customize "attempt(s) left" error message.

= 1.4.9 =
* [Bug Fix] Prevent brute force on 2FA pages.

= 1.4.8 =
* [Premium Feature] Added Google reCAPTCHA v3 and v2 invisible.

= 1.4.7 =
* [Security Fix] Our team internally conducted a security audit and have fixed couple of security issues. We recommend all users to upgrade to the latest version asap.

= 1.4.6 =
* [Task] Added Timezone offset in the Brute Force attempts list to get the exact time of the failed login attempt.
* [Bug Fix] For HTTP_X_FORWARDED_FOR if the value had multiple IPs including proxied IPs, the user IP detection failed. This is fixed.
* [Bug Fix] Undefined variable was used in the title on the dashboard page. This is fixed.

= 1.4.5 =
* [Announcement] Loginizer has joined forces with Softaculous team. 
* [Task] Added OTP validity time in the email sent for OTP for login.
* [Bug Fix] In the premium version the Math Captcha used to fail in some conditions. This is fixed. 

= 1.4.4 =
* [Task] Made Loginizer compatible with PHP 7.4
* [Bug Fix] The password field was not hidden in some themes for PasswordLess Login. This is fixed.

= 1.4.3 =
* [Bug Fix] At the time of login if recaptcha or 2FA using OTP was enabled and if you check mark on "Remember me", the login used to go to an invalid redirect URL and did not load anything. This has been fixed now.

= 1.4.2 =
* [Task] Tested up to: WordPress 5.2.0
* [Bug Fix] Placement of Captcha corrected for WooCommerce at the time of checkout for endusers.
* [Bug Fix] Checksum check shall now skip for the files which are present in default WordPress package and does not exist in the installation like deleted theme(s)/plugin(s).
* [Bug Fix] Grammar correction

= 1.4.1 =
* [Task] Tested up to: WordPress 5.0.2
* [Task] Refresh license will throw an error if the response received from our server is invalid
* [Bug Fix] The OTP input box (with respect to 2FA via SMS) was empty if the user was freshly registered and did not login at all. This is fixed.

= 1.4.0 =
* [Feature] New Registration Domain Blacklist - If you would like to ban new registrations from a particular domain, you can use this utility to do so.
* [Feature] Made Loginizer Security for BuddyPress compatibility.
* [Task] Added a method to reset wp-admin rename settings if you get locked out.
* [Bug Fix] There is an XSS bug introduced in version 1.3.8. This is fixed. Please upgrade ASAP.
* [Bug Fix] In the user 2FA security wizard, the default selected option was wrongly shown when the user had not set any preference for 2FA. This is fixed. 

= 1.3.9 =
* [Feature] Added an option to Enable / Disable Brute Force checks.
* [Feature] Added the feature to log the URL of the page from which the brute force attempt is being made.
* [Bug Fix] Blanking the login slug used to show the value after submission. This is fixed.
* [Bug Fix] Allowed HTML chars in wp_admin_msg for renaming WP-ADMIN.

= 1.3.8 =
* [Feature] Added Roles selection for Two Factor Authentication. The Admin can now enable 2FA for specific roles.
* [Feature] Added a Tester for WP-Admin Slug Renaming feature. Now you can test the new slug before saving it.
* [Feature] Added option to customize the Passwordless email being sent to the user.
* [Feature] Added a custom WP-Admin restriction message if wp-admin is restricted.
* [Feature] Added an option to Delete the entire Blacklist / Whitelist IP Ranges.
* [Feature] Custom IP Header added as an option for detecting the IP as per the Proxy settings of a server.
* [Task] Added an option to clear reCAPTCHA settings.
* [Task] Added Debugger in Updater
* [Task] Updater will show "Install License Key to check for Updates"
* [Bug Fix] In WooCommerce the number of login retries left was not being shown. This is fixed.

= 1.3.7 =
* [Bug Fix] Blacklist and Whitelist IPs were not being deleted. This is fixed.

= 1.3.6 =
* [Feature] Pagination added to the Blacklist and Whitelist IPs
* [Bug Fix] There used to be a login issue over SSL when wp-admin area is renamed. This is fixed.
* [Bug Fix] SQL Injection fix for X-Forwarded-For. This is fixed. Vulnerability was found by Jonas Lejon of WPScans.com
* [Bug Fix] There was a missing referrer check in Blacklist and Whitelist IP Wizard. This is fixed.

= 1.3.5 =
* [Feature] Added a simple Math Captcha to show Maths Questions, if someone doesn’t want to use Google Captcha
* [Feature] Added a wizard for admins to set their own language strings for Brute Force messages
* [Bug Fix] In WooCommerce the Lost Password, Reset Password and Comment Form captcha verification failed. This is fixed.
* [Bug Fix] Hide Captcha for logged in users was not working. This is fixed.
* [Bug Fix]	Twitter box shown in Loginizer was not accessed over HTTPS.

= 1.3.4 =
* [Bug Fix] Fixed the BigInteger Class for PHP 7 compatibility.

= 1.3.3 =
* [Feature] IPv6 support has been added.
* [Feature] The last attempted username will now be shown in the Login Logs.
* [Bug Fix] If the login page had been renamed, and wp-login.php was accessed over HTTPS, the login screen was shown instead of 404 not found. This is now fixed.
* [Bug Fix] If the user had used a "/" in the rename login slug, the new slug would not work without the "/" in the URL. This is now fixed.
* [Bug Fix] The license key could get reset in some cases. This also caused plugin updates to fail. This is now fixed.
* [Bug Fix] Wild Cards “*” in the Username Auto Blacklist did not work. This is now fixed.
* [Bug Fix] The documentation in the plugin was pointing to a wrong link. This is now fixed.

= 1.3.2 =
* [Feature] Rename the wp-admin access URL is now possible with Loginizer
* [Feature] WooCommerce support has been improved for reCAPTCHA 
* [Feature] Loginizer will now show a Notification to the Enduser to setup the preferred 2FA settings
* [Feature] Added option to choose between REMOTE_ADDR, HTTP_CLIENT_IP and HTTP_X_FORWARDED for websites behind a proxy 
* [Task] Multiple reCAPTHCA on a single page is now supported
* [Task] Added a link to Google's reCAPTCHA website for easy access in our reCAPTCHA wizard

= 1.3.1 =
* [Feature] Admin's can now remove a user's Two Factor Authentication if needed
* [Feature] Adden an option to change the Admin Username
* [Feature] Auto Blacklist IPs if certain usernames saved by the Admin are used to login by malicious bots / users
* [Feature] The Login attempt logs will now be shown as per the last attempt TIME and in Descending Order
* [Feature] Added an option to Reset the Login attempts for all or specific IPs 

= 1.3.0 =
* [Feature] Added MD5 File Checksum feature. If any core files are changed, Loginizer will log the differences and notify the Admin
* [Feature] Added an option to make Email OTP as the default Two Factor Auth when a user has not set the OTP method of their choice
* [Feature] Added WooCommerce support for Captcha forms
* [Feature] Added pagination in the Brute Force Logs Wizard
* [Bug Fix] Disabling and Re-Enabling Loginizer caused an SQL error

= 1.2.0 =
* [Feature] Rename Login with Secrecy : If set, then all Login URL's will still point to wp-login.php and users will have to access the New Login Slug by typing it in the browser.
* [Task] The brute force logs will now be sorted as per the time of failed login attemps
* [Bug Fix] Dashboard showed wrong permissions if wp-content path had been changed
* [Bug Fix] Added Directory path to include files which caused issues with some plugins

= 1.1.1 =
* [Bug Fix] Added ABSPATH instead of get_home_path()

= 1.1.0 =
* [Feature] PasswordLess Login
* [Feature] Two Factor Auth - Email
* [Feature] Two Factor Auth - App
* [Feature] Login Challenge Question
* [Feature] reCAPTCHA
* [Feature] Rename Login Page
* [Feature] Disable XML-RPC
* [Feature] Rename XML-RPC
* [Feature] Disable Pingbacks
* [Feature] New Dashboard
* [Feature] System Information added in the new Dashboard
* [Feature] File Permissions added in the new Dashboard
* [Feature] New UI
* [Bug Fix] Fixed bug to add IP Range from 0.0.0.1 - 255.255.255.255
* [Bug Fix] Removed /e from preg_replace causing warnings in PHP

= 1.0.2 =

* Fixed Extended Lockout bug
* Fixed Lockout bug
* Handle login attempts via XML-RPC

= 1.0.1 =

* Database structure changes to make the plugin work faster
* Minor fixes

= 1.0 =

* Blocks IP after maximum retries allowed
* Extended Lockout after maximum lockouts allowed
* Email notification to admin after max lockouts
* Blacklist IP/IP range
* Whitelist IP/IP range
* Check logs of failed attempts
* Create IP ranges
* Delete IP ranges
* Licensed under LGPLv2.1
* Safe & Secure