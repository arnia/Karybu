-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Gazda: localhost
-- Timp de generare: 29 Sep 2012 la 18:40
-- Versiune server: 5.5.24-0ubuntu0.12.04.1
-- Versiune PHP: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Baza de date: `xe150_shop`
--

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_action_forward`
--

DROP TABLE IF EXISTS `xe_action_forward`;
CREATE TABLE IF NOT EXISTS `xe_action_forward` (
  `act` varchar(80) NOT NULL,
  `module` varchar(60) NOT NULL,
  `type` varchar(15) NOT NULL,
  UNIQUE KEY `idx_foward` (`act`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_addons`
--

DROP TABLE IF EXISTS `xe_addons`;
CREATE TABLE IF NOT EXISTS `xe_addons` (
  `addon` varchar(250) NOT NULL,
  `is_used` char(1) NOT NULL DEFAULT 'Y',
  `is_used_m` char(1) NOT NULL DEFAULT 'N',
  `is_fixed` char(1) NOT NULL DEFAULT 'N',
  `extra_vars` text,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`addon`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_addons_site`
--

DROP TABLE IF EXISTS `xe_addons_site`;
CREATE TABLE IF NOT EXISTS `xe_addons_site` (
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `addon` varchar(250) NOT NULL,
  `is_used` char(1) NOT NULL DEFAULT 'Y',
  `is_used_m` char(1) NOT NULL DEFAULT 'N',
  `extra_vars` text,
  `regdate` varchar(14) DEFAULT NULL,
  UNIQUE KEY `unique_addon_site` (`site_srl`,`addon`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_admin_favorite`
--

DROP TABLE IF EXISTS `xe_admin_favorite`;
CREATE TABLE IF NOT EXISTS `xe_admin_favorite` (
  `admin_favorite_srl` bigint(11) NOT NULL AUTO_INCREMENT,
  `site_srl` bigint(11) DEFAULT '0',
  `module` varchar(80) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`admin_favorite_srl`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=437 ;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_admin_log`
--

DROP TABLE IF EXISTS `xe_admin_log`;
CREATE TABLE IF NOT EXISTS `xe_admin_log` (
  `ipaddress` varchar(100) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `site_srl` bigint(11) DEFAULT '0',
  `module` varchar(100) DEFAULT NULL,
  `act` varchar(100) DEFAULT NULL,
  `request_vars` text,
  KEY `idx_admin_ip` (`ipaddress`),
  KEY `idx_admin_date` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_ai_installed_packages`
--

DROP TABLE IF EXISTS `xe_ai_installed_packages`;
CREATE TABLE IF NOT EXISTS `xe_ai_installed_packages` (
  `package_srl` bigint(11) NOT NULL DEFAULT '0',
  `version` varchar(255) DEFAULT NULL,
  `current_version` varchar(255) DEFAULT NULL,
  `need_update` char(1) DEFAULT 'N',
  KEY `idx_package_srl` (`package_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_ai_remote_categories`
--

DROP TABLE IF EXISTS `xe_ai_remote_categories`;
CREATE TABLE IF NOT EXISTS `xe_ai_remote_categories` (
  `category_srl` bigint(11) NOT NULL DEFAULT '0',
  `parent_srl` bigint(11) NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`category_srl`),
  KEY `idx_parent_srl` (`parent_srl`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_autoinstall_packages`
--

DROP TABLE IF EXISTS `xe_autoinstall_packages`;
CREATE TABLE IF NOT EXISTS `xe_autoinstall_packages` (
  `package_srl` bigint(11) NOT NULL DEFAULT '0',
  `category_srl` bigint(11) DEFAULT '0',
  `path` varchar(250) NOT NULL,
  `updatedate` varchar(14) DEFAULT NULL,
  `latest_item_srl` bigint(11) NOT NULL DEFAULT '0',
  `version` varchar(255) DEFAULT NULL,
  UNIQUE KEY `unique_path` (`path`),
  KEY `idx_package_srl` (`package_srl`),
  KEY `idx_category_srl` (`category_srl`),
  KEY `idx_regdate` (`updatedate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_comments`
--

DROP TABLE IF EXISTS `xe_comments`;
CREATE TABLE IF NOT EXISTS `xe_comments` (
  `comment_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `parent_srl` bigint(11) NOT NULL DEFAULT '0',
  `is_secret` char(1) NOT NULL DEFAULT 'N',
  `content` longtext NOT NULL,
  `voted_count` bigint(11) NOT NULL DEFAULT '0',
  `blamed_count` bigint(11) NOT NULL DEFAULT '0',
  `notify_message` char(1) NOT NULL DEFAULT 'N',
  `password` varchar(60) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `user_name` varchar(80) NOT NULL,
  `nick_name` varchar(80) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `email_address` varchar(250) NOT NULL,
  `homepage` varchar(250) NOT NULL,
  `uploaded_count` bigint(11) NOT NULL DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  `status` bigint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`comment_srl`),
  UNIQUE KEY `idx_module_list_order` (`module_srl`,`list_order`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_voted_count` (`voted_count`),
  KEY `idx_blamed_count` (`blamed_count`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_uploaded_count` (`uploaded_count`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_last_update` (`last_update`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_list_order` (`list_order`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_comments_list`
--

DROP TABLE IF EXISTS `xe_comments_list`;
CREATE TABLE IF NOT EXISTS `xe_comments_list` (
  `comment_srl` bigint(11) NOT NULL,
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `head` bigint(11) NOT NULL DEFAULT '0',
  `arrange` bigint(11) NOT NULL DEFAULT '0',
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  `depth` bigint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_srl`),
  KEY `idx_list` (`document_srl`,`head`,`arrange`),
  KEY `idx_date` (`module_srl`,`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_comment_declared`
--

DROP TABLE IF EXISTS `xe_comment_declared`;
CREATE TABLE IF NOT EXISTS `xe_comment_declared` (
  `comment_srl` bigint(11) NOT NULL,
  `declared_count` bigint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_srl`),
  KEY `idx_declared_count` (`declared_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_comment_declared_log`
--

DROP TABLE IF EXISTS `xe_comment_declared_log`;
CREATE TABLE IF NOT EXISTS `xe_comment_declared_log` (
  `comment_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_comment_srl` (`comment_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_comment_voted_log`
--

DROP TABLE IF EXISTS `xe_comment_voted_log`;
CREATE TABLE IF NOT EXISTS `xe_comment_voted_log` (
  `comment_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `point` bigint(11) NOT NULL,
  KEY `idx_comment_srl` (`comment_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_counter_log`
--

DROP TABLE IF EXISTS `xe_counter_log`;
CREATE TABLE IF NOT EXISTS `xe_counter_log` (
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `ipaddress` varchar(250) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `user_agent` varchar(250) DEFAULT NULL,
  KEY `idx_site_counter_log` (`site_srl`,`ipaddress`),
  KEY `idx_counter_log` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_counter_site_status`
--

DROP TABLE IF EXISTS `xe_counter_site_status`;
CREATE TABLE IF NOT EXISTS `xe_counter_site_status` (
  `site_srl` bigint(11) NOT NULL,
  `regdate` bigint(11) NOT NULL,
  `unique_visitor` bigint(11) DEFAULT '0',
  `pageview` bigint(11) DEFAULT '0',
  UNIQUE KEY `site_status` (`site_srl`,`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_counter_status`
--

DROP TABLE IF EXISTS `xe_counter_status`;
CREATE TABLE IF NOT EXISTS `xe_counter_status` (
  `regdate` bigint(11) NOT NULL,
  `unique_visitor` bigint(11) DEFAULT '0',
  `pageview` bigint(11) DEFAULT '0',
  PRIMARY KEY (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_documents`
--

DROP TABLE IF EXISTS `xe_documents`;
CREATE TABLE IF NOT EXISTS `xe_documents` (
  `document_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `category_srl` bigint(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(10) NOT NULL DEFAULT '',
  `is_notice` char(1) NOT NULL DEFAULT 'N',
  `title` varchar(250) DEFAULT NULL,
  `title_bold` char(1) NOT NULL DEFAULT 'N',
  `title_color` varchar(7) DEFAULT NULL,
  `content` longtext NOT NULL,
  `readed_count` bigint(11) NOT NULL DEFAULT '0',
  `voted_count` bigint(11) NOT NULL DEFAULT '0',
  `blamed_count` bigint(11) NOT NULL DEFAULT '0',
  `comment_count` bigint(11) NOT NULL DEFAULT '0',
  `trackback_count` bigint(11) NOT NULL DEFAULT '0',
  `uploaded_count` bigint(11) NOT NULL DEFAULT '0',
  `password` varchar(60) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `user_name` varchar(80) NOT NULL,
  `nick_name` varchar(80) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `email_address` varchar(250) NOT NULL,
  `homepage` varchar(250) NOT NULL,
  `tags` text,
  `extra_vars` text,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `last_updater` varchar(80) DEFAULT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  `update_order` bigint(11) NOT NULL,
  `allow_trackback` char(1) NOT NULL DEFAULT 'Y',
  `notify_message` char(1) NOT NULL DEFAULT 'N',
  `status` varchar(20) DEFAULT 'PUBLIC',
  `comment_status` varchar(20) DEFAULT 'ALLOW',
  PRIMARY KEY (`document_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_category_srl` (`category_srl`),
  KEY `idx_is_notice` (`is_notice`),
  KEY `idx_readed_count` (`readed_count`),
  KEY `idx_voted_count` (`voted_count`),
  KEY `idx_blamed_count` (`blamed_count`),
  KEY `idx_comment_count` (`comment_count`),
  KEY `idx_trackback_count` (`trackback_count`),
  KEY `idx_uploaded_count` (`uploaded_count`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_last_update` (`last_update`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_list_order` (`list_order`),
  KEY `idx_update_order` (`update_order`),
  KEY `idx_module_list_order` (`module_srl`,`list_order`),
  KEY `idx_module_update_order` (`module_srl`,`update_order`),
  KEY `idx_module_readed_count` (`module_srl`,`readed_count`),
  KEY `idx_module_voted_count` (`module_srl`,`voted_count`),
  KEY `idx_module_notice` (`module_srl`,`is_notice`),
  KEY `idx_module_document_srl` (`module_srl`,`document_srl`),
  KEY `idx_module_blamed_count` (`module_srl`,`blamed_count`),
  KEY `idx_module_status` (`module_srl`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_aliases`
--

DROP TABLE IF EXISTS `xe_document_aliases`;
CREATE TABLE IF NOT EXISTS `xe_document_aliases` (
  `alias_srl` bigint(11) NOT NULL DEFAULT '0',
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `alias_title` varchar(250) NOT NULL,
  PRIMARY KEY (`alias_srl`),
  UNIQUE KEY `idx_module_title` (`module_srl`,`alias_title`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_alias_title` (`alias_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_categories`
--

DROP TABLE IF EXISTS `xe_document_categories`;
CREATE TABLE IF NOT EXISTS `xe_document_categories` (
  `category_srl` bigint(11) NOT NULL DEFAULT '0',
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `parent_srl` bigint(12) NOT NULL DEFAULT '0',
  `title` varchar(250) DEFAULT NULL,
  `expand` char(1) DEFAULT 'N',
  `document_count` bigint(11) NOT NULL DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `list_order` bigint(11) NOT NULL,
  `group_srls` text,
  `color` varchar(11) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`category_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_declared`
--

DROP TABLE IF EXISTS `xe_document_declared`;
CREATE TABLE IF NOT EXISTS `xe_document_declared` (
  `document_srl` bigint(11) NOT NULL,
  `declared_count` bigint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`document_srl`),
  KEY `idx_declared_count` (`declared_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_declared_log`
--

DROP TABLE IF EXISTS `xe_document_declared_log`;
CREATE TABLE IF NOT EXISTS `xe_document_declared_log` (
  `document_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_extra_keys`
--

DROP TABLE IF EXISTS `xe_document_extra_keys`;
CREATE TABLE IF NOT EXISTS `xe_document_extra_keys` (
  `module_srl` bigint(11) NOT NULL,
  `var_idx` bigint(11) NOT NULL,
  `var_name` varchar(250) NOT NULL,
  `var_type` varchar(50) NOT NULL,
  `var_is_required` char(1) NOT NULL DEFAULT 'N',
  `var_search` char(1) NOT NULL DEFAULT 'N',
  `var_default` text,
  `var_desc` text,
  `eid` varchar(40) DEFAULT NULL,
  UNIQUE KEY `unique_module_keys` (`module_srl`,`var_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_extra_vars`
--

DROP TABLE IF EXISTS `xe_document_extra_vars`;
CREATE TABLE IF NOT EXISTS `xe_document_extra_vars` (
  `module_srl` bigint(11) NOT NULL,
  `document_srl` bigint(11) NOT NULL,
  `var_idx` bigint(11) NOT NULL,
  `lang_code` varchar(10) NOT NULL,
  `value` longtext,
  `eid` varchar(40) DEFAULT NULL,
  UNIQUE KEY `unique_extra_vars` (`module_srl`,`document_srl`,`var_idx`,`lang_code`),
  KEY `idx_document_list_order` (`document_srl`,`module_srl`,`var_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_histories`
--

DROP TABLE IF EXISTS `xe_document_histories`;
CREATE TABLE IF NOT EXISTS `xe_document_histories` (
  `history_srl` bigint(11) NOT NULL DEFAULT '0',
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `content` longtext,
  `nick_name` varchar(80) NOT NULL,
  `member_srl` bigint(11) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `ipaddress` varchar(128) NOT NULL,
  PRIMARY KEY (`history_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_ipaddress` (`ipaddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_readed_log`
--

DROP TABLE IF EXISTS `xe_document_readed_log`;
CREATE TABLE IF NOT EXISTS `xe_document_readed_log` (
  `document_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_trash`
--

DROP TABLE IF EXISTS `xe_document_trash`;
CREATE TABLE IF NOT EXISTS `xe_document_trash` (
  `trash_srl` bigint(11) NOT NULL DEFAULT '0',
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `trash_date` varchar(14) DEFAULT NULL,
  `description` text,
  `ipaddress` varchar(128) NOT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `user_name` varchar(80) NOT NULL,
  `nick_name` varchar(80) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  PRIMARY KEY (`trash_srl`),
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_trash_date` (`trash_date`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_member_srl` (`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_document_voted_log`
--

DROP TABLE IF EXISTS `xe_document_voted_log`;
CREATE TABLE IF NOT EXISTS `xe_document_voted_log` (
  `document_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `point` bigint(11) NOT NULL,
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_editor_autosave`
--

DROP TABLE IF EXISTS `xe_editor_autosave`;
CREATE TABLE IF NOT EXISTS `xe_editor_autosave` (
  `member_srl` bigint(11) DEFAULT '0',
  `ipaddress` varchar(128) DEFAULT NULL,
  `module_srl` bigint(11) DEFAULT NULL,
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `title` varchar(250) DEFAULT NULL,
  `content` longtext NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_editor_components`
--

DROP TABLE IF EXISTS `xe_editor_components`;
CREATE TABLE IF NOT EXISTS `xe_editor_components` (
  `component_name` varchar(250) NOT NULL,
  `enabled` char(1) NOT NULL DEFAULT 'N',
  `extra_vars` text,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`component_name`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_editor_components_site`
--

DROP TABLE IF EXISTS `xe_editor_components_site`;
CREATE TABLE IF NOT EXISTS `xe_editor_components_site` (
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `component_name` varchar(250) NOT NULL,
  `enabled` char(1) NOT NULL DEFAULT 'N',
  `extra_vars` text,
  `list_order` bigint(11) NOT NULL,
  UNIQUE KEY `unique_component_site` (`site_srl`,`component_name`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_files`
--

DROP TABLE IF EXISTS `xe_files`;
CREATE TABLE IF NOT EXISTS `xe_files` (
  `file_srl` bigint(11) NOT NULL,
  `upload_target_srl` bigint(11) NOT NULL DEFAULT '0',
  `upload_target_type` char(3) DEFAULT NULL,
  `sid` varchar(60) DEFAULT NULL,
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `member_srl` bigint(11) NOT NULL,
  `download_count` bigint(11) NOT NULL DEFAULT '0',
  `direct_download` char(1) NOT NULL DEFAULT 'N',
  `source_filename` varchar(250) DEFAULT NULL,
  `uploaded_filename` varchar(250) DEFAULT NULL,
  `file_size` bigint(11) NOT NULL DEFAULT '0',
  `comment` varchar(250) DEFAULT NULL,
  `isvalid` char(1) DEFAULT 'N',
  `regdate` varchar(14) DEFAULT NULL,
  `ipaddress` varchar(128) NOT NULL,
  PRIMARY KEY (`file_srl`),
  KEY `idx_upload_target_srl` (`upload_target_srl`),
  KEY `idx_upload_target_type` (`upload_target_type`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_download_count` (`download_count`),
  KEY `idx_file_size` (`file_size`),
  KEY `idx_is_valid` (`isvalid`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_ipaddress` (`ipaddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_lang`
--

DROP TABLE IF EXISTS `xe_lang`;
CREATE TABLE IF NOT EXISTS `xe_lang` (
  `site_srl` bigint(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lang_code` varchar(10) NOT NULL,
  `value` text,
  KEY `idx_lang` (`site_srl`,`name`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_layouts`
--

DROP TABLE IF EXISTS `xe_layouts`;
CREATE TABLE IF NOT EXISTS `xe_layouts` (
  `layout_srl` bigint(12) NOT NULL,
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `layout` varchar(250) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `extra_vars` text,
  `layout_path` varchar(250) DEFAULT NULL,
  `module_srl` bigint(12) DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  `layout_type` char(1) DEFAULT 'P',
  PRIMARY KEY (`layout_srl`),
  KEY `menu_site_srl` (`site_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member`
--

DROP TABLE IF EXISTS `xe_member`;
CREATE TABLE IF NOT EXISTS `xe_member` (
  `member_srl` bigint(11) NOT NULL,
  `user_id` varchar(80) NOT NULL,
  `email_address` varchar(250) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email_id` varchar(80) NOT NULL,
  `email_host` varchar(160) DEFAULT NULL,
  `user_name` varchar(40) NOT NULL,
  `nick_name` varchar(40) NOT NULL,
  `find_account_question` bigint(11) DEFAULT NULL,
  `find_account_answer` varchar(250) DEFAULT NULL,
  `homepage` varchar(250) DEFAULT NULL,
  `blog` varchar(250) DEFAULT NULL,
  `birthday` char(8) DEFAULT NULL,
  `allow_mailing` char(1) NOT NULL DEFAULT 'Y',
  `allow_message` char(1) NOT NULL DEFAULT 'Y',
  `denied` char(1) DEFAULT 'N',
  `limit_date` varchar(14) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `last_login` varchar(14) DEFAULT NULL,
  `change_password_date` varchar(14) DEFAULT NULL,
  `is_admin` char(1) DEFAULT 'N',
  `description` text,
  `extra_vars` text,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`member_srl`),
  UNIQUE KEY `unique_user_id` (`user_id`),
  UNIQUE KEY `unique_email_address` (`email_address`),
  UNIQUE KEY `unique_nick_name` (`nick_name`),
  KEY `idx_email_host` (`email_host`),
  KEY `idx_allow_mailing` (`allow_mailing`),
  KEY `idx_is_denied` (`denied`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_last_login` (`last_login`),
  KEY `idx_is_admin` (`is_admin`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_auth_mail`
--

DROP TABLE IF EXISTS `xe_member_auth_mail`;
CREATE TABLE IF NOT EXISTS `xe_member_auth_mail` (
  `auth_key` varchar(60) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `user_id` varchar(80) NOT NULL,
  `new_password` varchar(80) NOT NULL,
  `is_register` char(1) DEFAULT 'N',
  `regdate` varchar(14) DEFAULT NULL,
  UNIQUE KEY `unique_key` (`auth_key`,`member_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_autologin`
--

DROP TABLE IF EXISTS `xe_member_autologin`;
CREATE TABLE IF NOT EXISTS `xe_member_autologin` (
  `autologin_key` varchar(80) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  UNIQUE KEY `unique_key` (`autologin_key`,`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_count_history`
--

DROP TABLE IF EXISTS `xe_member_count_history`;
CREATE TABLE IF NOT EXISTS `xe_member_count_history` (
  `member_srl` bigint(11) NOT NULL,
  `content` longtext NOT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`member_srl`),
  KEY `idx_last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_denied_nick_name`
--

DROP TABLE IF EXISTS `xe_member_denied_nick_name`;
CREATE TABLE IF NOT EXISTS `xe_member_denied_nick_name` (
  `nick_name` varchar(80) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`nick_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_denied_user_id`
--

DROP TABLE IF EXISTS `xe_member_denied_user_id`;
CREATE TABLE IF NOT EXISTS `xe_member_denied_user_id` (
  `user_id` varchar(80) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `description` text,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_friend`
--

DROP TABLE IF EXISTS `xe_member_friend`;
CREATE TABLE IF NOT EXISTS `xe_member_friend` (
  `friend_srl` bigint(11) NOT NULL,
  `friend_group_srl` bigint(11) NOT NULL DEFAULT '0',
  `member_srl` bigint(11) NOT NULL,
  `target_srl` bigint(11) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`friend_srl`),
  KEY `idx_friend_group_srl` (`friend_group_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_target_srl` (`target_srl`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_friend_group`
--

DROP TABLE IF EXISTS `xe_member_friend_group`;
CREATE TABLE IF NOT EXISTS `xe_member_friend_group` (
  `friend_group_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`friend_group_srl`),
  KEY `index_owner_member_srl` (`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_group`
--

DROP TABLE IF EXISTS `xe_member_group`;
CREATE TABLE IF NOT EXISTS `xe_member_group` (
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `group_srl` bigint(11) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  `title` varchar(80) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `is_default` char(1) DEFAULT 'N',
  `is_admin` char(1) DEFAULT 'N',
  `image_mark` text,
  `description` text,
  PRIMARY KEY (`group_srl`),
  UNIQUE KEY `idx_site_title` (`site_srl`,`title`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_group_member`
--

DROP TABLE IF EXISTS `xe_member_group_member`;
CREATE TABLE IF NOT EXISTS `xe_member_group_member` (
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `group_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_site_srl` (`site_srl`),
  KEY `idx_group_member` (`group_srl`,`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_join_form`
--

DROP TABLE IF EXISTS `xe_member_join_form`;
CREATE TABLE IF NOT EXISTS `xe_member_join_form` (
  `member_join_form_srl` bigint(11) NOT NULL,
  `column_type` varchar(60) NOT NULL,
  `column_name` varchar(60) NOT NULL,
  `column_title` varchar(60) NOT NULL,
  `required` char(1) NOT NULL DEFAULT 'N',
  `default_value` text,
  `is_active` char(1) DEFAULT 'Y',
  `description` text,
  `list_order` bigint(11) NOT NULL DEFAULT '1',
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`member_join_form_srl`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_login_count`
--

DROP TABLE IF EXISTS `xe_member_login_count`;
CREATE TABLE IF NOT EXISTS `xe_member_login_count` (
  `ipaddress` varchar(128) NOT NULL,
  `count` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_message`
--

DROP TABLE IF EXISTS `xe_member_message`;
CREATE TABLE IF NOT EXISTS `xe_member_message` (
  `message_srl` bigint(11) NOT NULL,
  `related_srl` bigint(11) NOT NULL,
  `sender_srl` bigint(11) NOT NULL,
  `receiver_srl` bigint(11) NOT NULL,
  `message_type` char(1) NOT NULL DEFAULT 'S',
  `title` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `readed` char(1) NOT NULL DEFAULT 'N',
  `list_order` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `readed_date` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`message_srl`),
  KEY `idx_related_srl` (`related_srl`),
  KEY `idx_sender_srl` (`sender_srl`),
  KEY `idx_receiver_srl` (`receiver_srl`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_member_scrap`
--

DROP TABLE IF EXISTS `xe_member_scrap`;
CREATE TABLE IF NOT EXISTS `xe_member_scrap` (
  `member_srl` bigint(11) NOT NULL,
  `document_srl` bigint(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `user_name` varchar(80) NOT NULL,
  `nick_name` varchar(80) NOT NULL,
  `target_member_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `list_order` bigint(11) NOT NULL,
  UNIQUE KEY `unique_scrap` (`member_srl`,`document_srl`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_menu`
--

DROP TABLE IF EXISTS `xe_menu`;
CREATE TABLE IF NOT EXISTS `xe_menu` (
  `menu_srl` bigint(12) NOT NULL,
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `title` varchar(250) DEFAULT NULL,
  `listorder` bigint(11) DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`menu_srl`),
  KEY `menu_site_srl` (`site_srl`),
  KEY `idx_title` (`title`),
  KEY `idx_listorder` (`listorder`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_menu_item`
--

DROP TABLE IF EXISTS `xe_menu_item`;
CREATE TABLE IF NOT EXISTS `xe_menu_item` (
  `menu_item_srl` bigint(12) NOT NULL,
  `parent_srl` bigint(12) NOT NULL DEFAULT '0',
  `menu_srl` bigint(12) NOT NULL,
  `name` text,
  `url` varchar(250) DEFAULT NULL,
  `open_window` char(1) DEFAULT 'N',
  `expand` char(1) DEFAULT 'N',
  `normal_btn` varchar(255) DEFAULT NULL,
  `hover_btn` varchar(255) DEFAULT NULL,
  `active_btn` varchar(255) DEFAULT NULL,
  `group_srls` text,
  `listorder` bigint(11) DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`menu_item_srl`),
  KEY `idx_menu_srl` (`menu_srl`),
  KEY `idx_listorder` (`listorder`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_menu_layout`
--

DROP TABLE IF EXISTS `xe_menu_layout`;
CREATE TABLE IF NOT EXISTS `xe_menu_layout` (
  `menu_srl` bigint(12) NOT NULL,
  `layout_srl` bigint(12) NOT NULL,
  PRIMARY KEY (`menu_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_modules`
--

DROP TABLE IF EXISTS `xe_modules`;
CREATE TABLE IF NOT EXISTS `xe_modules` (
  `module_srl` bigint(11) NOT NULL,
  `module` varchar(80) NOT NULL,
  `module_category_srl` bigint(11) DEFAULT '0',
  `layout_srl` bigint(11) DEFAULT '0',
  `use_mobile` char(1) DEFAULT 'N',
  `mlayout_srl` bigint(11) DEFAULT '0',
  `menu_srl` bigint(11) DEFAULT '0',
  `site_srl` bigint(11) NOT NULL DEFAULT '0',
  `mid` varchar(40) NOT NULL,
  `is_skin_fix` char(1) NOT NULL DEFAULT 'Y',
  `skin` varchar(250) DEFAULT NULL,
  `mskin` varchar(250) DEFAULT NULL,
  `browser_title` varchar(250) NOT NULL,
  `description` text,
  `is_default` char(1) NOT NULL DEFAULT 'N',
  `content` longtext,
  `mcontent` longtext,
  `open_rss` char(1) NOT NULL DEFAULT 'Y',
  `header_text` text,
  `footer_text` text,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`module_srl`),
  UNIQUE KEY `idx_site_mid` (`site_srl`,`mid`),
  KEY `idx_module` (`module`),
  KEY `idx_module_category` (`module_category_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_admins`
--

DROP TABLE IF EXISTS `xe_module_admins`;
CREATE TABLE IF NOT EXISTS `xe_module_admins` (
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  UNIQUE KEY `unique_module_admin` (`module_srl`,`member_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_categories`
--

DROP TABLE IF EXISTS `xe_module_categories`;
CREATE TABLE IF NOT EXISTS `xe_module_categories` (
  `module_category_srl` bigint(11) NOT NULL DEFAULT '0',
  `title` varchar(250) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`module_category_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_config`
--

DROP TABLE IF EXISTS `xe_module_config`;
CREATE TABLE IF NOT EXISTS `xe_module_config` (
  `module` varchar(250) NOT NULL,
  `site_srl` bigint(11) NOT NULL,
  `config` text,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_extend`
--

DROP TABLE IF EXISTS `xe_module_extend`;
CREATE TABLE IF NOT EXISTS `xe_module_extend` (
  `parent_module` varchar(80) NOT NULL,
  `extend_module` varchar(80) NOT NULL,
  `type` varchar(15) NOT NULL,
  `kind` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_extra_vars`
--

DROP TABLE IF EXISTS `xe_module_extra_vars`;
CREATE TABLE IF NOT EXISTS `xe_module_extra_vars` (
  `module_srl` bigint(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `value` text,
  UNIQUE KEY `unique_module_vars` (`module_srl`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_filebox`
--

DROP TABLE IF EXISTS `xe_module_filebox`;
CREATE TABLE IF NOT EXISTS `xe_module_filebox` (
  `module_filebox_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `fileextension` varchar(4) NOT NULL,
  `filesize` bigint(11) NOT NULL DEFAULT '0',
  `comment` varchar(250) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`module_filebox_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_fileextension` (`fileextension`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_grants`
--

DROP TABLE IF EXISTS `xe_module_grants`;
CREATE TABLE IF NOT EXISTS `xe_module_grants` (
  `module_srl` bigint(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `group_srl` bigint(11) NOT NULL,
  UNIQUE KEY `unique_module` (`module_srl`,`name`,`group_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_locks`
--

DROP TABLE IF EXISTS `xe_module_locks`;
CREATE TABLE IF NOT EXISTS `xe_module_locks` (
  `lock_name` varchar(40) NOT NULL,
  `deadline` varchar(14) DEFAULT NULL,
  `member_srl` bigint(11) DEFAULT NULL,
  UNIQUE KEY `unique_lock_name` (`lock_name`),
  KEY `idx_deadline` (`deadline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_mobile_skins`
--

DROP TABLE IF EXISTS `xe_module_mobile_skins`;
CREATE TABLE IF NOT EXISTS `xe_module_mobile_skins` (
  `module_srl` bigint(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `value` text,
  UNIQUE KEY `unique_module_mobile_skins` (`module_srl`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_part_config`
--

DROP TABLE IF EXISTS `xe_module_part_config`;
CREATE TABLE IF NOT EXISTS `xe_module_part_config` (
  `module` varchar(250) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `config` text,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_module_part_config` (`module`,`module_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_skins`
--

DROP TABLE IF EXISTS `xe_module_skins`;
CREATE TABLE IF NOT EXISTS `xe_module_skins` (
  `module_srl` bigint(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `value` text,
  UNIQUE KEY `unique_module_skins` (`module_srl`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_module_trigger`
--

DROP TABLE IF EXISTS `xe_module_trigger`;
CREATE TABLE IF NOT EXISTS `xe_module_trigger` (
  `trigger_name` varchar(80) NOT NULL,
  `called_position` varchar(15) NOT NULL,
  `module` varchar(80) NOT NULL,
  `type` varchar(15) NOT NULL,
  `called_method` varchar(80) NOT NULL,
  UNIQUE KEY `idx_trigger` (`trigger_name`,`called_position`,`module`,`type`,`called_method`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_point`
--

DROP TABLE IF EXISTS `xe_point`;
CREATE TABLE IF NOT EXISTS `xe_point` (
  `member_srl` bigint(11) NOT NULL,
  `point` bigint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`member_srl`),
  KEY `idx_point` (`point`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_poll`
--

DROP TABLE IF EXISTS `xe_poll`;
CREATE TABLE IF NOT EXISTS `xe_poll` (
  `poll_srl` bigint(11) NOT NULL,
  `stop_date` varchar(14) DEFAULT NULL,
  `upload_target_srl` bigint(11) NOT NULL,
  `poll_count` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`poll_srl`),
  KEY `idx_upload_target_srl` (`upload_target_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_poll_item`
--

DROP TABLE IF EXISTS `xe_poll_item`;
CREATE TABLE IF NOT EXISTS `xe_poll_item` (
  `poll_item_srl` bigint(11) NOT NULL,
  `poll_srl` bigint(11) NOT NULL,
  `poll_index_srl` bigint(11) NOT NULL,
  `upload_target_srl` bigint(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `poll_count` bigint(11) NOT NULL,
  PRIMARY KEY (`poll_item_srl`),
  KEY `index_poll_srl` (`poll_srl`),
  KEY `idx_poll_index_srl` (`poll_index_srl`),
  KEY `idx_upload_target_srl` (`upload_target_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_poll_log`
--

DROP TABLE IF EXISTS `xe_poll_log`;
CREATE TABLE IF NOT EXISTS `xe_poll_log` (
  `poll_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  KEY `idx_poll_srl` (`poll_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_poll_title`
--

DROP TABLE IF EXISTS `xe_poll_title`;
CREATE TABLE IF NOT EXISTS `xe_poll_title` (
  `poll_srl` bigint(11) NOT NULL,
  `poll_index_srl` bigint(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `checkcount` bigint(11) NOT NULL DEFAULT '1',
  `poll_count` bigint(11) NOT NULL,
  `upload_target_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `list_order` bigint(11) NOT NULL,
  KEY `idx_poll_srl` (`poll_srl`,`poll_index_srl`),
  KEY `idx_upload_target_srl` (`upload_target_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_sequence`
--

DROP TABLE IF EXISTS `xe_sequence`;
CREATE TABLE IF NOT EXISTS `xe_sequence` (
  `seq` bigint(64) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=775 ;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_session`
--

DROP TABLE IF EXISTS `xe_session`;
CREATE TABLE IF NOT EXISTS `xe_session` (
  `session_key` varchar(255) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `expired` varchar(14) DEFAULT NULL,
  `val` longtext,
  `ipaddress` varchar(128) NOT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `cur_mid` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`session_key`),
  KEY `idx_session_member_srl` (`member_srl`),
  KEY `idx_session_expired` (`expired`),
  KEY `idx_session_update` (`last_update`),
  KEY `idx_session_cur_mid` (`cur_mid`),
  KEY `idx_session_update_mid` (`member_srl`,`last_update`,`cur_mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop`
--

DROP TABLE IF EXISTS `xe_shop`;
CREATE TABLE IF NOT EXISTS `xe_shop` (
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `shop_title` varchar(250) NOT NULL,
  `shop_content` varchar(250) NOT NULL DEFAULT '',
  `profile_content` text NOT NULL,
  `input_email` char(1) DEFAULT 'R',
  `input_website` char(1) DEFAULT 'R',
  `timezone` varchar(10) DEFAULT '+0900',
  `currency` varchar(5) DEFAULT NULL,
  `VAT` float DEFAULT NULL,
  `telephone` bigint(20) DEFAULT NULL,
  `address` text,
  `regdate` varchar(14) NOT NULL,
  `currency_symbol` varchar(5) DEFAULT NULL,
  `discount_min_amount` bigint(20) DEFAULT NULL,
  `discount_type` varchar(40) DEFAULT NULL,
  `discount_amount` bigint(20) DEFAULT NULL,
  `discount_tax_phase` varchar(40) DEFAULT NULL,
  `out_of_stock_products` char(1) DEFAULT NULL,
  `minimum_order` bigint(20) DEFAULT NULL,
  `show_VAT` char(1) DEFAULT NULL,
  `menus` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`module_srl`),
  KEY `idx_member_srl` (`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_addresses`
--

DROP TABLE IF EXISTS `xe_shop_addresses`;
CREATE TABLE IF NOT EXISTS `xe_shop_addresses` (
  `address_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `country` varchar(45) DEFAULT NULL,
  `region` varchar(45) DEFAULT NULL,
  `city` varchar(45) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `postal_code` varchar(45) DEFAULT NULL,
  `telephone` varchar(45) DEFAULT NULL,
  `fax` varchar(45) DEFAULT NULL,
  `default_shipping` char(1) DEFAULT 'N',
  `default_billing` char(1) DEFAULT 'N',
  `email` varchar(45) DEFAULT NULL,
  `additional_info` text,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `firstname` varchar(45) DEFAULT NULL,
  `lastname` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`address_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_attributes`
--

DROP TABLE IF EXISTS `xe_shop_attributes`;
CREATE TABLE IF NOT EXISTS `xe_shop_attributes` (
  `attribute_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `title` varchar(160) NOT NULL,
  `type` varchar(14) DEFAULT NULL,
  `required` char(1) DEFAULT 'Y',
  `status` char(1) DEFAULT 'Y',
  `default_value` varchar(255) DEFAULT NULL,
  `values` text,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`attribute_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_attributes_scope`
--

DROP TABLE IF EXISTS `xe_shop_attributes_scope`;
CREATE TABLE IF NOT EXISTS `xe_shop_attributes_scope` (
  `attribute_srl` bigint(11) NOT NULL,
  `category_srl` bigint(11) NOT NULL,
  PRIMARY KEY (`attribute_srl`,`category_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_cart`
--

DROP TABLE IF EXISTS `xe_shop_cart`;
CREATE TABLE IF NOT EXISTS `xe_shop_cart` (
  `cart_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `billing_address_srl` bigint(11) DEFAULT NULL,
  `shipping_address_srl` bigint(11) DEFAULT NULL,
  `items` bigint(11) DEFAULT NULL,
  `extra` text,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`cart_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_cart_products`
--

DROP TABLE IF EXISTS `xe_shop_cart_products`;
CREATE TABLE IF NOT EXISTS `xe_shop_cart_products` (
  `cart_srl` bigint(11) NOT NULL,
  `product_srl` bigint(11) NOT NULL,
  `quantity` bigint(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `price` float(8, 4) DEFAULT NULL,
  PRIMARY KEY (`cart_srl`,`product_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_categories`
--

DROP TABLE IF EXISTS `xe_shop_categories`;
CREATE TABLE IF NOT EXISTS `xe_shop_categories` (
  `category_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `parent_srl` bigint(11) DEFAULT NULL,
  `filename` varchar(250) DEFAULT NULL,
  `title` varchar(250) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `product_count` bigint(11) DEFAULT NULL,
  `friendly_url` varchar(250) DEFAULT NULL,
  `include_in_navigation_menu` char(1) DEFAULT 'Y',
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `list_order` bigint(11) DEFAULT 0 NOT NULL,
  PRIMARY KEY (`category_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_guests`
--

DROP TABLE IF EXISTS `xe_shop_guests`;
CREATE TABLE IF NOT EXISTS `xe_shop_guests` (
  `guest_srl` bigint(11) NOT NULL,
  `address_srl` bigint(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `session_id` varchar(14) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`guest_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_invoices`
--

DROP TABLE IF EXISTS `xe_shop_invoices`;
CREATE TABLE IF NOT EXISTS `xe_shop_invoices` (
  `invoice_srl` bigint(11) NOT NULL,
  `order_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `comments` text,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`invoice_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_newsletters`
--

DROP TABLE IF EXISTS `xe_shop_newsletters`;
CREATE TABLE IF NOT EXISTS `xe_shop_newsletters` (
  `newsletter_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `subject` text,
  `sender_name` varchar(45) DEFAULT NULL,
  `sender_email` varchar(45) DEFAULT NULL,
  `content` text,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`newsletter_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_orders`
--

DROP TABLE IF EXISTS `xe_shop_orders`;
CREATE TABLE IF NOT EXISTS `xe_shop_orders` (
  `order_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `cart_srl` bigint(11) DEFAULT NULL,
  `member_srl` bigint(11) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_company` varchar(255) DEFAULT NULL,
  `billing_address` text,
  `shipping_address` text,
  `payment_method` varchar(255) DEFAULT NULL,
  `shipping_method` varchar(255) DEFAULT NULL,
  `shipping_cost` bigint(11) DEFAULT NULL,
  `total` float(20) NOT NULL,
  `vat` bigint(11) DEFAULT NULL,
  `order_status` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `transaction_id` varchar(128) DEFAULT NULL,
  `discount_min_order` bigint(11) DEFAULT NULL,
  `discount_type` varchar(45) DEFAULT NULL,
  `discount_amount` bigint(11) DEFAULT NULL,
  `discount_tax_phase` varchar(20) DEFAULT NULL,
  `discount_reduction_value` float(20) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`order_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_order_products`
--

DROP TABLE IF EXISTS `xe_shop_order_products`;
CREATE TABLE IF NOT EXISTS `xe_shop_order_products` (
  `order_srl` bigint(11) NOT NULL,
  `product_srl` bigint(11) NOT NULL,
  `quantity` bigint(11) DEFAULT NULL,
  `member_srl` bigint(11) NOT NULL,
  `parent_product_srl` bigint(11) DEFAULT NULL,
  `product_type` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` longtext,
  `short_description` varchar(500) DEFAULT NULL,
  `sku` varchar(250) NOT NULL,
  `weight` float DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `friendly_url` varchar(50) DEFAULT NULL,
  `price` float NOT NULL,
  `discount_price` float DEFAULT NULL,
  `qty` float DEFAULT NULL,
  `in_stock` char(1) DEFAULT 'N',
  `primary_image_filename` varchar(250) DEFAULT NULL,
  `related_products` varchar(500) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`order_srl`,`product_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_payment_gateways`
--

DROP TABLE IF EXISTS `xe_shop_payment_gateways`;
-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_payment_methods`
--

DROP TABLE IF EXISTS `xe_shop_payment_methods`;
CREATE TABLE IF NOT EXISTS `xe_shop_payment_methods` (
  `id` bigint(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `status` bigint(1) DEFAULT '0',
  `props` text,
  `module_srl` bigint(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_srl_name` (`module_srl`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_products`
--

DROP TABLE IF EXISTS `xe_shop_products`;
CREATE TABLE IF NOT EXISTS `xe_shop_products` (
  `product_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `parent_product_srl` bigint(11) DEFAULT NULL,
  `product_type` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` longtext,
  `short_description` varchar(500) DEFAULT NULL,
  `sku` varchar(250) NOT NULL,
  `weight` float DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `friendly_url` varchar(50) DEFAULT NULL,
  `price` float NOT NULL,
  `qty` float DEFAULT NULL,
  `in_stock` char(1) DEFAULT 'N',
  `primary_image_filename` varchar(250) DEFAULT NULL,
  `related_products` varchar(500) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `last_update` varchar(14) DEFAULT NULL,
  `discount_price` float DEFAULT NULL,
  `is_featured` char(1) DEFAULT NULL,
  PRIMARY KEY (`product_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_product_attributes`
--

DROP TABLE IF EXISTS `xe_shop_product_attributes`;
CREATE TABLE IF NOT EXISTS `xe_shop_product_attributes` (
  `product_srl` bigint(11) NOT NULL,
  `attribute_srl` bigint(11) NOT NULL,
  `value` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`product_srl`,`attribute_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_product_categories`
--

DROP TABLE IF EXISTS `xe_shop_product_categories`;
CREATE TABLE IF NOT EXISTS `xe_shop_product_categories` (
  `product_srl` bigint(11) NOT NULL,
  `category_srl` bigint(11) NOT NULL,
  PRIMARY KEY (`product_srl`,`category_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_product_images`
--

DROP TABLE IF EXISTS `xe_shop_product_images`;
CREATE TABLE IF NOT EXISTS `xe_shop_product_images` (
  `image_srl` bigint(11) NOT NULL,
  `product_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `is_primary` char(1) DEFAULT 'N',
  `file_size` bigint(11) NOT NULL DEFAULT '0',
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`image_srl`),
  UNIQUE KEY `unique_shop_product_images` (`product_srl`,`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_shipments`
--

DROP TABLE IF EXISTS `xe_shop_shipments`;
CREATE TABLE IF NOT EXISTS `xe_shop_shipments` (
  `shipment_srl` bigint(11) NOT NULL,
  `order_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `package_number` bigint(20) DEFAULT NULL,
  `comments` text,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`shipment_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_shop_shipping_methods`
--

DROP TABLE IF EXISTS `xe_shop_shipping_methods`;
CREATE TABLE IF NOT EXISTS `xe_shop_shipping_methods` (
  `id` bigint(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `status` bigint(1) DEFAULT '0',
  `props` text,
  `module_srl` bigint(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_srl_name` (`module_srl`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_sites`
--

DROP TABLE IF EXISTS `xe_sites`;
CREATE TABLE IF NOT EXISTS `xe_sites` (
  `site_srl` bigint(11) NOT NULL,
  `index_module_srl` bigint(11) DEFAULT '0',
  `domain` varchar(255) NOT NULL,
  `default_language` varchar(255) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`site_srl`),
  UNIQUE KEY `unique_domain` (`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_site_admin`
--

DROP TABLE IF EXISTS `xe_site_admin`;
CREATE TABLE IF NOT EXISTS `xe_site_admin` (
  `site_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  UNIQUE KEY `idx_site_admin` (`site_srl`,`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_spamfilter_denied_ip`
--

DROP TABLE IF EXISTS `xe_spamfilter_denied_ip`;
CREATE TABLE IF NOT EXISTS `xe_spamfilter_denied_ip` (
  `ipaddress` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_spamfilter_denied_word`
--

DROP TABLE IF EXISTS `xe_spamfilter_denied_word`;
CREATE TABLE IF NOT EXISTS `xe_spamfilter_denied_word` (
  `word` varchar(250) NOT NULL,
  `hit` bigint(20) NOT NULL DEFAULT '0',
  `latest_hit` varchar(14) DEFAULT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`word`),
  KEY `idx_hit` (`hit`),
  KEY `idx_latest_hit` (`latest_hit`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_spamfilter_log`
--

DROP TABLE IF EXISTS `xe_spamfilter_log`;
CREATE TABLE IF NOT EXISTS `xe_spamfilter_log` (
  `spamfilter_log_srl` bigint(11) NOT NULL,
  `ipaddress` varchar(250) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`spamfilter_log_srl`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_tags`
--

DROP TABLE IF EXISTS `xe_tags`;
CREATE TABLE IF NOT EXISTS `xe_tags` (
  `tag_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `tag` varchar(240) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`tag_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_tag` (`document_srl`,`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle`
--

DROP TABLE IF EXISTS `xe_textyle`;
CREATE TABLE IF NOT EXISTS `xe_textyle` (
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `textyle_title` varchar(250) NOT NULL,
  `textyle_content` varchar(250) NOT NULL DEFAULT '',
  `profile_content` text NOT NULL,
  `post_style` varchar(250) NOT NULL DEFAULT 'blog',
  `post_editor_skin` varchar(250) NOT NULL DEFAULT 'dreditor',
  `post_list_count` bigint(2) NOT NULL DEFAULT '30',
  `comment_list_count` bigint(2) NOT NULL DEFAULT '30',
  `comment_editor_skin` varchar(250) DEFAULT 'xpresseditor',
  `comment_editor_colorset` varchar(250) DEFAULT 'white_text_usehtml',
  `guestbook_list_count` bigint(20) NOT NULL DEFAULT '11',
  `guestbook_editor_skin` varchar(250) DEFAULT 'xpresseditor',
  `guestbook_editor_colorset` varchar(250) DEFAULT 'white_text_usehtml',
  `input_email` char(1) DEFAULT 'R',
  `input_website` char(1) DEFAULT 'R',
  `post_use_prefix` char(1) DEFAULT 'N',
  `post_use_suffix` char(1) DEFAULT 'N',
  `post_prefix` text NOT NULL,
  `post_suffix` text NOT NULL,
  `timezone` varchar(10) DEFAULT '+0900',
  `subscription_date` varchar(14) NOT NULL DEFAULT '',
  `regdate` varchar(14) NOT NULL,
  PRIMARY KEY (`module_srl`),
  KEY `idx_member_srl` (`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_api`
--

DROP TABLE IF EXISTS `xe_textyle_api`;
CREATE TABLE IF NOT EXISTS `xe_textyle_api` (
  `module_srl` bigint(11) NOT NULL,
  `api_srl` bigint(11) NOT NULL,
  `blogapi_service` varchar(250) NOT NULL,
  `blogapi_host_provider` varchar(250) NOT NULL,
  `blogapi_type` varchar(50) NOT NULL,
  `blogapi_site_url` varchar(250) NOT NULL,
  `blogapi_site_title` varchar(250) NOT NULL,
  `blogapi_url` varchar(250) NOT NULL,
  `blogapi_user_id` varchar(250) NOT NULL,
  `blogapi_password` varchar(250) NOT NULL,
  `publishded` bigint(11) DEFAULT '0',
  `enable` char(1) DEFAULT 'Y',
  `regdate` varchar(14) NOT NULL,
  `blogapi_blogid` varchar(250) DEFAULT NULL,
  KEY `idx_api` (`module_srl`,`api_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_blogapi_logs`
--

DROP TABLE IF EXISTS `xe_textyle_blogapi_logs`;
CREATE TABLE IF NOT EXISTS `xe_textyle_blogapi_logs` (
  `textyle_blogapi_logs_srl` bigint(11) NOT NULL,
  `document_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `blogapi_url` varchar(250) NOT NULL,
  `blogapi_id` varchar(50) NOT NULL,
  `success` char(1) NOT NULL DEFAULT 'N',
  `regdate` varchar(14) NOT NULL,
  PRIMARY KEY (`textyle_blogapi_logs_srl`),
  KEY `idx_document_srl` (`document_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_blogapi_services`
--

DROP TABLE IF EXISTS `xe_textyle_blogapi_services`;
CREATE TABLE IF NOT EXISTS `xe_textyle_blogapi_services` (
  `textyle_blogapi_services_srl` bigint(11) NOT NULL,
  `service_name` varchar(50) NOT NULL,
  `api_type` varchar(50) NOT NULL,
  `url_description` varchar(50) NOT NULL,
  `id_description` varchar(50) NOT NULL,
  `password_description` varchar(50) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`textyle_blogapi_services_srl`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_deny`
--

DROP TABLE IF EXISTS `xe_textyle_deny`;
CREATE TABLE IF NOT EXISTS `xe_textyle_deny` (
  `textyle_deny_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `deny_type` varchar(1) NOT NULL,
  `deny_content` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`textyle_deny_srl`),
  KEY `module_srl` (`module_srl`),
  KEY `idx_deny` (`deny_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_export`
--

DROP TABLE IF EXISTS `xe_textyle_export`;
CREATE TABLE IF NOT EXISTS `xe_textyle_export` (
  `site_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `export_status` char(1) NOT NULL DEFAULT 'R',
  `export_type` varchar(10) NOT NULL DEFAULT 'ttxml',
  `export_file` varchar(255) DEFAULT NULL,
  `regdate` varchar(14) NOT NULL,
  `export_date` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`site_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_member_srl` (`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_extra_menu`
--

DROP TABLE IF EXISTS `xe_textyle_extra_menu`;
CREATE TABLE IF NOT EXISTS `xe_textyle_extra_menu` (
  `module_srl` bigint(11) NOT NULL,
  `site_srl` bigint(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `type` varchar(250) NOT NULL,
  `list_order` bigint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_srl`),
  KEY `idx_site_srl` (`site_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_guestbook`
--

DROP TABLE IF EXISTS `xe_textyle_guestbook`;
CREATE TABLE IF NOT EXISTS `xe_textyle_guestbook` (
  `textyle_guestbook_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `parent_srl` bigint(11) NOT NULL DEFAULT '0',
  `member_srl` bigint(11) NOT NULL,
  `password` varchar(60) DEFAULT NULL,
  `user_name` varchar(80) NOT NULL,
  `user_id` varchar(80) NOT NULL,
  `nick_name` varchar(80) NOT NULL,
  `email_address` varchar(250) NOT NULL,
  `homepage` varchar(250) NOT NULL,
  `content` longtext NOT NULL,
  `regdate` varchar(14) NOT NULL,
  `last_update` varchar(14) NOT NULL,
  `is_secret` bigint(1) NOT NULL DEFAULT '-1',
  `list_order` bigint(11) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  PRIMARY KEY (`textyle_guestbook_srl`),
  KEY `idx_textyle_srl` (`module_srl`),
  KEY `idx_member_srl` (`member_srl`),
  KEY `idx_list_order` (`list_order`),
  KEY `idx_ipaddress` (`ipaddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_publish_logs`
--

DROP TABLE IF EXISTS `xe_textyle_publish_logs`;
CREATE TABLE IF NOT EXISTS `xe_textyle_publish_logs` (
  `document_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `logs` text,
  PRIMARY KEY (`document_srl`),
  KEY `idx_module_srl` (`module_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_referers`
--

DROP TABLE IF EXISTS `xe_textyle_referers`;
CREATE TABLE IF NOT EXISTS `xe_textyle_referers` (
  `module_srl` bigint(11) NOT NULL,
  `textyle_referer_srl` bigint(11) NOT NULL,
  `textyle_host_srl` bigint(11) NOT NULL,
  `document_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) NOT NULL,
  `visitor` bigint(11) NOT NULL,
  `referer_url` text NOT NULL,
  `link_word` varchar(250) NOT NULL,
  PRIMARY KEY (`textyle_referer_srl`),
  KEY `module_srl` (`module_srl`),
  KEY `referer_srl` (`textyle_host_srl`,`document_srl`,`regdate`),
  KEY `link_word` (`link_word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_referer_hosts`
--

DROP TABLE IF EXISTS `xe_textyle_referer_hosts`;
CREATE TABLE IF NOT EXISTS `xe_textyle_referer_hosts` (
  `textyle_host_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `document_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) NOT NULL,
  `visitor` bigint(11) NOT NULL,
  `host` varchar(250) NOT NULL,
  PRIMARY KEY (`textyle_host_srl`),
  KEY `host_srl` (`module_srl`,`document_srl`,`regdate`),
  KEY `host_url` (`host`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_subscription`
--

DROP TABLE IF EXISTS `xe_textyle_subscription`;
CREATE TABLE IF NOT EXISTS `xe_textyle_subscription` (
  `document_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `publish_date` varchar(14) NOT NULL,
  PRIMARY KEY (`document_srl`),
  KEY `idx_module_srl` (`module_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_textyle_supporter`
--

DROP TABLE IF EXISTS `xe_textyle_supporter`;
CREATE TABLE IF NOT EXISTS `xe_textyle_supporter` (
  `textyle_supporter_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL,
  `member_srl` bigint(11) NOT NULL,
  `nick_name` varchar(80) NOT NULL,
  `homepage` varchar(255) NOT NULL,
  `comment_count` bigint(11) NOT NULL DEFAULT '0',
  `trackback_count` bigint(11) NOT NULL DEFAULT '0',
  `guestbook_count` bigint(11) NOT NULL DEFAULT '0',
  `total_count` bigint(11) NOT NULL DEFAULT '0',
  `regdate` char(6) NOT NULL DEFAULT '',
  KEY `idx_textyle_srl` (`textyle_supporter_srl`,`module_srl`,`total_count`),
  KEY `idx_textyle_member_srl` (`member_srl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_trackbacks`
--

DROP TABLE IF EXISTS `xe_trackbacks`;
CREATE TABLE IF NOT EXISTS `xe_trackbacks` (
  `trackback_srl` bigint(11) NOT NULL,
  `module_srl` bigint(11) NOT NULL DEFAULT '0',
  `document_srl` bigint(11) NOT NULL DEFAULT '0',
  `url` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `blog_name` varchar(250) NOT NULL,
  `excerpt` text NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `list_order` bigint(11) NOT NULL,
  PRIMARY KEY (`trackback_srl`),
  KEY `idx_module_srl` (`module_srl`),
  KEY `idx_document_srl` (`document_srl`),
  KEY `idx_regdate` (`regdate`),
  KEY `idx_ipaddress` (`ipaddress`),
  KEY `idx_list_order` (`list_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structura de tabel pentru tabelul `xe_trash`
--

DROP TABLE IF EXISTS `xe_trash`;
CREATE TABLE IF NOT EXISTS `xe_trash` (
  `trash_srl` bigint(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `origin_module` varchar(250) NOT NULL DEFAULT 'document',
  `serialized_object` longtext NOT NULL,
  `description` text,
  `ipaddress` varchar(128) NOT NULL,
  `remover_srl` bigint(11) NOT NULL,
  `regdate` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`trash_srl`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Salvarea datelor din tabel `xe_layouts`
--

INSERT INTO `xe_layouts` (`layout_srl`, `site_srl`, `layout`, `title`, `extra_vars`, `layout_path`, `module_srl`, `regdate`, `layout_type`) VALUES
(64, 0, 'xe_official', 'welcome_layout', 'O:8:"stdClass":4:{s:8:"colorset";s:7:"default";s:9:"main_menu";i:61;s:11:"bottom_menu";i:61;s:14:"menu_name_list";a:1:{i:61;s:12:"welcome_menu";}}', NULL, 0, '20120831171005', 'P');

--
-- Salvarea datelor din tabel `xe_member`
--

INSERT INTO `xe_member` (`member_srl`, `user_id`, `email_address`, `password`, `email_id`, `email_host`, `user_name`, `nick_name`, `find_account_question`, `find_account_answer`, `homepage`, `blog`, `birthday`, `allow_mailing`, `allow_message`, `denied`, `limit_date`, `regdate`, `last_login`, `change_password_date`, `is_admin`, `description`, `extra_vars`, `list_order`) VALUES
(4, 'admin', 'corina.udrescu@gmail.com', '23e5484cb88f3c07bcce2920a5e6a2a7', 'corina.udrescu', 'gmail.com', '4', 'admin', NULL, NULL, '', '', NULL, 'N', 'Y', 'N', NULL, '20120831171001', '20120904141721', '20120831171001', 'Y', NULL, NULL, -4);


--
-- Salvarea datelor din tabel `xe_member_group`
--

INSERT INTO `xe_member_group` (`site_srl`, `group_srl`, `list_order`, `title`, `regdate`, `is_default`, `is_admin`, `image_mark`, `description`) VALUES
(0, 1, 1, 'Managing Group', '20120831171001', 'N', 'Y', '', ''),
(0, 2, 2, 'Associate Member', '20120831171001', 'Y', 'N', '', ''),
(0, 3, 3, 'Regular Member', '20120831171001', 'N', 'N', '', '');

--
-- Salvarea datelor din tabel `xe_member_group_member`
--

INSERT INTO `xe_member_group_member` (`site_srl`, `group_srl`, `member_srl`, `regdate`) VALUES
(0, 1, 4, '20120831171001');


INSERT INTO `xe_sites` (`site_srl`, `index_module_srl`, `domain`, `default_language`, `regdate`) VALUES
(0, 65, 'xe150_shop/', 'en', '20120831170955');
