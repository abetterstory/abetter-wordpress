# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Värd: localhost (MySQL 5.7.23)
# Databas: labs_abetter_start_test
# Genereringstid: 2018-10-29 12:17:20 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Tabelldump wp_commentmeta
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_commentmeta`;

CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;



# Tabelldump wp_comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_comments`;

CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT '0',
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;



# Tabelldump wp_links
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_links`;

CREATE TABLE `wp_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT '1',
  `link_rating` int(11) NOT NULL DEFAULT '0',
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;



# Tabelldump wp_options
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_options`;

CREATE TABLE `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;

INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`)
VALUES
	(1,'siteurl','http://test.app.loc/wp','yes'),
	(2,'home','http://test.app.loc/wp','yes'),
	(3,'blogname','TEST','yes'),
	(4,'blogdescription','','yes'),
	(5,'users_can_register','0','yes'),
	(6,'admin_email','johan@sjoland.com','yes'),
	(7,'start_of_week','1','yes'),
	(8,'use_balanceTags','0','yes'),
	(9,'use_smilies','1','yes'),
	(10,'require_name_email','','yes'),
	(11,'comments_notify','','yes'),
	(12,'posts_per_rss','10','yes'),
	(13,'rss_use_excerpt','0','yes'),
	(14,'mailserver_url','mail.example.com','yes'),
	(15,'mailserver_login','login@example.com','yes'),
	(16,'mailserver_pass','password','yes'),
	(17,'mailserver_port','110','yes'),
	(18,'default_category','1','yes'),
	(19,'default_comment_status','closed','yes'),
	(20,'default_ping_status','closed','yes'),
	(21,'default_pingback_flag','','yes'),
	(22,'posts_per_page','10','yes'),
	(23,'date_format','Y-m-d','yes'),
	(24,'time_format','H:i','yes'),
	(25,'links_updated_date_format','F j, Y g:i a','yes'),
	(26,'comment_moderation','','yes'),
	(27,'moderation_notify','','yes'),
	(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),
	(29,'rewrite_rules','a:90:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:27:\"comment-page-([0-9]{1,})/?$\";s:38:\"index.php?&page_id=2&cpage=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:58:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:68:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:88:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:64:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:53:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/embed/?$\";s:91:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/trackback/?$\";s:85:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&tb=1\";s:77:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:65:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/page/?([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&paged=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/comment-page-([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&cpage=$matches[5]\";s:61:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)(?:/([0-9]+))?/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&page=$matches[5]\";s:47:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:57:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:77:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:53:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cpage=$matches[4]\";s:51:\"([0-9]{4})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&cpage=$matches[3]\";s:38:\"([0-9]{4})/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&cpage=$matches[2]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";}','yes'),
	(30,'hack_file','0','yes'),
	(31,'blog_charset','UTF-8','yes'),
	(32,'moderation_keys','','no'),
	(33,'active_plugins','a:7:{i:0;s:34:\"advanced-custom-fields-pro/acf.php\";i:1;s:49:\"advanced-database-cleaner/advanced-db-cleaner.php\";i:2;s:33:\"duplicate-menu/duplicate-menu.php\";i:3;s:33:\"duplicate-post/duplicate-post.php\";i:4;s:45:\"enable-media-replace/enable-media-replace.php\";i:5;s:39:\"mce-table-buttons/mce_table_buttons.php\";i:6;s:49:\"wp-post-meta-revisions/wp-post-meta-revisions.php\";}','yes'),
	(34,'category_base','','yes'),
	(35,'ping_sites','http://rpc.pingomatic.com/','yes'),
	(36,'comment_max_links','2','yes'),
	(37,'gmt_offset','','yes'),
	(38,'default_email_category','1','yes'),
	(39,'recently_edited','','no'),
	(40,'template','abetter','yes'),
	(41,'stylesheet','abetter','yes'),
	(42,'comment_whitelist','','yes'),
	(43,'blacklist_keys','','no'),
	(44,'comment_registration','','yes'),
	(45,'html_type','text/html','yes'),
	(46,'use_trackback','0','yes'),
	(47,'default_role','subscriber','yes'),
	(48,'db_version','38590','yes'),
	(49,'uploads_use_yearmonth_folders','1','yes'),
	(50,'upload_path','','yes'),
	(51,'blog_public','0','yes'),
	(52,'default_link_category','2','yes'),
	(53,'show_on_front','page','yes'),
	(54,'tag_base','','yes'),
	(55,'show_avatars','1','yes'),
	(56,'avatar_rating','G','yes'),
	(57,'upload_url_path','','yes'),
	(58,'thumbnail_size_w','150','yes'),
	(59,'thumbnail_size_h','150','yes'),
	(60,'thumbnail_crop','1','yes'),
	(61,'medium_size_w','300','yes'),
	(62,'medium_size_h','300','yes'),
	(63,'avatar_default','mystery','yes'),
	(64,'large_size_w','1024','yes'),
	(65,'large_size_h','1024','yes'),
	(66,'image_default_link_type','none','yes'),
	(67,'image_default_size','','yes'),
	(68,'image_default_align','','yes'),
	(69,'close_comments_for_old_posts','','yes'),
	(70,'close_comments_days_old','14','yes'),
	(71,'thread_comments','','yes'),
	(72,'thread_comments_depth','5','yes'),
	(73,'page_comments','','yes'),
	(74,'comments_per_page','50','yes'),
	(75,'default_comments_page','newest','yes'),
	(76,'comment_order','asc','yes'),
	(77,'sticky_posts','a:0:{}','yes'),
	(78,'widget_categories','a:2:{i:2;a:4:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:12:\"hierarchical\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}','yes'),
	(79,'widget_text','a:0:{}','yes'),
	(80,'widget_rss','a:0:{}','yes'),
	(81,'uninstall_plugins','a:1:{s:49:\"advanced-database-cleaner/advanced-db-cleaner.php\";s:14:\"aDBc_uninstall\";}','no'),
	(82,'timezone_string','Europe/Stockholm','yes'),
	(83,'page_for_posts','7','yes'),
	(84,'page_on_front','2','yes'),
	(85,'default_post_format','0','yes'),
	(86,'link_manager_enabled','0','yes'),
	(87,'finished_splitting_shared_terms','1','yes'),
	(88,'site_icon','0','yes'),
	(89,'medium_large_size_w','768','yes'),
	(90,'medium_large_size_h','0','yes'),
	(91,'wp_page_for_privacy_policy','3','yes'),
	(92,'initial_db_version','38590','yes'),
	(93,'wp_user_roles','a:6:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:62:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:10:\"copy_posts\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:35:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:10:\"copy_posts\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}s:9:\"developer\";a:2:{s:4:\"name\";s:9:\"Developer\";s:12:\"capabilities\";a:62:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:10:\"copy_posts\";b:1;}}}','yes'),
	(94,'fresh_site','0','yes'),
	(95,'widget_search','a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}','yes'),
	(96,'widget_recent-posts','a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}','yes'),
	(97,'widget_recent-comments','a:2:{i:2;a:2:{s:5:\"title\";s:0:\"\";s:6:\"number\";i:5;}s:12:\"_multiwidget\";i:1;}','yes'),
	(98,'widget_archives','a:2:{i:2;a:3:{s:5:\"title\";s:0:\"\";s:5:\"count\";i:0;s:8:\"dropdown\";i:0;}s:12:\"_multiwidget\";i:1;}','yes'),
	(99,'widget_meta','a:2:{i:2;a:1:{s:5:\"title\";s:0:\"\";}s:12:\"_multiwidget\";i:1;}','yes'),
	(100,'sidebars_widgets','a:2:{s:19:\"wp_inactive_widgets\";a:6:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";i:3;s:10:\"archives-2\";i:4;s:12:\"categories-2\";i:5;s:6:\"meta-2\";}s:13:\"array_version\";i:3;}','yes'),
	(101,'widget_pages','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(102,'widget_calendar','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(103,'widget_media_audio','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(104,'widget_media_image','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(105,'widget_media_gallery','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(106,'widget_media_video','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(107,'widget_tag_cloud','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(108,'widget_nav_menu','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(109,'widget_custom_html','a:1:{s:12:\"_multiwidget\";i:1;}','yes'),
	(110,'cron','a:5:{i:1540816252;a:1:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}}i:1540845052;a:3:{s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1540888258;a:2:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1540888378;a:1:{s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}s:7:\"version\";i:2;}','yes'),
	(112,'_site_transient_update_core','O:8:\"stdClass\":4:{s:7:\"updates\";a:1:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:6:\"latest\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-4.9.8.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-4.9.8.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-4.9.8-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-4.9.8-new-bundled.zip\";s:7:\"partial\";b:0;s:8:\"rollback\";b:0;}s:7:\"current\";s:5:\"4.9.8\";s:7:\"version\";s:5:\"4.9.8\";s:11:\"php_version\";s:5:\"5.2.4\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"4.7\";s:15:\"partial_version\";s:0:\"\";}}s:12:\"last_checked\";i:1540811408;s:15:\"version_checked\";s:5:\"4.9.8\";s:12:\"translations\";a:0:{}}','no'),
	(117,'_site_transient_update_themes','O:8:\"stdClass\":4:{s:12:\"last_checked\";i:1540811410;s:7:\"checked\";a:1:{s:7:\"abetter\";s:5:\"1.0.0\";}s:8:\"response\";a:0:{}s:12:\"translations\";a:0:{}}','no'),
	(123,'can_compress_scripts','1','no'),
	(134,'theme_mods_twentyseventeen','a:1:{s:16:\"sidebars_widgets\";a:2:{s:4:\"time\";i:1528878663;s:4:\"data\";a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:6:{i:0;s:8:\"search-2\";i:1;s:14:\"recent-posts-2\";i:2;s:17:\"recent-comments-2\";i:3;s:10:\"archives-2\";i:4;s:12:\"categories-2\";i:5;s:6:\"meta-2\";}s:9:\"sidebar-2\";a:0:{}s:9:\"sidebar-3\";a:0:{}}}}','yes'),
	(135,'current_theme','Abetter Theme for Laravel','yes'),
	(136,'theme_mods_abetter','a:2:{i:0;b:0;s:18:\"nav_menu_locations\";a:0:{}}','yes'),
	(137,'theme_switched','','yes'),
	(140,'recently_activated','a:0:{}','yes'),
	(141,'acf_version','5.7.7','yes'),
	(142,'duplicate_post_copytitle','1','yes'),
	(143,'duplicate_post_copydate','0','yes'),
	(144,'duplicate_post_copystatus','0','yes'),
	(145,'duplicate_post_copyslug','0','yes'),
	(146,'duplicate_post_copyexcerpt','1','yes'),
	(147,'duplicate_post_copycontent','1','yes'),
	(148,'duplicate_post_copythumbnail','1','yes'),
	(149,'duplicate_post_copytemplate','1','yes'),
	(150,'duplicate_post_copyformat','1','yes'),
	(151,'duplicate_post_copyauthor','0','yes'),
	(152,'duplicate_post_copypassword','0','yes'),
	(153,'duplicate_post_copyattachments','0','yes'),
	(154,'duplicate_post_copychildren','0','yes'),
	(155,'duplicate_post_copycomments','0','yes'),
	(156,'duplicate_post_copymenuorder','1','yes'),
	(157,'duplicate_post_taxonomies_blacklist','a:0:{}','yes'),
	(158,'duplicate_post_blacklist','','yes'),
	(159,'duplicate_post_types_enabled','a:2:{i:0;s:4:\"post\";i:1;s:4:\"page\";}','yes'),
	(160,'duplicate_post_show_row','1','yes'),
	(161,'duplicate_post_show_adminbar','1','yes'),
	(162,'duplicate_post_show_submitbox','1','yes'),
	(163,'duplicate_post_show_bulkactions','1','yes'),
	(164,'duplicate_post_version','3.2.2','yes'),
	(165,'duplicate_post_show_notice','0','no'),
	(166,'emr_news','1','yes'),
	(167,'aDBc_settings','a:3:{s:9:\"left_menu\";s:1:\"1\";s:12:\"top_main_msg\";s:1:\"1\";s:22:\"tables_cleanup_warning\";s:1:\"1\";}','yes'),
	(173,'WPLANG','','yes'),
	(174,'new_admin_email','johan@sjoland.com','yes'),
	(175,'nonce_key','n4gs9C2X~i9HAuz+4:(#;a1?T$S/08.qvLY~f[9V%xe$!S%VIym2DGWN/dTvJjk#','no'),
	(176,'nonce_salt','3r)Brb`T-|{edq~e%2.0@Ij521[`7$2iIeakX@d-[Wbs[Emdn:J$n`8Oh)bfoHz`','no'),
	(178,'auth_key',']P=5$gsdp.Ef*w`d{_zaKmzd%S8+ROw?(A1XO/- =G[g!bv$ZVROv6h(%82FOGm*','no'),
	(179,'auth_salt','j6QI%Br<>^l58Z8J3Uh}C1^yuwLS8f7tW]q@l1oj;W;ijACq+&x05 =Z+{m%)E&=','no'),
	(180,'logged_in_key','i4Mh2^4/ux1N[;xPM2ohv3{$Er+]Ca1COKQ3jo<KB<V0i^_n}1mYxHTC&;!!{U/&','no'),
	(181,'logged_in_salt','~M5mF#<[ccH iG1H0|FOzpHs(D^rOsGw|xsidZZxi{8crn;B+nX2fK088_lg3<[U','no'),
	(199,'nav_menu_options','a:2:{i:0;b:0;s:8:\"auto_add\";a:0:{}}','yes'),
	(210,'_site_transient_timeout_community-events-d41d8cd98f00b204e9800998ecf8427e','1540855300','no'),
	(211,'_site_transient_community-events-d41d8cd98f00b204e9800998ecf8427e','a:2:{s:8:\"location\";a:1:{s:2:\"ip\";b:0;}s:6:\"events\";a:1:{i:0;a:7:{s:4:\"type\";s:8:\"wordcamp\";s:5:\"title\";s:15:\"WordCamp Europe\";s:3:\"url\";s:32:\"https://2019.europe.wordcamp.org\";s:6:\"meetup\";N;s:10:\"meetup_url\";N;s:4:\"date\";s:19:\"2019-06-21 00:00:00\";s:8:\"location\";a:4:{s:8:\"location\";s:15:\"WordCamp Europe\";s:7:\"country\";s:2:\"DE\";s:8:\"latitude\";d:52.473107;s:9:\"longitude\";d:13.4587819;}}}}','no'),
	(220,'_transient_timeout_dash_v2_88ae138922fe95674369b1cb3d215a2b','1540851294','no'),
	(221,'_transient_dash_v2_88ae138922fe95674369b1cb3d215a2b','<div class=\"rss-widget\"><ul><li><a class=\'rsswidget\' href=\'https://wordpress.org/news/2018/10/wordpress-5-0-beta-1/\'>WordPress 5.0 Beta 1</a></li></ul></div><div class=\"rss-widget\"><ul><li><a class=\'rsswidget\' href=\'https://wptavern.com/woocommerce-3-5-introduces-rest-api-v3-improves-transactional-emails\'>WPTavern: WooCommerce 3.5 Introduces REST API v3, Improves Transactional Emails</a></li><li><a class=\'rsswidget\' href=\'https://wptavern.com/wp-engine-acquires-array-themes\'>WPTavern: WP Engine Acquires Array Themes</a></li><li><a class=\'rsswidget\' href=\'https://pento.net/2018/10/26/iterating-on-merge-proposals/\'>Gary: Iterating on Merge Proposals</a></li></ul></div>','no'),
	(222,'_transient_timeout_plugin_slugs','1540895565','no'),
	(223,'_transient_plugin_slugs','a:15:{i:0;s:18:\"acfml/wpml-acf.php\";i:1;s:34:\"advanced-custom-fields-pro/acf.php\";i:2;s:49:\"advanced-database-cleaner/advanced-db-cleaner.php\";i:3;s:33:\"duplicate-menu/duplicate-menu.php\";i:4;s:33:\"duplicate-post/duplicate-post.php\";i:5;s:45:\"enable-media-replace/enable-media-replace.php\";i:6;s:39:\"mce-table-buttons/mce_table_buttons.php\";i:7;s:49:\"wp-post-meta-revisions/wp-post-meta-revisions.php\";i:8;s:20:\"realguids/plugin.php\";i:9;s:59:\"simple-post-type-permalinks/simple-post-type-permalinks.php\";i:10;s:40:\"sitepress-multilingual-cms/sitepress.php\";i:11;s:34:\"wpml-string-translation/plugin.php\";i:12;s:38:\"wpml-translation-management/plugin.php\";i:13;s:25:\"wp-sync-db/wp-sync-db.php\";i:14;s:49:\"wp-sync-db-media-files/wp-sync-db-media-files.php\";}','no'),
	(230,'_transient_timeout_acf_plugin_info_pro','1540812656','no'),
	(231,'_transient_acf_plugin_info_pro','a:17:{s:4:\"name\";s:26:\"Advanced Custom Fields PRO\";s:4:\"slug\";s:26:\"advanced-custom-fields-pro\";s:8:\"homepage\";s:37:\"https://www.advancedcustomfields.com/\";s:7:\"version\";s:5:\"5.7.7\";s:6:\"author\";s:13:\"Elliot Condon\";s:10:\"author_url\";s:28:\"http://www.elliotcondon.com/\";s:12:\"contributors\";s:12:\"elliotcondon\";s:8:\"requires\";s:5:\"4.4.0\";s:6:\"tested\";s:5:\"4.9.9\";s:4:\"tags\";a:86:{i:0;s:11:\"5.8.0-beta2\";i:1;s:11:\"5.8.0-beta1\";i:2;s:5:\"5.7.6\";i:3;s:5:\"5.7.5\";i:4;s:5:\"5.7.4\";i:5;s:5:\"5.7.3\";i:6;s:5:\"5.7.2\";i:7;s:5:\"5.7.1\";i:8;s:5:\"5.7.0\";i:9;s:5:\"5.6.9\";i:10;s:5:\"5.6.8\";i:11;s:5:\"5.6.7\";i:12;s:5:\"5.6.6\";i:13;s:5:\"5.6.5\";i:14;s:5:\"5.6.4\";i:15;s:5:\"5.6.3\";i:16;s:5:\"5.6.2\";i:17;s:6:\"5.6.10\";i:18;s:5:\"5.6.1\";i:19;s:11:\"5.6.0-beta2\";i:20;s:11:\"5.6.0-beta1\";i:21;s:9:\"5.6.0-RC2\";i:22;s:9:\"5.6.0-RC1\";i:23;s:5:\"5.6.0\";i:24;s:5:\"5.5.9\";i:25;s:5:\"5.5.7\";i:26;s:5:\"5.5.5\";i:27;s:5:\"5.5.3\";i:28;s:5:\"5.5.2\";i:29;s:6:\"5.5.14\";i:30;s:6:\"5.5.13\";i:31;s:6:\"5.5.12\";i:32;s:6:\"5.5.11\";i:33;s:6:\"5.5.10\";i:34;s:5:\"5.5.1\";i:35;s:5:\"5.5.0\";i:36;s:5:\"5.4.8\";i:37;s:5:\"5.4.7\";i:38;s:5:\"5.4.6\";i:39;s:5:\"5.4.5\";i:40;s:5:\"5.4.4\";i:41;s:5:\"5.4.3\";i:42;s:5:\"5.4.2\";i:43;s:5:\"5.4.1\";i:44;s:5:\"5.4.0\";i:45;s:5:\"5.3.9\";i:46;s:5:\"5.3.8\";i:47;s:5:\"5.3.7\";i:48;s:5:\"5.3.6\";i:49;s:5:\"5.3.5\";i:50;s:5:\"5.3.4\";i:51;s:5:\"5.3.3\";i:52;s:5:\"5.3.2\";i:53;s:6:\"5.3.10\";i:54;s:5:\"5.3.1\";i:55;s:5:\"5.3.0\";i:56;s:5:\"5.2.9\";i:57;s:5:\"5.2.8\";i:58;s:5:\"5.2.7\";i:59;s:5:\"5.2.6\";i:60;s:5:\"5.2.5\";i:61;s:5:\"5.2.4\";i:62;s:5:\"5.2.3\";i:63;s:5:\"5.2.2\";i:64;s:5:\"5.2.1\";i:65;s:5:\"5.2.0\";i:66;s:5:\"5.1.9\";i:67;s:5:\"5.1.8\";i:68;s:5:\"5.1.7\";i:69;s:5:\"5.1.6\";i:70;s:5:\"5.1.5\";i:71;s:5:\"5.1.4\";i:72;s:5:\"5.1.3\";i:73;s:5:\"5.1.2\";i:74;s:5:\"5.1.1\";i:75;s:5:\"5.1.0\";i:76;s:5:\"5.0.9\";i:77;s:5:\"5.0.8\";i:78;s:5:\"5.0.7\";i:79;s:5:\"5.0.6\";i:80;s:5:\"5.0.5\";i:81;s:5:\"5.0.4\";i:82;s:5:\"5.0.3\";i:83;s:5:\"5.0.2\";i:84;s:5:\"5.0.1\";i:85;s:5:\"5.0.0\";}s:6:\"tagged\";s:61:\"acf, advanced, custom, field, fields, form, repeater, content\";s:11:\"description\";s:1353:\"<p>Use the Advanced Custom Fields plugin to take full control of your WordPress edit screens & custom field data.</p>\n<p><strong>Add fields on demand.</strong> Our field builder allows you to quickly and easily add fields to WP edit screens with only the click of a few buttons!</p>\n<p><strong>Add them anywhere.</strong> Fields can be added all over WP including posts, users, taxonomy terms, media, comments and even custom options pages!</p>\n<p><strong>Show them everywhere.</strong> Load and display your custom field values in any theme template file with our hassle free developer friendly functions!</p>\n<h4>Features</h4>\n<ul>\n<li> Simple & Intuitive</li>\n<li> Powerful Functions</li>\n<li> Over 30 Field Types</li>\n<li> Extensive Documentation</li>\n<li> Millions of Users</li>\n</ul>\n<h4>Links</h4>\n<ul>\n<li> <a href=\"https://www.advancedcustomfields.com\">Website</a></li>\n<li> <a href=\"https://www.advancedcustomfields.com/resources/\">Documentation</a></li>\n<li> <a href=\"https://support.advancedcustomfields.com\">Support</a></li>\n<li> <a href=\"https://www.advancedcustomfields.com/pro/\">ACF PRO</a></li>\n</ul>\n<h4>PRO</h4>\n<p>The Advanced Custom Fields plugin is also available in a professional version which includes more fields, more functionality, and more flexibility! <a href=\"https://www.advancedcustomfields.com/pro/\">Learn more</a></p>\n\";s:12:\"installation\";s:508:\"<p>From your WordPress dashboard</p>\n<ol>\n<li> <strong>Visit</strong> Plugins > Add New</li>\n<li> <strong>Search</strong> for \"Advanced Custom Fields\"</li>\n<li> <strong>Activate</strong> Advanced Custom Fields from your Plugins page</li>\n<li> <strong>Click</strong> on the new menu item \"Custom Fields\" and create your first Custom Field Group!</li>\n<li> <strong>Read</strong> the documentation to <a href=\"https://www.advancedcustomfields.com/resources/getting-started-with-acf/\">get started</a></li>\n</ol>\n\";s:9:\"changelog\";s:5329:\"<h4>5.7.7</h4>\n<p><em>Release Date - 1 October 2018</em></p>\n<ul>\n<li> Fix - Fixed various plugin update issues.</li>\n<li> Tweak - Added \'language\' to Google Maps API url.</li>\n<li> Dev - Major improvements to the <code>acf.models.Postbox</code> model.</li>\n<li> Dev - Added JS filter \'check_screen_args\'.</li>\n<li> Dev - Added JS action \'check_screen_complete\'.</li>\n<li> Dev - Added action \'acf/options_page/submitbox_before_major_actions\'.</li>\n<li> Dev - Added action \'acf/options_page/submitbox_major_actions\'.</li>\n<li> i18n - Updated Portuguese language thanks to Pedro Mendonça</li>\n</ul>\n<h4>5.7.6</h4>\n<p><em>Release Date - 12 September 2018</em></p>\n<ul>\n<li> Fix - Fixed unload prompt not working.</li>\n<li> Dev - Reduced number of queries needed to populate the relationship field taxonomy filter.</li>\n<li> Dev - Added \'nav_menu_item_id\' and \'nav_menu_item_depth\' to get_field_groups() query.</li>\n<li> Dev - Reordered various actions and filters for more usefulness.</li>\n<li> i18n - Updated Polish language thanks to Dariusz Zielonka</li>\n</ul>\n<h4>5.7.5</h4>\n<p><em>Release Date - 6 September 2018</em></p>\n<ul>\n<li> Fix - Fixed bug causing multisite login redirect issues.</li>\n<li> Fix - Fixed bug causing validation issues in older versions of Firefox.</li>\n<li> Fix - Fixed bug causing duplicate Select2 instances when adding a widget via drag/drop.</li>\n<li> Dev - Improved WPML compatibility by using <code>$sitepress->get_current_language()</code> instead of <code>ICL_LANGUAGE_CODE</code>.</li>\n<li> Dev - Improved validation JS with new Validator model and logic.</li>\n</ul>\n<h4>5.7.4</h4>\n<p><em>Release Date - 30 August 2018</em></p>\n<ul>\n<li> Fix - Fixed bug causing field groups to disappear when selecting a taxonomy term with WPML active.</li>\n<li> Tweak - Added more Dark Mode styles.</li>\n<li> Tweak - Improved DB upgrade prompt, functions and AJAX logic.</li>\n<li> Tweak - Improved the \"What\'s New\" admin page seen after DB Upgrade.</li>\n<li> Dev - Added new location rules filters.</li>\n</ul>\n<h4>5.7.3</h4>\n<p><em>Release Date - 20 August 2018</em></p>\n<ul>\n<li> New - Added Dark Mode styles for the <a href=\"https://en-au.wordpress.org/plugins/dark-mode/\">Dark Mode Plugin</a>.</li>\n<li> New - Added \"Value Contains\" condition to the Select field type.</li>\n<li> New - Added support for the WooCommerce product type dropdown to trigger \"update metaboxes\".</li>\n<li> Tweak - Improved acf.screen model responsible for \"updating metaboxes\" when changing post data.</li>\n<li> Tweak - Removed user fields from the multisite \"Add New User\" page. </li>\n<li> Fix - Fixed bug preventing some tinymce customizations from working.</li>\n<li> Fix - Fixed JS bug throwing \"preference\" error in console.</li>\n<li> Dev - Added action \'acf/enqueue_uploader\' triggered after the hidden \"ACF Content\" editor is rendered.</li>\n</ul>\n<h4>5.7.2</h4>\n<p><em>Release Date - 6 August 2018</em></p>\n<ul>\n<li> Fix - Fixed bug preventing the Google Maps Field address from being customized.</li>\n<li> Fix - Improved logic to request and cache plugin update information.</li>\n<li> Fix - Fixed bug preventing JS initialization when editing widgets in accessibility mode.</li>\n<li> Fix - Added missing $parent argument to term_exists() function when adding a new term via taxonomy field popup.</li>\n<li> Fix - Fixed bug where nested Group Fields did not delete their values.</li>\n<li> Fix - Fixed JS error thrown by localStorage if cookies are not enabled.</li>\n<li> Dev - Bumped minimum WP version requirement to 4.4.</li>\n<li> Dev - Added action \'wp_nav_menu_item_custom_fields\' for compatibility with other plugins modifying the menu walker class.</li>\n<li> Dev - Added \'multiple\' to the allowed attributes for an email field.</li>\n<li> Dev - Added new ACF_Ajax class for upcoming features.</li>\n</ul>\n<h4>5.7.1</h4>\n<ul>\n<li> Core: Minor fixes and improvements</li>\n</ul>\n<h4>5.7.0</h4>\n<ul>\n<li> Core: Major JavaScript updates</li>\n<li> Core: Improved conditional logic with new types and more supported fields</li>\n<li> Core: Improved localization and internationalization</li>\n<li> Repeater field: Improved logic that remembers collapsed row states</li>\n<li> Repeater field: Added support to collapse multiple rows (hold shift)</li>\n<li> API: Improved lookup to find fields without a reference value</li>\n<li> Language: Added Croatian translation - Thanks to Vlado Bosnjak</li>\n<li> Language: Updated Italian translation - thanks to Davide Pantè</li>\n<li> Language: Updated Romanian translation - thanks to Ionut Staicu</li>\n<li> Language: Updated German translation - thanks to Ralf Koller</li>\n<li> Language: Updated Arabic translation - thanks to Karim Ramadan</li>\n<li> Language: Updated Portuguese translation - thanks to Pedro Mendonça</li>\n</ul>\n<h4>5.6.10</h4>\n<ul>\n<li> Core: Minor fixes and improvements</li>\n</ul>\n<h4>5.6.9</h4>\n<ul>\n<li> User field: Added new \'Return Format\' setting (Array, Object, ID)</li>\n<li> Core: Added basic compatibility with Gutenberg - values now save</li>\n<li> Core: Fixed bug affecting the loading of fields on new Menu Items</li>\n<li> Core: Removed private (\'show_ui\' => false) post types from the \'Post Type\' location rule choices</li>\n<li> Core: Minor fixes and improvements</li>\n<li> Language: Updated French translation - thanks to Maxime Bernard-Jacquet</li>\n</ul>\n\";s:14:\"upgrade_notice\";s:505:\"<h4>5.2.7</h4>\n<ul>\n<li> Field class names have changed slightly in v5.2.7 from <code>field_type-{$type}</code> to <code>acf-field-{$type}</code>. This change was introduced to better optimize JS performance. The previous class names can be added back in with the following filter: https://www.advancedcustomfields.com/resources/acfcompatibility/</li>\n</ul>\n<h4>3.0.0</h4>\n<ul>\n<li> Editor is broken in WordPress 3.3</li>\n</ul>\n<h4>2.1.4</h4>\n<ul>\n<li> Adds post_id column back into acf_values</li>\n</ul>\n\";s:5:\"icons\";a:1:{s:7:\"default\";s:63:\"https://ps.w.org/advanced-custom-fields/assets/icon-256x256.png\";}s:7:\"banners\";a:1:{s:7:\"default\";s:66:\"https://ps.w.org/advanced-custom-fields/assets/banner-1544x500.jpg\";}}','no'),
	(232,'acf_pro_license','YToyOntzOjM6ImtleSI7czo3MjoiYjNKa1pYSmZhV1E5T1RBMU5ETjhkSGx3WlQxa1pYWmxiRzl3WlhKOFpHRjBaVDB5TURFMkxUQTVMVEk0SURBM09qSXhPakU0IjtzOjM6InVybCI7czoyNjoiaHR0cDovL3Rlc3QuYWJldHRlci5sb2Mvd3AiO30=','yes'),
	(233,'_site_transient_update_plugins','O:8:\"stdClass\":4:{s:12:\"last_checked\";i:1540811408;s:8:\"response\";a:0:{}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:7:{s:49:\"advanced-database-cleaner/advanced-db-cleaner.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:39:\"w.org/plugins/advanced-database-cleaner\";s:4:\"slug\";s:25:\"advanced-database-cleaner\";s:6:\"plugin\";s:49:\"advanced-database-cleaner/advanced-db-cleaner.php\";s:11:\"new_version\";s:5:\"2.0.0\";s:3:\"url\";s:56:\"https://wordpress.org/plugins/advanced-database-cleaner/\";s:7:\"package\";s:74:\"https://downloads.wordpress.org/plugin/advanced-database-cleaner.2.0.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:78:\"https://ps.w.org/advanced-database-cleaner/assets/icon-256x256.png?rev=1306117\";s:2:\"1x\";s:78:\"https://ps.w.org/advanced-database-cleaner/assets/icon-128x128.png?rev=1306117\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:80:\"https://ps.w.org/advanced-database-cleaner/assets/banner-772x250.png?rev=1630620\";}s:11:\"banners_rtl\";a:0:{}}s:33:\"duplicate-menu/duplicate-menu.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:28:\"w.org/plugins/duplicate-menu\";s:4:\"slug\";s:14:\"duplicate-menu\";s:6:\"plugin\";s:33:\"duplicate-menu/duplicate-menu.php\";s:11:\"new_version\";s:5:\"0.2.1\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/duplicate-menu/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/duplicate-menu.0.2.1.zip\";s:5:\"icons\";a:1:{s:7:\"default\";s:58:\"https://s.w.org/plugins/geopattern-icon/duplicate-menu.svg\";}s:7:\"banners\";a:0:{}s:11:\"banners_rtl\";a:0:{}}s:33:\"duplicate-post/duplicate-post.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:28:\"w.org/plugins/duplicate-post\";s:4:\"slug\";s:14:\"duplicate-post\";s:6:\"plugin\";s:33:\"duplicate-post/duplicate-post.php\";s:11:\"new_version\";s:5:\"3.2.2\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/duplicate-post/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/duplicate-post.3.2.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/duplicate-post/assets/icon-256x256.png?rev=1612753\";s:2:\"1x\";s:67:\"https://ps.w.org/duplicate-post/assets/icon-128x128.png?rev=1612753\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:69:\"https://ps.w.org/duplicate-post/assets/banner-772x250.png?rev=1612986\";}s:11:\"banners_rtl\";a:0:{}}s:45:\"enable-media-replace/enable-media-replace.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:34:\"w.org/plugins/enable-media-replace\";s:4:\"slug\";s:20:\"enable-media-replace\";s:6:\"plugin\";s:45:\"enable-media-replace/enable-media-replace.php\";s:11:\"new_version\";s:5:\"3.2.7\";s:3:\"url\";s:51:\"https://wordpress.org/plugins/enable-media-replace/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/enable-media-replace.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/enable-media-replace/assets/icon-256x256.png?rev=1940728\";s:2:\"1x\";s:73:\"https://ps.w.org/enable-media-replace/assets/icon-128x128.png?rev=1940728\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:76:\"https://ps.w.org/enable-media-replace/assets/banner-1544x500.png?rev=1940728\";s:2:\"1x\";s:75:\"https://ps.w.org/enable-media-replace/assets/banner-772x250.png?rev=1940728\";}s:11:\"banners_rtl\";a:0:{}}s:39:\"mce-table-buttons/mce_table_buttons.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:31:\"w.org/plugins/mce-table-buttons\";s:4:\"slug\";s:17:\"mce-table-buttons\";s:6:\"plugin\";s:39:\"mce-table-buttons/mce_table_buttons.php\";s:11:\"new_version\";s:3:\"3.3\";s:3:\"url\";s:48:\"https://wordpress.org/plugins/mce-table-buttons/\";s:7:\"package\";s:64:\"https://downloads.wordpress.org/plugin/mce-table-buttons.3.3.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/mce-table-buttons/assets/icon-256x256.png?rev=971854\";s:2:\"1x\";s:69:\"https://ps.w.org/mce-table-buttons/assets/icon-128x128.png?rev=971854\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:72:\"https://ps.w.org/mce-table-buttons/assets/banner-1544x500.png?rev=971854\";s:2:\"1x\";s:71:\"https://ps.w.org/mce-table-buttons/assets/banner-772x250.png?rev=971854\";}s:11:\"banners_rtl\";a:0:{}}s:49:\"wp-post-meta-revisions/wp-post-meta-revisions.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:36:\"w.org/plugins/wp-post-meta-revisions\";s:4:\"slug\";s:22:\"wp-post-meta-revisions\";s:6:\"plugin\";s:49:\"wp-post-meta-revisions/wp-post-meta-revisions.php\";s:11:\"new_version\";s:5:\"1.0.0\";s:3:\"url\";s:53:\"https://wordpress.org/plugins/wp-post-meta-revisions/\";s:7:\"package\";s:71:\"https://downloads.wordpress.org/plugin/wp-post-meta-revisions.1.0.0.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:75:\"https://ps.w.org/wp-post-meta-revisions/assets/icon-256x256.png?rev=1038294\";s:2:\"1x\";s:75:\"https://ps.w.org/wp-post-meta-revisions/assets/icon-128x128.png?rev=1038294\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:77:\"https://ps.w.org/wp-post-meta-revisions/assets/banner-772x250.jpg?rev=1038298\";}s:11:\"banners_rtl\";a:0:{}}s:59:\"simple-post-type-permalinks/simple-post-type-permalinks.php\";O:8:\"stdClass\":9:{s:2:\"id\";s:41:\"w.org/plugins/simple-post-type-permalinks\";s:4:\"slug\";s:27:\"simple-post-type-permalinks\";s:6:\"plugin\";s:59:\"simple-post-type-permalinks/simple-post-type-permalinks.php\";s:11:\"new_version\";s:5:\"2.0.2\";s:3:\"url\";s:58:\"https://wordpress.org/plugins/simple-post-type-permalinks/\";s:7:\"package\";s:76:\"https://downloads.wordpress.org/plugin/simple-post-type-permalinks.2.0.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:80:\"https://ps.w.org/simple-post-type-permalinks/assets/icon-256x256.png?rev=1126124\";s:2:\"1x\";s:80:\"https://ps.w.org/simple-post-type-permalinks/assets/icon-128x128.png?rev=1126124\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:83:\"https://ps.w.org/simple-post-type-permalinks/assets/banner-1544x500.png?rev=1126124\";s:2:\"1x\";s:82:\"https://ps.w.org/simple-post-type-permalinks/assets/banner-772x250.png?rev=1126124\";}s:11:\"banners_rtl\";a:0:{}}}}','no'),
	(234,'_transient_timeout_acf_plugin_updates','1540895564','no'),
	(235,'_transient_acf_plugin_updates','a:4:{s:7:\"plugins\";a:0:{}s:10:\"expiration\";i:86400;s:6:\"status\";i:1;s:7:\"checked\";a:1:{s:34:\"advanced-custom-fields-pro/acf.php\";s:5:\"5.7.7\";}}','no'),
	(239,'_site_transient_timeout_theme_roots','1540813209','no'),
	(240,'_site_transient_theme_roots','a:1:{s:7:\"abetter\";s:7:\"/themes\";}','no');

/*!40000 ALTER TABLE `wp_options` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_postmeta
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_postmeta`;

CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_postmeta` WRITE;
/*!40000 ALTER TABLE `wp_postmeta` DISABLE KEYS */;

INSERT INTO `wp_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`)
VALUES
	(1,2,'_wp_page_template','default'),
	(2,3,'_wp_page_template','default'),
	(7,2,'_edit_lock','1528878880:1'),
	(8,2,'_edit_last','1'),
	(9,7,'_edit_last','1'),
	(10,7,'_edit_lock','1528878756:1'),
	(11,9,'_edit_last','1'),
	(12,9,'_edit_lock','1528878837:1'),
	(13,11,'_edit_last','1'),
	(14,11,'_edit_lock','1528878832:1'),
	(15,13,'_edit_last','1'),
	(16,13,'_edit_lock','1528878868:1'),
	(17,3,'_edit_lock','1528878874:1'),
	(18,3,'_edit_last','1'),
	(19,15,'_menu_item_type','post_type'),
	(20,15,'_menu_item_menu_item_parent','0'),
	(21,15,'_menu_item_object_id','7'),
	(22,15,'_menu_item_object','page'),
	(23,15,'_menu_item_target',''),
	(24,15,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),
	(25,15,'_menu_item_xfn',''),
	(26,15,'_menu_item_url',''),
	(28,16,'_menu_item_type','post_type'),
	(29,16,'_menu_item_menu_item_parent','0'),
	(30,16,'_menu_item_object_id','2'),
	(31,16,'_menu_item_object','page'),
	(32,16,'_menu_item_target',''),
	(33,16,'_menu_item_classes','a:1:{i:0;s:8:\"is-front\";}'),
	(34,16,'_menu_item_xfn',''),
	(35,16,'_menu_item_url',''),
	(36,17,'_edit_last','1'),
	(37,17,'_wp_page_template','default'),
	(38,17,'_edit_lock','1540808062:1'),
	(39,19,'_menu_item_type','post_type'),
	(40,19,'_menu_item_menu_item_parent','0'),
	(41,19,'_menu_item_object_id','17'),
	(42,19,'_menu_item_object','page'),
	(43,19,'_menu_item_target',''),
	(44,19,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),
	(45,19,'_menu_item_xfn',''),
	(46,19,'_menu_item_url',''),
	(47,20,'_edit_lock','1540812142:1'),
	(48,41,'_edit_lock','1540807998:1'),
	(49,55,'_edit_lock','1540808008:1'),
	(50,20,'_edit_last','1'),
	(51,58,'_edit_last','1'),
	(52,58,'_edit_lock','1540812320:1'),
	(53,58,'_wp_page_template','default'),
	(54,58,'cover_type',''),
	(55,58,'_cover_type','field_5ae1a84c901c7'),
	(56,58,'cover_label',''),
	(57,58,'_cover_label','field_5923ed45ebead'),
	(58,58,'cover_headline',''),
	(59,58,'_cover_headline','field_5ae1a803901c5'),
	(60,58,'cover_lead',''),
	(61,58,'_cover_lead','field_5ae1a81b901c6'),
	(62,58,'cover_image',''),
	(63,58,'_cover_image','field_5923ead4acbc7'),
	(64,58,'cover_image_style',''),
	(65,58,'_cover_image_style','field_5ba902fb451f8'),
	(66,58,'cover_caption',''),
	(67,58,'_cover_caption','field_5923edcd4d475'),
	(68,58,'cover_link',''),
	(69,58,'_cover_link','field_5ae1a8b8901c8'),
	(70,58,'cover_url',''),
	(71,58,'_cover_url','field_5ae1a8c8901c9'),
	(72,58,'cover_icon',''),
	(73,58,'_cover_icon','field_5923eac3acbc6'),
	(74,58,'cover_style',''),
	(75,58,'_cover_style','field_5923eb15acbc8'),
	(76,58,'breadcrumbs_type',''),
	(77,58,'_breadcrumbs_type','field_5b49e125f50fa'),
	(78,58,'breadcrumbs_style',''),
	(79,58,'_breadcrumbs_style','field_5b49e1f03e289'),
	(80,58,'seo_title',''),
	(81,58,'_seo_title','field_588f42ed28220'),
	(82,58,'seo_description',''),
	(83,58,'_seo_description','field_588f42f828221'),
	(84,58,'seo_keywords',''),
	(85,58,'_seo_keywords','field_59109efed9b93'),
	(86,58,'seo_type',''),
	(87,58,'_seo_type','field_588f430128222'),
	(88,58,'seo_image',''),
	(89,58,'_seo_image','field_588f431f28224'),
	(90,58,'seo_url',''),
	(91,58,'_seo_url','field_588f430828223'),
	(92,58,'seo_author',''),
	(93,58,'_seo_author','field_5baa50441f8bd'),
	(94,58,'seo_robots',''),
	(95,58,'_seo_robots','field_5baa50521f8be'),
	(96,58,'menu_label',''),
	(97,58,'_menu_label','field_5927141d5dafe'),
	(98,58,'menu_description',''),
	(99,58,'_menu_description','field_5a6f27868306e'),
	(100,58,'menu_icon',''),
	(101,58,'_menu_icon','field_592714245daff'),
	(102,58,'menu_style',''),
	(103,58,'_menu_style','field_5927142c5db00'),
	(104,58,'settings_expire',''),
	(105,58,'_settings_expire','field_5a70481ec73e7'),
	(106,58,'settings_redirect',''),
	(107,58,'_settings_redirect','field_5a70482bc73e8'),
	(108,58,'teaser_label',''),
	(109,58,'_teaser_label','field_5b7d207d2d624'),
	(110,58,'teaser_headline',''),
	(111,58,'_teaser_headline','field_5b7d20522d622'),
	(112,58,'teaser_excerpt',''),
	(113,58,'_teaser_excerpt','field_5b7d206e2d623'),
	(114,58,'teaser_image',''),
	(115,58,'_teaser_image','field_5b7d22b6bec6a'),
	(116,58,'teaser_link',''),
	(117,58,'_teaser_link','field_5b7d20932d625'),
	(246,61,'_menu_item_type','post_type'),
	(247,61,'_menu_item_menu_item_parent','0'),
	(248,61,'_menu_item_object_id','58'),
	(249,61,'_menu_item_object','page'),
	(250,61,'_menu_item_target',''),
	(251,61,'_menu_item_classes','a:1:{i:0;s:0:\"\";}'),
	(252,61,'_menu_item_xfn',''),
	(253,61,'_menu_item_url','');

/*!40000 ALTER TABLE `wp_postmeta` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_posts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_posts`;

CREATE TABLE `wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_posts` WRITE;
/*!40000 ALTER TABLE `wp_posts` DISABLE KEYS */;

INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`)
VALUES
	(2,1,'2018-06-13 08:30:52','2018-06-13 08:30:52','','Start','','publish','closed','closed','','start','','','2018-06-13 08:34:40','2018-06-13 08:34:40','',0,'http://test.app.loc/wp/?page_id=2',1,'page','',0),
	(3,1,'2018-06-13 08:30:52','2018-06-13 08:30:52','','Privacy Policy','','publish','closed','closed','','privacy-policy','','','2018-06-13 08:34:34','2018-06-13 08:34:34','',0,'http://test.app.loc/wp/?page_id=3',200,'page','',0),
	(7,1,'2018-06-13 08:33:02','2018-06-13 08:33:02','','News','','publish','closed','closed','','news','','','2018-06-13 08:34:58','2018-06-13 08:34:58','',0,'http://test.app.loc/?page_id=7',200,'page','',0),
	(9,1,'2018-06-13 08:33:21','2018-06-13 08:33:21','','404 Not Found','','publish','closed','closed','','404-not-found','','','2018-06-13 08:33:57','2018-06-13 08:33:57','',0,'http://test.app.loc/?page_id=9',404,'page','',0),
	(11,1,'2018-06-13 08:33:31','2018-06-13 08:33:31','','403 Forbidden','','publish','closed','closed','','403-forbidden','','','2018-06-13 08:33:52','2018-06-13 08:33:52','',0,'http://test.app.loc/?page_id=11',403,'page','',0),
	(13,1,'2018-06-13 08:33:41','2018-06-13 08:33:41','','Search','','publish','closed','closed','','search','','','2018-06-13 08:34:28','2018-06-13 08:34:28','',0,'http://test.app.loc/?page_id=13',400,'page','',0),
	(15,1,'2018-10-25 18:00:57','2018-10-25 16:00:57',' ','','','publish','closed','closed','','15','','','2018-10-29 12:28:07','2018-10-29 11:28:07','',0,'http://test.abetter.loc/?p=15',4,'nav_menu_item','',0),
	(16,1,'2018-10-25 18:00:57','2018-10-25 16:00:57',' ','','','publish','closed','closed','','16','','','2018-10-29 12:28:07','2018-10-29 11:28:07','',0,'http://test.abetter.loc/?p=16',1,'nav_menu_item','',0),
	(17,1,'2018-10-26 00:23:30','2018-10-25 22:23:30','','About','','publish','closed','closed','','about','','','2018-10-26 00:24:03','2018-10-25 22:24:03','',0,'http://test.abetter.loc/?page_id=17',100,'page','',0),
	(19,1,'2018-10-26 00:24:43','2018-10-25 22:24:43',' ','','','publish','closed','closed','','19','','','2018-10-29 12:28:07','2018-10-29 11:28:07','',0,'http://test.abetter.loc/?p=19',3,'nav_menu_item','',0),
	(20,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:7:{s:8:\"location\";a:2:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"!=\";s:5:\"value\";s:10:\"dictionary\";}}i:1;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:4:\"post\";}}}s:8:\"position\";s:15:\"acf_after_title\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:4:\"left\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";}','Cover','cover','publish','closed','closed','','group_5923ea9cd5f21','','','2018-10-29 12:24:31','2018-10-29 11:24:31','',0,'http://test.abetter.loc/?post_type=acf-field-group&#038;p=20',-100,'acf-field-group','',0),
	(21,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:12:{s:4:\"type\";s:8:\"checkbox\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:5:{s:11:\"cover-small\";s:5:\"Small\";s:11:\"cover-large\";s:5:\"Large\";s:16:\"cover-fullscreen\";s:10:\"Fullscreen\";s:11:\"cover-video\";s:5:\"Video\";s:12:\"cover-hidden\";s:6:\"Hidden\";}s:12:\"allow_custom\";i:0;s:13:\"default_value\";a:0:{}s:6:\"layout\";s:10:\"horizontal\";s:6:\"toggle\";i:0;s:13:\"return_format\";s:5:\"value\";s:11:\"save_custom\";i:0;}','Type','cover_type','publish','closed','closed','','field_5ae1a84c901c7','','','2018-10-29 12:24:31','2018-10-29 11:24:31','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=21',0,'acf-field','',0),
	(22,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Label','cover_label','publish','closed','closed','','field_5923ed45ebead','','','2018-10-29 12:23:07','2018-10-29 11:23:07','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=22',1,'acf-field','',0),
	(23,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Headline','cover_headline','publish','closed','closed','','field_5ae1a803901c5','','','2018-10-29 12:23:07','2018-10-29 11:23:07','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=23',2,'acf-field','',0),
	(24,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:8:\"textarea\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:4:\"rows\";i:2;s:9:\"new_lines\";s:0:\"\";}','Lead','cover_lead','publish','closed','closed','','field_5ae1a81b901c6','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=24',3,'acf-field','',0),
	(25,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:15:{s:4:\"type\";s:5:\"image\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"return_format\";s:3:\"url\";s:12:\"preview_size\";s:4:\"full\";s:7:\"library\";s:3:\"all\";s:9:\"min_width\";s:0:\"\";s:10:\"min_height\";s:0:\"\";s:8:\"min_size\";s:0:\"\";s:9:\"max_width\";s:0:\"\";s:10:\"max_height\";s:0:\"\";s:8:\"max_size\";s:0:\"\";s:10:\"mime_types\";s:0:\"\";}','Image','cover_image','publish','closed','closed','','field_5923ead4acbc7','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=25',4,'acf-field','',0),
	(26,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:13:{s:4:\"type\";s:6:\"select\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:34:{s:9:\"align-top\";s:9:\"align-top\";s:12:\"align-bottom\";s:12:\"align-bottom\";s:10:\"align-left\";s:10:\"align-left\";s:11:\"align-right\";s:11:\"align-right\";s:14:\"align-top-left\";s:14:\"align-top-left\";s:15:\"align-top-right\";s:15:\"align-top-right\";s:17:\"align-bottom-left\";s:17:\"align-bottom-left\";s:18:\"align-bottom-right\";s:18:\"align-bottom-right\";s:9:\"align-y-0\";s:9:\"align-y-0\";s:10:\"align-y-10\";s:10:\"align-y-10\";s:10:\"align-y-20\";s:10:\"align-y-20\";s:10:\"align-y-25\";s:10:\"align-y-25\";s:10:\"align-y-30\";s:10:\"align-y-30\";s:10:\"align-y-40\";s:10:\"align-y-40\";s:10:\"align-y-50\";s:10:\"align-y-50\";s:10:\"align-y-60\";s:10:\"align-y-60\";s:10:\"align-y-70\";s:10:\"align-y-70\";s:10:\"align-y-75\";s:10:\"align-y-75\";s:10:\"align-y-80\";s:10:\"align-y-80\";s:10:\"align-y-90\";s:10:\"align-y-90\";s:11:\"align-y-100\";s:11:\"align-y-100\";s:9:\"align-x-0\";s:9:\"align-x-0\";s:10:\"align-x-10\";s:10:\"align-x-10\";s:10:\"align-x-20\";s:10:\"align-x-20\";s:10:\"align-x-25\";s:10:\"align-x-25\";s:10:\"align-x-30\";s:10:\"align-x-30\";s:10:\"align-x-40\";s:10:\"align-x-40\";s:10:\"align-x-50\";s:10:\"align-x-50\";s:10:\"align-x-60\";s:10:\"align-x-60\";s:10:\"align-x-70\";s:10:\"align-x-70\";s:10:\"align-x-75\";s:10:\"align-x-75\";s:10:\"align-x-80\";s:10:\"align-x-80\";s:10:\"align-x-90\";s:10:\"align-x-90\";s:11:\"align-x-100\";s:11:\"align-x-100\";}s:13:\"default_value\";a:0:{}s:10:\"allow_null\";i:1;s:8:\"multiple\";i:0;s:2:\"ui\";i:0;s:13:\"return_format\";s:5:\"value\";s:4:\"ajax\";i:0;s:11:\"placeholder\";s:0:\"\";}','Image Style','cover_image_style','publish','closed','closed','','field_5ba902fb451f8','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=26',5,'acf-field','',0),
	(27,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:8:\"textarea\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:4:\"rows\";i:1;s:9:\"new_lines\";s:0:\"\";}','Caption','cover_caption','publish','closed','closed','','field_5923edcd4d475','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=27',6,'acf-field','',0),
	(28,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Link','cover_link','publish','closed','closed','','field_5ae1a8b8901c8','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=28',7,'acf-field','',0),
	(29,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','URL','cover_url','publish','closed','closed','','field_5ae1a8c8901c9','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=29',8,'acf-field','',0),
	(30,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Icon','cover_icon','publish','closed','closed','','field_5923eac3acbc6','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=30',9,'acf-field','',0),
	(31,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Style','cover_style','publish','closed','closed','','field_5923eb15acbc8','','','2018-10-29 12:23:08','2018-10-29 11:23:08','',20,'http://test.abetter.loc/?post_type=acf-field&#038;p=31',10,'acf-field','',0),
	(32,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:8:{s:8:\"location\";a:2:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"!=\";s:5:\"value\";s:10:\"dictionary\";}}i:1;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:4:\"post\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:4:\"left\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:5:\"local\";s:3:\"php\";}','Teaser','teaser','publish','closed','closed','','group_5b7d203e6b717','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',0,'http://test.abetter.loc/?post_type=acf-field-group&p=32',-100,'acf-field-group','',0),
	(33,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Label','teaser_label','publish','closed','closed','','field_5b7d207d2d624','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',32,'http://test.abetter.loc/?post_type=acf-field&p=33',0,'acf-field','',0),
	(34,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Headline','teaser_headline','publish','closed','closed','','field_5b7d20522d622','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',32,'http://test.abetter.loc/?post_type=acf-field&p=34',1,'acf-field','',0),
	(35,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:8:\"textarea\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:4:\"rows\";i:2;s:9:\"new_lines\";s:0:\"\";}','Excerpt','teaser_excerpt','publish','closed','closed','','field_5b7d206e2d623','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',32,'http://test.abetter.loc/?post_type=acf-field&p=35',2,'acf-field','',0),
	(36,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:15:{s:4:\"type\";s:5:\"image\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"return_format\";s:3:\"url\";s:12:\"preview_size\";s:4:\"full\";s:7:\"library\";s:3:\"all\";s:9:\"min_width\";s:0:\"\";s:10:\"min_height\";s:0:\"\";s:8:\"min_size\";s:0:\"\";s:9:\"max_width\";s:0:\"\";s:10:\"max_height\";s:0:\"\";s:8:\"max_size\";s:0:\"\";s:10:\"mime_types\";s:0:\"\";}','Image','teaser_image','publish','closed','closed','','field_5b7d22b6bec6a','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',32,'http://test.abetter.loc/?post_type=acf-field&p=36',3,'acf-field','',0),
	(37,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Link','teaser_link','publish','closed','closed','','field_5b7d20932d625','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',32,'http://test.abetter.loc/?post_type=acf-field&p=37',4,'acf-field','',0),
	(38,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:8:{s:8:\"location\";a:2:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"!=\";s:5:\"value\";s:10:\"dictionary\";}}i:1;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:4:\"post\";}}}s:8:\"position\";s:15:\"acf_after_title\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:4:\"left\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:5:\"local\";s:3:\"php\";}','Breadcrumbs','breadcrumbs','publish','closed','closed','','group_5b49e11e6aa9f','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',0,'http://test.abetter.loc/?post_type=acf-field-group&p=38',-50,'acf-field-group','',0),
	(39,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:12:{s:4:\"type\";s:8:\"checkbox\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:1:{s:18:\"breadcrumbs-hidden\";s:6:\"Hidden\";}s:12:\"allow_custom\";i:0;s:11:\"save_custom\";i:0;s:13:\"default_value\";a:0:{}s:6:\"layout\";s:10:\"horizontal\";s:6:\"toggle\";i:0;s:13:\"return_format\";s:5:\"value\";}','Type','breadcrumbs_type','publish','closed','closed','','field_5b49e125f50fa','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',38,'http://test.abetter.loc/?post_type=acf-field&p=39',0,'acf-field','',0),
	(40,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Style','breadcrumbs_style','publish','closed','closed','','field_5b49e1f03e289','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',38,'http://test.abetter.loc/?post_type=acf-field&p=40',1,'acf-field','',0),
	(41,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:8:{s:8:\"location\";a:2:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"!=\";s:5:\"value\";s:10:\"dictionary\";}}i:1;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:4:\"post\";}}}s:8:\"position\";s:4:\"side\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:5:\"local\";s:3:\"php\";}','Menu','menu','publish','closed','closed','','group_592714059285d','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',0,'http://test.abetter.loc/?post_type=acf-field-group&p=41',0,'acf-field-group','',0),
	(42,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Label','menu_label','publish','closed','closed','','field_5927141d5dafe','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',41,'http://test.abetter.loc/?post_type=acf-field&p=42',0,'acf-field','',0),
	(43,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:8:\"textarea\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:4:\"rows\";i:2;s:9:\"new_lines\";s:0:\"\";}','Description','menu_description','publish','closed','closed','','field_5a6f27868306e','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',41,'http://test.abetter.loc/?post_type=acf-field&p=43',1,'acf-field','',0),
	(44,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Icon','menu_icon','publish','closed','closed','','field_592714245daff','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',41,'http://test.abetter.loc/?post_type=acf-field&p=44',2,'acf-field','',0),
	(45,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Style','menu_style','publish','closed','closed','','field_5927142c5db00','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',41,'http://test.abetter.loc/?post_type=acf-field&p=45',3,'acf-field','',0),
	(46,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:8:{s:8:\"location\";a:2:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"!=\";s:5:\"value\";s:10:\"dictionary\";}}i:1;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:4:\"post\";}}}s:8:\"position\";s:15:\"acf_after_title\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:4:\"left\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:5:\"local\";s:3:\"php\";}','SEO','seo','publish','closed','closed','','group_588f42e94411a','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',0,'http://test.abetter.loc/?post_type=acf-field-group&p=46',0,'acf-field-group','',0),
	(47,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}','Title','seo_title','publish','closed','closed','','field_588f42ed28220','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=47',0,'acf-field','',0),
	(48,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:8:\"textarea\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:4:\"rows\";i:2;s:9:\"new_lines\";s:0:\"\";}','Description','seo_description','publish','closed','closed','','field_588f42f828221','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=48',1,'acf-field','',0),
	(49,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Keywords','seo_keywords','publish','closed','closed','','field_59109efed9b93','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=49',2,'acf-field','',0),
	(50,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}','Type','seo_type','publish','closed','closed','','field_588f430128222','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=50',3,'acf-field','',0),
	(51,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:15:{s:4:\"type\";s:5:\"image\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"return_format\";s:3:\"url\";s:12:\"preview_size\";s:4:\"full\";s:7:\"library\";s:3:\"all\";s:9:\"min_width\";s:0:\"\";s:10:\"min_height\";s:0:\"\";s:8:\"min_size\";s:0:\"\";s:9:\"max_width\";s:0:\"\";s:10:\"max_height\";s:0:\"\";s:8:\"max_size\";s:0:\"\";s:10:\"mime_types\";s:0:\"\";}','Image','seo_image','publish','closed','closed','','field_588f431f28224','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=51',4,'acf-field','',0),
	(52,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:7:{s:4:\"type\";s:3:\"url\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";}','Url','seo_url','publish','closed','closed','','field_588f430828223','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=52',5,'acf-field','',0),
	(53,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Author','seo_author','publish','closed','closed','','field_5baa50441f8bd','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=53',6,'acf-field','',0),
	(54,1,'2018-10-29 11:15:09','2018-10-29 10:15:09','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Robots','seo_robots','publish','closed','closed','','field_5baa50521f8be','','','2018-10-29 11:15:09','2018-10-29 10:15:09','',46,'http://test.abetter.loc/?post_type=acf-field&p=54',7,'acf-field','',0),
	(55,1,'2018-10-29 11:15:10','2018-10-29 10:15:10','a:8:{s:8:\"location\";a:2:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"!=\";s:5:\"value\";s:10:\"dictionary\";}}i:1;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:4:\"post\";}}}s:8:\"position\";s:4:\"side\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:5:\"local\";s:3:\"php\";}','Settings','settings','publish','closed','closed','','group_5a70481912181','','','2018-10-29 11:15:10','2018-10-29 10:15:10','',0,'http://test.abetter.loc/?post_type=acf-field-group&p=55',0,'acf-field-group','',0),
	(56,1,'2018-10-29 11:15:10','2018-10-29 10:15:10','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Expire','settings_expire','publish','closed','closed','','field_5a70481ec73e7','','','2018-10-29 11:15:10','2018-10-29 10:15:10','',55,'http://test.abetter.loc/?post_type=acf-field&p=56',0,'acf-field','',0),
	(57,1,'2018-10-29 11:15:10','2018-10-29 10:15:10','a:10:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";}','Redirect','settings_redirect','publish','closed','closed','','field_5a70482bc73e8','','','2018-10-29 11:15:10','2018-10-29 10:15:10','',55,'http://test.abetter.loc/?post_type=acf-field&p=57',1,'acf-field','',0),
	(58,1,'2018-10-29 12:27:15','2018-10-29 11:27:15','','Features','','publish','closed','closed','','features','','','2018-10-29 12:27:40','2018-10-29 11:27:40','',0,'http://test.abetter.loc/?page_id=58',20,'page','',0),
	(61,1,'2018-10-29 12:28:07','2018-10-29 11:28:07',' ','','','publish','closed','closed','','61','','','2018-10-29 12:28:07','2018-10-29 11:28:07','',0,'http://test.abetter.loc/?p=61',2,'nav_menu_item','',0);

/*!40000 ALTER TABLE `wp_posts` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_term_relationships
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_term_relationships`;

CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_term_relationships` WRITE;
/*!40000 ALTER TABLE `wp_term_relationships` DISABLE KEYS */;

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`)
VALUES
	(15,2,0),
	(16,2,0),
	(19,2,0),
	(61,2,0);

/*!40000 ALTER TABLE `wp_term_relationships` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_term_taxonomy
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_term_taxonomy`;

CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_term_taxonomy` WRITE;
/*!40000 ALTER TABLE `wp_term_taxonomy` DISABLE KEYS */;

INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`)
VALUES
	(1,1,'category','',0,0),
	(2,2,'nav_menu','',0,4);

/*!40000 ALTER TABLE `wp_term_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_termmeta
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_termmeta`;

CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;



# Tabelldump wp_terms
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_terms`;

CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_terms` WRITE;
/*!40000 ALTER TABLE `wp_terms` DISABLE KEYS */;

INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`)
VALUES
	(1,'Uncategorized','uncategorized',0),
	(2,'Main','main',0);

/*!40000 ALTER TABLE `wp_terms` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_usermeta
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_usermeta`;

CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_usermeta` WRITE;
/*!40000 ALTER TABLE `wp_usermeta` DISABLE KEYS */;

INSERT INTO `wp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`)
VALUES
	(1,1,'nickname','admin'),
	(2,1,'first_name',''),
	(3,1,'last_name',''),
	(4,1,'description',''),
	(5,1,'rich_editing','true'),
	(6,1,'syntax_highlighting','true'),
	(7,1,'comment_shortcuts','false'),
	(8,1,'admin_color','fresh'),
	(9,1,'use_ssl','0'),
	(10,1,'show_admin_bar_front','true'),
	(11,1,'locale',''),
	(12,1,'wp_capabilities','a:1:{s:13:\"administrator\";b:1;}'),
	(13,1,'wp_user_level','10'),
	(14,1,'dismissed_wp_pointers','wp496_privacy'),
	(15,1,'show_welcome_panel','1'),
	(16,1,'session_tokens','a:2:{s:64:\"a5444671a674228a579503dc21046b227bcf2226e5c6a31dcb002ac1035dfe50\";a:4:{s:10:\"expiration\";i:1542017689;s:2:\"ip\";s:3:\"::1\";s:2:\"ua\";s:121:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36\";s:5:\"login\";i:1540808089;}s:64:\"1c1851d604335a100f9d0dcb546fced679724bf0e499d29c61c0cf08f39afce4\";a:4:{s:10:\"expiration\";i:1542023120;s:2:\"ip\";s:3:\"::1\";s:2:\"ua\";s:121:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36\";s:5:\"login\";i:1540813520;}}'),
	(17,1,'wp_dashboard_quick_press_last_post_id','14'),
	(18,1,'managenav-menuscolumnshidden','a:4:{i:0;s:11:\"link-target\";i:1;s:15:\"title-attribute\";i:2;s:3:\"xfn\";i:3;s:11:\"description\";}'),
	(19,1,'metaboxhidden_nav-menus','a:1:{i:0;s:12:\"add-post_tag\";}'),
	(20,1,'nav_menu_recently_edited','2'),
	(21,1,'closedpostboxes_page','a:3:{i:0;s:23:\"acf-group_5923ea9cd5f21\";i:1;s:23:\"acf-group_5b49e11e6aa9f\";i:2;s:23:\"acf-group_588f42e94411a\";}'),
	(22,1,'metaboxhidden_page','a:4:{i:0;s:11:\"postexcerpt\";i:1;s:16:\"commentstatusdiv\";i:2;s:7:\"slugdiv\";i:3;s:9:\"authordiv\";}');

/*!40000 ALTER TABLE `wp_usermeta` ENABLE KEYS */;
UNLOCK TABLES;


# Tabelldump wp_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wp_users`;

CREATE TABLE `wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

LOCK TABLES `wp_users` WRITE;
/*!40000 ALTER TABLE `wp_users` DISABLE KEYS */;

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`)
VALUES
	(1,'admin','$P$BJU/t80JhCBEJxy.NnckytaNfZK.DJ.','admin','johan@sjoland.com','','2018-06-13 08:30:52','',0,'admin');

/*!40000 ALTER TABLE `wp_users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
