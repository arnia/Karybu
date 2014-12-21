<?php
    /**
	 * adminAdminView class
	 * Admin view class of admin module
	 *
	 * @author Arnia (dev@karybu.org)
	 * @package /modules/admin
	 * @version 0.1
	 */
    class adminAdminView extends admin {
		/**
		 * layout list
		 * @var array
		 */
		var $layout_list;
		/**
		 * easy install check file
		 * @var array
		 */
		var $easyinstallCheckFile = './files/env/easyinstall_last';

        /**
         * Initilization
         * @return void
         */
        function init() {
            // forbit access if the user is not an administrator
            $oMemberModel = &getModel('member');
            $logged_info = $oMemberModel->getLoggedInfo();
            if(empty($logged_info->is_admin) || $logged_info->is_admin != 'Y') {
                return $this->stop("msg_is_not_administrator");
            }

            // change into administration layout
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setLayoutPath($this->getTemplatePath());
            $this->setLayoutFile('layout.html');
            Context::addBodyClass('x');

            $oAdminView = & getAdminView('admin');
            $oAdminView->makeDashboardSitemap();
            $oAdminView->makeFavoriteList();
            $this->initAdminMenu();
			$this->makeGnbUrl();

            // Retrieve the list of installed modules

            $db_info = Context::getDBInfo();

            Context::set('time_zone_list', $GLOBALS['time_zone']);
            Context::set('time_zone', $GLOBALS['_time_zone']);
            Context::set('use_rewrite', (isset($db_info->use_rewrite) && $db_info->use_rewrite == 'Y') ? 'Y' : 'N');
            Context::set('use_sso', (isset($db_info->use_sso) && $db_info->use_sso=='Y') ? 'Y' : 'N');
            Context::set('use_html5', (isset($db_info->use_html5) && $db_info->use_html5=='Y') ? 'Y' : 'N');
            Context::set('use_spaceremover', isset($db_info->use_spaceremover) ? $db_info->use_spaceremover : 'Y');//not use
            Context::set('qmail_compatibility', (isset($db_info->qmail_compatibility) && $db_info->qmail_compatibility=='Y') ? 'Y' : 'N');
            Context::set('use_db_session', (isset($db_info->use_db_session) && $db_info->use_db_session=='N') ? 'N' : 'Y');
            Context::set('use_mobile_view', (isset($db_info->use_mobile_view) && $db_info->use_mobile_view) =='Y' ? 'Y' : 'N');
            Context::set('use_ssl', (isset($db_info->use_ssl) && $db_info->use_ssl) ? $db_info->use_ssl:"none");
			Context::set('use_cdn', (isset($db_info->use_cdn) && $db_info->use_cdn) ? $db_info->use_cdn:"none");
            if(isset($db_info->http_port)) {
                Context::set('http_port', $db_info->http_port);
            }
            if(isset($db_info->https_port)) {
                Context::set('https_port', $db_info->https_port);
            }

			$this->showSendEnv();
			$this->checkEasyinstall();
        }

		/**
		 * check easy install
		 * @return void
		 */
		function checkEasyinstall()
		{
			$lastTime = (int)FileHandler::readFile($this->easyinstallCheckFile);
			if ($lastTime > time() - 60*60*24*30) {
                return;
            }

			$oAutoinstallModel = &getModel('autoinstall');
			$params = array();
			$params["act"] = "getResourceapiLastupdate";
            $generator = new XmlGenerater();
			$body = $generator->generate($params);
			$buff = FileHandler::getRemoteResource(_KARYBU_DOWNLOAD_SERVER_, $body, 3, "POST", "application/xml");
			$xml_lUpdate = new XmlParser();
			$lUpdateDoc = $xml_lUpdate->parse($buff);
			$updateDate = isset($lUpdateDoc->response->updatedate->body) ? $lUpdateDoc->response->updatedate->body : null;

			if (!$updateDate) {
				$this->_markingCheckEasyinstall();
				return;
			}

			$item = $oAutoinstallModel->getLatestPackage();
			if(!$item || $item->updatedate < $updateDate) {
				$oController = &getAdminController('autoinstall');
				$oController->_updateinfo();
			}
			$this->_markingCheckEasyinstall();
		}

		/**
		 * update easy install file content
		 * @return void
		 */
		function _markingCheckEasyinstall()
		{
			$currentTime = time();
			FileHandler::writeFile($this->easyinstallCheckFile, $currentTime);
		}

        /*
         * Include dashboard sitemap on all views of admin
         */
        function makeDashboardSitemap()
        {
            $oAdminAdminModel = getAdminModel('admin');
            $main_menu = $oAdminAdminModel->getMainMenuItems();
            Context::set('main_menu',$main_menu);
        }

        /*
         * set favorite list to context for display on all admin views
         */
        function makeFavoriteList(){
            // Get list of favorite
            $oAdminAdminModel = &getAdminModel('admin');
            $output = $oAdminAdminModel->getFavoriteList(0, true);
            Context::set('favorite_list', $output->get('favoriteList'));
        }

		/**
		 * Include admin menu php file and make menu url
		 * Setting admin logo, newest news setting
		 * @return void
		 */
		function makeGnbUrl($module = 'admin')
		{
			global $lang;

			$oAdminAdminModel   = &getAdminModel('admin');
			$lang->menu_gnb_sub = $oAdminAdminModel->getAdminMenuLang();

			$oMenuAdminModel = &getAdminModel('menu');
			$menu_info = $oMenuAdminModel->getMenuByTitle('__KARYBU_ADMIN__');
			Context::set('admin_menu_srl', $menu_info->menu_srl);

			if(!is_readable($menu_info->php_file)) {
                return;
            }

			include $menu_info->php_file;

            $oModuleModel = &getModel('module');
			$moduleActionInfo = $oModuleModel->getModuleActionXml($module);

			$currentAct   = Context::get('act');
			$subMenuTitle = '';
            if (isset($moduleActionInfo->menu)) {
                foreach((array)$moduleActionInfo->menu as $key=>$value) {
                    if(isset($value->acts) && is_array($value->acts) && in_array($currentAct, $value->acts)) {
                        $subMenuTitle = $value->title;
                        break;
                    }
                }
            }

			$parentSrl = 0;
            if (isset($menu->list)) {
                foreach((array)$menu->list as $parentKey=>$parentMenu) {
                    if(!is_array($parentMenu['list']) || !count($parentMenu['list'])) continue;
                    if($parentMenu['href'] == '#' && count($parentMenu['list'])) {
                        $firstChild = current($parentMenu['list']);
                        $menu->list[$parentKey]['href'] = $firstChild['href'];
                    }

                    foreach($parentMenu['list'] as $childKey=>$childMenu) {
                        if($subMenuTitle == $childMenu['text']) {
                            $parentSrl = $childMenu['parent_srl'];
                            break;
                        }
                    }
                }
            }

			// Admin logo, title setup
            $gnbTitleInfo = new stdClass();
			$objConfig = $oModuleModel->getModuleConfig('admin');
			$gnbTitleInfo->adminTitle = isset($objConfig->adminTitle) ? $objConfig->adminTitle:'Karybu Admin';
			$gnbTitleInfo->adminLogo  = isset($objConfig->adminLogo) ? $objConfig->adminLogo:'modules/admin/tpl/img/karybu.png';

			$browserTitle = ($subMenuTitle ? $subMenuTitle : 'Dashboard').' - '.$gnbTitleInfo->adminTitle;

			$this->makeFavoriteList();

			// Retrieve recent news and set them into context,
			// move from index method, because use in admin footer
            // TODO refactor news for karybu
			/*$newest_news_url = sprintf("http://news.karybu.org/%s/news.php?version=%s&package=%s", _KARYBU_LOCATION_, __KARYBU_VERSION__, _KARYBU_PACKAGE_);
			$cache_file = sprintf("%sfiles/cache/newest_news.%s.cache.php", _KARYBU_PATH_, _KARYBU_LOCATION_);
			if(!file_exists($cache_file) || filemtime($cache_file)+ 60*60 < time()) {
				// Considering if data cannot be retrieved due to network problem, modify filemtime to prevent trying to reload again when refreshing administration page
				// Ensure to access the administration page even though news cannot be displayed
				FileHandler::writeFile($cache_file,'');
				FileHandler::getRemoteFile($newest_news_url, $cache_file, null, 1, 'GET', 'text/html', array('REQUESTURL'=>getFullUrl('')));
			}

			if(file_exists($cache_file)) {
				$oXml = new XmlParser();
				$buff = $oXml->parse(FileHandler::readFile($cache_file));

				$item = $buff->zbxe_news->item;
				if($item) {
					if(!is_array($item)) $item = array($item);

					foreach($item as $key => $val) {
						$obj = null;
						$obj->title = $val->body;
						$obj->date = $val->attrs->date;
						$obj->url = $val->attrs->url;
						$news[] = $obj;
					}
					Context::set('news', $news);
					if(isset($news) && is_array($news))
					{
						Context::set('latestVersion', array_shift($news));
					}
				}
				Context::set('released_version', $buff->zbxe_news->attrs->released_version);
				Context::set('download_link', $buff->zbxe_news->attrs->download_link);
			}*/
            $currentUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $activeNode = null;
            foreach($menu->list as $k=>$item){
                if(strpos($currentUrl,$item['href']) !== false) {
                    if($item['text'] == $lang->control_panel){
                        $delta = str_replace($item['href'],'',$currentUrl);
                        $delta = str_replace("http://".$_SERVER['HTTP_HOST'],'',$delta);
                        if($delta == '/' || $delta == '/index.php'){
                            $activeNode = $item['node_srl'];
                        }
                    }else {
                        $activeNode = $item['node_srl'];
                    }
                }
                if (!is_null($activeNode)){
                    break;
                }
            }
            if(isset($menu->list[$parentSrl])) {
                $page_title = $menu->list[$parentSrl]['text'];
                Context::set('class_name',isset($menu->list[$parentSrl]['class_name'])? $menu->list[$parentSrl]['class_name'] : '');
            } elseif ($parentSrl == 0 && isset($menu->list[$activeNode])){
                $page_title = $menu->list[$activeNode]['text'];
                Context::set('class_name',isset($menu->list[$activeNode]['class_name'])? $menu->list[$activeNode]['class_name'] : '');
            }
            else{
                $page_title = $module;
            }
            $page_title = str_replace('_',' ',ucfirst($page_title));
            Context::set('activeNode', $activeNode);
            Context::set('subMenuTitle', $subMenuTitle);
			Context::set('gnbUrlList',   $menu->list);
			Context::set('page_title',   $page_title);
			Context::set('parentSrl',    $parentSrl);
			Context::set('gnb_title_info', $gnbTitleInfo);
            Context::setBrowserTitle($browserTitle);
		}

		/**
		 * Display Super Admin Dashboard
		 * @return void
		 */
        function dispAdminIndex() {
            // Get statistics
            $args = new stdClass();
            $args->date = date("Ymd000000", time()-60*60*24);
            $today = date("Ymd");

            // Member Status
            $status = new stdClass();
            $status->member = new stdClass();
			$oMemberAdminModel = &getAdminModel('member');
			$status->member->todayCount = $oMemberAdminModel->getMemberCountByDate($today);
			$status->member->totalCount = $oMemberAdminModel->getMemberCountByDate();

            // Document Status
            $status->document = new stdClass();
			$oDocumentAdminModel = &getAdminModel('document');
			$statusList = array('PUBLIC', 'SECRET');
			$status->document->todayCount = $oDocumentAdminModel->getDocumentCountByDate($today, array(), $statusList);
			$status->document->totalCount = $oDocumentAdminModel->getDocumentCountByDate('', array(), $statusList);

            // Comment Status
			$oCommentModel = &getModel('comment');
            $status->comment = new stdClass();
			$status->comment->todayCount = $oCommentModel->getCommentCountByDate($today);
			$status->comment->totalCount = $oCommentModel->getCommentCountByDate();

            // Trackback Status
			$oTrackbackAdminModel = &getAdminModel('trackback');
            $status->trackback = new stdClass();
			$status->trackback->todayCount = $oTrackbackAdminModel->getTrackbackCountByDate($today);
			$status->trackback->totalCount = $oTrackbackAdminModel->getTrackbackCountByDate();

            // Attached files Status
			$oFileAdminModel = &getAdminModel('file');
            $status->file = new stdClass();
			$status->file->todayCount = $oFileAdminModel->getFilesCountByDate($today);
			$status->file->totalCount = $oFileAdminModel->getFilesCountByDate();

            $oAdminAdminModel = getAdminModel('admin');
            $visitors = $oAdminAdminModel->getWeeklyVisitors();
            $status->week_max = $visitors->week_max;
            $status->week = $visitors->week;
            $status->thisWeekSum = $visitors->thisWeekSum;
            $status->total_visitor = $visitors->total_visitor;
            $status->visitor = $visitors->visitor;

            Context::set('status', $status);

            // Latest Document
			$oDocumentModel = &getModel('document');
			$columnList = array('document_srl', 'module_srl', 'category_srl', 'title', 'nick_name', 'member_srl');
			$args->list_count = 5;;
            $args->page_count = 5;
			$output = $oDocumentModel->getDocumentList($args, false, false, $columnList);
            Context::set('latestDocumentList', $output->data);
			$security = new Security();
			$security->encodeHTML('latestDocumentList..variables.nick_name');
			unset($args, $output, $columnList);
            $args = new stdClass();
			// Latest Comment
			$oCommentModel = &getModel('comment');
			$columnList = array('comment_srl', 'module_srl', 'document_srl', 'content', 'nick_name', 'member_srl');
			$args->list_count = 5;
			$output = $oCommentModel->getNewestCommentList($args, $columnList);
			if(is_array($output)) {
				foreach($output AS $key=>$value) {
					$value->content = strip_tags($value->content);
				}
			}
            Context::set('latestCommentList', $output);
			unset($args, $output, $columnList);
            $args = new stdClass();
			//Latest Trackback
			$oTrackbackModel = &getModel('trackback');
			$columnList = array();
			$args->list_count = 5;
			$output =$oTrackbackModel->getNewestTrackbackList($args);
            Context::set('latestTrackbackList', $output->data);
			unset($args, $output, $columnList);

            // Get list of modules
            $oModuleModel = &getModel('module');
            $module_list = $oModuleModel->getModuleList();
			if(is_array($module_list)) {
				$needUpdate = false;
				$addTables = false;
				foreach($module_list AS $key=>$value) {
					if($value->need_install) {
						$addTables = true;
					}
					if($value->need_update) {
						$needUpdate = true;
					}
				}
			}
            Context::set('module_list', $module_list);
            Context::set('needUpdate', $needUpdate);
            Context::set('addTables', $addTables);
            Context::set('needUpdate', $needUpdate);

			// gathering enviroment check
			$mainVersion = join('.', array_slice(explode('.', __KARYBU_VERSION__), 0, 2));
			$path = FileHandler::getRealPath('./files/env/'.$mainVersion);
			$isEnviromentGatheringAgreement = false;
			if(file_exists($path)) $isEnviromentGatheringAgreement = true;
			Context::set('isEnviromentGatheringAgreement', $isEnviromentGatheringAgreement);
            Context::set('layout','none');

            $this->setTemplateFile('index');
        }

		/**
		 * Display Configuration(settings) page
		 * @return void
		 */
        function dispAdminConfigGeneral() {
            global $lang;
		    Context::loadLang('modules/install/lang');

            $db_info = Context::getDBInfo();

            Context::set('selected_lang', $db_info->lang_type);

			Context::set('default_url', $db_info->default_url);
            Context::set('langs', Context::loadLangSupported());

            Context::set('lang_selected', Context::loadLangSelected());
            if (isset($db_info->admin_ip_list)) {
			    $admin_ip_list = preg_replace("/[,]+/","\r\n",$db_info->admin_ip_list);
            }
            else{
                $admin_ip_list = null;
            }
            Context::set('admin_ip_list', $admin_ip_list);

			$oAdminModel = &getAdminModel('admin');
			$favicon_url = $oAdminModel->getFaviconUrl();
			$mobicon_url = $oAdminModel->getMobileIconUrl();
            Context::set('favicon_url', $favicon_url);
			Context::set('mobicon_url', $mobicon_url);

			$oDocumentModel = &getModel('document');
			$config = $oDocumentModel->getDocumentConfig();
            if (!empty($config->thumbnail_type)) {
       		    Context::set('thumbnail_type',$config->thumbnail_type);
            }
            else {
                Context::set('thumbnail_type',null);
            }
			
			Context::set('IP',$_SERVER['REMOTE_ADDR']);
			
			$oModuleModel = &getModel('module');
			$config = $oModuleModel->getModuleConfig('module');
            if (!empty($config->htmlFooter)) {
       		    Context::set('htmlFooter',htmlspecialchars($config->htmlFooter));
            }
            else {
                Context::set('htmlFooter',null);
            }

			$columnList = array('modules.mid', 'modules.browser_title', 'sites.index_module_srl');
			$start_module = $oModuleModel->getSiteInfo(0, $columnList);
            Context::set('start_module', $start_module);

            $environments = \Karybu\Environment\Environment::getEnvironments();
            //set translated titles
            foreach ($environments as $key => $env) {
                $langKey = $env->getLanguageKey();
                $environments[$key]->setTitle($lang->$langKey);
            }
            Context::set('environments', $environments);
            Context::set('debug_levels', array(
                'debug'     => $lang->debug_debug,
                'info'      => $lang->debug_info,
                'warning'   => $lang->debug_warning,
                'error'     => $lang->debug_error,
                'critical'  => $lang->debug_critical,
                'alert'     => $lang->debug_alert
            ));
            Context::set('debug_handlers', array(
                'files'     => $lang->debug_handler_files,
                'chrome'    => $lang->debug_handler_chrome,
                'firebug'   => $lang->debug_handler_firebug,
            ));
            $debugValues = array();
            $toolbarDisable = array();
            //get current values
            foreach ($environments as $env) {
                $debugValues[$env->getCode()] = array();
                $configFile = "files/config/config_{$env->getCode()}.yml";
                //fallback for installer
                if (!file_exists($configFile)) {
                    $configFile = "config/config_{$env->getCode()}.base.yml";
                }
                if (file_exists($configFile)) {
                    $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
                    $extension = new \Karybu\DependencyInjection\Dummy\Extension();
                    $extension->setNamespace('debug');
                    $container->registerExtension($extension);
                    $loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \Symfony\Component\Config\FileLocator(array(_KARYBU_PATH_)));
                    $loader->load($configFile);
                    $extensionConfig = $container->getExtensionConfig('debug');
                    if (isset($extensionConfig[0])) {
                        //merge all config settings
                        $debugValues[$env->getCode()] = call_user_func_array('array_merge', $extensionConfig);
                    }
                    $gzEnabled = $container->getParameterBag()->get('cms.gz_encoding');
                    if ($gzEnabled) {
                        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')!==false &&
                            function_exists('ob_gzhandler') &&
                            extension_loaded('zlib')) {
                            $toolbarDisable[$env->getCode()] = true;
                            $debugValues[$env->getCode()]['toolbar'] = false;
                        }
                    }
                }
            }
            Context::set('current_env', $environments[KARYBU_ENVIRONMENT]);
            Context::set('toolbar_disable', $toolbarDisable);

            Context::set('debug_values', $debugValues);
            $this->setTemplateFile('config_general');
			$security = new Security();
			$security->encodeHTML('news..', 'released_version', 'download_link', 'selected_lang', 'module_list..', 'module_list..author..', 'addon_list..', 'addon_list..author..', 'start_module.');
        }

		/**
		 * Display FTP Configuration(settings) page
		 * @return void
		 */
        function dispAdminConfigFtp() {
		    Context::loadLang('modules/install/lang');

            $ftp_info = Context::getFTPInfo();
            Context::set('ftp_info', $ftp_info);
			Context::set('sftp_support', function_exists('ssh2_sftp'));

            $this->setTemplateFile('config_ftp');

//			$security = new Security();
//			$security->encodeHTML('ftp_info..');

        }

		/**
		 * Display Admin Menu Configuration(settings) page
		 * @return void
		 */
		function dispAdminSetup()
		{
			$oModuleModel = &getModel('module');
			$configObject = $oModuleModel->getModuleConfig('admin');

			$oMenuAdminModel = &getAdminModel('menu');
			$output = $oMenuAdminModel->getMenuByTitle('__KARYBU_ADMIN__');

			Context::set('menu_srl', $output->menu_srl);
			Context::set('menu_title', $output->title);
			Context::set('config_object', $configObject);
            $this->setTemplateFile('admin_setup');
		}


		/**
         * //TODO collet Karybu Server Information
		 * @return void
		 */
		function showSendEnv() {
			if(Context::getResponseMethod() != 'HTML') {
                return;
            }

			$server = 'http://collect.karybu.org/env/img.php?';
			$path = './files/env/';
			$install_env = $path . 'install';
			$mainVersion = join('.', array_slice(explode('.', __KARYBU_VERSION__), 0, 2));

			if(file_exists(FileHandler::getRealPath($install_env))) {
				$oAdminAdminModel = &getAdminModel('admin');
				$params = $oAdminAdminModel->getEnv('INSTALL');
				$img = sprintf('<img src="%s" alt="" style="height:0px;width:0px" />', $server.$params);
				Context::addHtmlFooter($img);

				FileHandler::removeDir($path);
				FileHandler::writeFile($path.$mainVersion,'1');

			}
			else if(isset($_SESSION['enviroment_gather']) && !file_exists(FileHandler::getRealPath($path.$mainVersion)))
			{
				if($_SESSION['enviroment_gather']=='Y')
				{
					$oAdminAdminModel = &getAdminModel('admin');
					$params = $oAdminAdminModel->getEnv();
					$img = sprintf('<img src="%s" alt="" style="height:0px;width:0px" />', $server.$params);
					Context::addHtmlFooter($img);
				}

				FileHandler::removeDir($path);
				FileHandler::writeFile($path.$mainVersion,'1');
				unset($_SESSION['enviroment_gather']);
			}
		}

        function initAdminMenu()
        {
            // admin menu check
            if (Context::isInstalled()) {
                $oMenuAdminModel = & getAdminModel('menu');
                $output = $oMenuAdminModel->getMenuByTitle('__KARYBU_ADMIN__');
                if (empty($output->menu_srl)) {
                    $oAdminClass = & getClass('admin');
                    $oAdminClass->createXeAdminMenu();
                } else {
                    if (!is_readable($output->php_file)) {
                        $oMenuAdminController = & getAdminController('menu');
                        $oMenuAdminController->makeXmlFile($output->menu_srl);
                    }
                }
            }
        }

		/**
		 * Display Admin theme Configuration(settings) page
		 * @return void
		 */
		function dispAdminTheme(){
			// choice theme file
			$theme_file = _KARYBU_PATH_.'files/theme/theme_info.php';
			if(is_readable($theme_file)){
				include($theme_file);
				Context::set('current_layout', $theme_info->layout);
				Context::set('theme_info', $theme_info);
			}
			else{
				$oModuleModel = &getModel('module');
				$default_mid = $oModuleModel->getDefaultMid();
				Context::set('current_layout', $default_mid->layout_srl);
			}

			// layout list
			$oLayoutModel = &getModel('layout');


			$oAdminModel = &getAdminModel('admin');
			$theme_list = $oAdminModel->getThemeList();
			$layouts = $oLayoutModel->getLayoutList(0);
			$layout_list = array();
			if (is_array($layouts)){
				foreach($layouts as $val){
					unset($layout_info);
					$layout_info = $oLayoutModel->getLayout($val->layout_srl);
					if (!$layout_info) continue;
					$layout_parse = explode('|@|', $layout_info->layout);
					if (count($layout_parse) == 2){
						$thumb_path = sprintf('themes/%s/layouts/%s/thumbnail.png', $layout_parse[0], $layout_parse[1]);
					}
					else{
						$thumb_path = './layouts/'.$layout_info->layout.'/thumbnail.png';
					}
					$layout_info->thumbnail = (is_readable('./'.$thumb_path))?getFullUrl().$thumb_path:null;
					$layout_list[] = $layout_info;
				}
			}
			Context::set('theme_list', $theme_list);
			Context::set('layout_list', $layout_list);


			$module_list = $oAdminModel->getModulesSkinList();
			Context::set('module_list', $module_list);

			$this->setTemplateFile('theme');
		}
    }
