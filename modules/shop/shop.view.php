<?php

/**
 * @class  shopView
 * @author Arnia (xe_dev@arnia.ro)
 *  shop module View class
 **/

class shopView extends shop {

    /** @var shopModel */
    protected $model;
    /** @var shopInfo */
    protected $shop;

    /**
	 * Initialization
	 **/
	public function init() {
		$this->model = getModel('shop');
        $this->shop = $this->model->getShop($this->module_info->module_srl);
        if(preg_match("/ShopTool/",$this->act) ) {
			$this->initTool($this);

		} else {
			$this->initService($this);
		}
	}

	/**
	 * Shop common init
	 **/
	public function initCommon($is_other_module = FALSE){
		if(!$this->checkXECoreVersion('1.4.3')) return $this->stop(sprintf(Context::getLang('msg_requried_version'),'1.4.3'));

		$oShopModel = getModel('shop');
		$oShopController = getController('shop');
		$oModuleModel = getModel('module');

		$site_module_info = Context::get('site_module_info');
		if(!$this->module_srl) {
			$site_module_info = Context::get('site_module_info');
			$site_srl = $site_module_info->site_srl;
			if($site_srl) {
				$this->module_srl = $site_module_info->index_module_srl;
				$this->module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
				if (!$is_other_module) {
					Context::set('module_info',$this->module_info);
					Context::set('mid',$this->module_info->mid);
					Context::set('current_module_info',$this->module_info);
				}
			}
		}

		if(!$this->module_info->skin) $this->module_info->skin = $this->skin;

		$preview_skin = Context::get('preview_skin');
		if($oModuleModel->isSiteAdmin(Context::get('logged_info'))&&$preview_skin) {
			if(is_dir($this->module_path.'skins/'.$preview_skin)) {
                $shop_config = new stdClass();
				$shop_config->skin = $this->module_info->skin = $preview_skin;
			}
		}

		if (!$is_other_module){
			Context::set('module_info',$this->module_info);
			Context::set('current_module_info', $this->module_info);
		}

		$this->shop = $oShopModel->getShop($this->module_info->module_srl);
		$this->site_srl = $this->shop->site_srl;
		Context::set('shop',$this->shop);

        Context::addHtmlHeader('<link rel="shortcut icon" href="'.$this->shop->getFaviconSrc().'" />');

		if($this->shop->timezone) $GLOBALS['_time_zone'] = $this->shop->timezone;

        Context::set('module', 'shop');


	}

	/**
	 * Shop init tool
	 **/
	public function initTool(&$oModule, $is_other_module = FALSE){
		if (!$oModule) $oModule = $this;

		$this->initCommon($is_other_module);

		$oShopModel = getModel('shop');

		$site_module_info = Context::get('site_module_info');
		$shop = $oShopModel->getShop($site_module_info->index_module_srl);

        Context::set('site_keyword', $site_module_info->domain);

		$info = Context::getDBInfo();

		if ($is_other_module) {
			$oModule->setLayoutPath($this->module_path.'tpl');
			$oModule->setLayoutFile('_tool_layout');
		}else{
			$template_path = sprintf("%stpl",$this->module_path);
			$this->setTemplatePath($template_path);
			$this->setTemplateFile(str_replace('dispShopTool','',$this->act));
		}

		if($_COOKIE['tclnb']) Context::addBodyClass('lnbClose');
		else Context::addBodyClass('lnbToggleOpen');

		// set browser title
		Context::setBrowserTitle($shop->get('browser_title') . ' - admin');
	}

	/**
	 * shop init service
	 **/
	public function initService(&$oModule, $is_other_module = FALSE, $isMobile = FALSE){
		if (!$oModule) $oModule = $this;

        /** @var $oShopModel shopModel */
		$oShopModel = getModel('shop');

		$this->initCommon($is_other_module);

		Context::addJsFile($this->module_path.'tpl/js/shop_service.js');

		$preview_skin = Context::get('preview_skin');
		if(!$isMobile)
		{
			if($is_other_module){
				$path_method = 'setLayoutPath';
				$file_method = 'setLayoutFile';
				$css_path_method = 'getLayoutPath';
				Context::set('shop_mode', 'module');
				Context::set('external_module', $oModule->module);
			}else{
				$path_method = 'setTemplatePath';
				$file_method = 'setTemplateFile';
				$css_path_method = 'getTemplatePath';
			}

			if(!$preview_skin){
				$oShopModel->checkShopPath($this->module_srl, $this->module_info->skin);
				$oModule->{$path_method}($oShopModel->getShopPath($this->module_srl));
			}else{
				$oModule->{$path_method}($this->module_path.'skins/'.$preview_skin);
			}

			$oModule->{$file_method}('shop');
			Context::addCssFile($oModule->{$css_path_method}().'shop.css',TRUE,'all','',100);
		}

		Context::set('root_url', Context::getRequestUri());
		Context::set('home_url', getFullSiteUrl($this->shop->domain));
		Context::set('profile_url', getSiteUrl($this->shop->domain,'','mid',$this->module_info->mid,'act','dispShopProfile'));
		if(Context::get('is_logged')) Context::set('admin_url', getSiteUrl($this->shop->domain,'','mid',$this->module_info->mid,'act','dispShopToolDashboard'));
		else Context::set('admin_url', getSiteUrl($this->shop->domain,'','mid','shop','act','dispShopToolLogin'));
		Context::set('shop_title', $this->shop->get('shop_title'));

		// set browser title
		Context::setBrowserTitle($this->shop->get('browser_title'));

        // Load cart for display on all pages (in header)
        $cartRepo = new CartRepository();
        $logged_info = Context::get('logged_info');
		// If cart doesn't exist, create new one
        $cart = $cartRepo->getCart($this->module_srl, NULL, $logged_info->member_srl, session_id(), TRUE);
        Context::set('cart', $cart);

        // Load cart preview (for ajax cart feature in header)
		$cart_preview = new CartPreview($cart, 3);
		Context::set('cart_preview', $cart_preview);

        // Load menu for display on all pages (in header)
        $shop = Context::get('shop');
        $menus = $shop->getMenus();
        foreach($menus as $menu_key => $menu_srl)
        {
            $menu = new ShopMenu($menu_srl);
            Context::set($menu_key, $menu->getHtml());
        }

        // Load categories for display in search dropdown (header)
        $category_repository = new CategoryRepository();
        $tree = $category_repository->getNavigationCategoriesTree($this->module_srl);
        $flat_tree = $tree->toFlatStructure();
        Context::set('search_categories', $flat_tree);
	}

    /**
     * route
     * @return Object|void
     * @throws ShopException
     */
    public function route()
    {
        try {
            if (!$type = Context::get('type')) throw new ShopException('Routing: no type');
            if (!$identifier = Context::get('identifier')) throw new ShopException('Routing: no identifier');
            if ($type == 'product') {
                $repo = new ProductRepository();
                if (!$product = $repo->getProductByFriendlyUrl($identifier)) throw new ShopException('Product does not exist');
                Context::set('product', $product);
                return $this->dispShopProduct();
            }
            elseif ($type == 'category') {
                $repo = new CategoryRepository();
                if (!$cat = $repo->getCategoryByFriendlyUrl($identifier)) throw new ShopException('Category does not exist');
                Context::set('category', $cat);
                return $this->dispShop();
            }
            else throw new ShopException("Routing: invalid type: $type");
        }
        catch (ShopException $e) {
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopHome'));
            return new Object(-1, $e->getMessage());
        }
    }

    /**
	 * Tool dashboard
	 **/
	public function dispShopToolDashboard(){
		$oCounterModel = getModel('counter');
        /** @var $oShopModel shopModel */
		$oShopModel = getModel('shop');

        //get visitor graph details
		$time = time();
		$w = date("D");
		while(date("D",$time) != "Sun") {
			$time += 60*60*24;
		}
		$time -= 60*60*24;
		while(date("D",$time)!="Sun") {
			$thisWeek[] = date("Ymd",$time);
			$time -= 60*60*24;
		}
		$thisWeek[] = date("Ymd",$time);
		asort($thisWeek);
		$thisWeekCounter = $oCounterModel->getStatus($thisWeek, $this->site_srl);

		$time -= 60*60*24;
		while(date("D",$time)!="Sun") {
			$lastWeek[] = date("Ymd",$time);
			$time -= 60*60*24;
		}
		$lastWeek[] = date("Ymd",$time);
		asort($lastWeek);
		$lastWeekCounter = $oCounterModel->getStatus($lastWeek, $this->site_srl);

		$max = 0;
		foreach($thisWeek as $day) {
			$v = (int)$thisWeekCounter[$day]->unique_visitor;
			if($v && $v>$max) $max = $v;
            $stat = new stdClass();
			$stat->week[date("D",strtotime($day))]->this = $v;
		}
		foreach($lastWeek as $day) {
			$v = (int)$lastWeekCounter[$day]->unique_visitor;
			if($v && $v>$max) $max = $v;
			$stat->week[date("D",strtotime($day))]->last = $v;
		}
		$stat->week_max = $max;
		$idx = 0;
		foreach($stat->week as $key => $val) {
			$_item[] = sprintf("<item id=\"%d\" name=\"%s\" />", $idx, $thisWeek[$idx]);
			$_thisWeek[] = $val->this;
			$_lastWeek[] = $val->last;
			$idx++;
		}

		$buff = '<?xml version="1.0" encoding="utf-8" ?><Graph><gdata title="Shop Counter" id="data2"><fact>'.implode('',$_item).'</fact><subFact>';
		$buff .= '<item id="0"><data name="'.Context::getLang('this_week').'">'.implode('|',$_thisWeek).'</data></item>';
		$buff .= '<item id="1"><data name="'.Context::getLang('last_week').'">'.implode('|',$_lastWeek).'</data></item>';
		$buff .= '</subFact></gdata></Graph>';
		Context::set('xml', $buff);

		$counter = $oCounterModel->getStatus(array(0,date("Ymd")),$this->site_srl);
		$stat->total_visitor = $counter[0]->unique_visitor;
		$stat->visitor = $counter[date("Ymd")]->unique_visitor;

        //get order and sale statistics
        $order_statistics = $oShopModel->getOrderStatistics($this->module_info->module_srl);
        $stat->placed_orders = 0;
        foreach($order_statistics as $stats){
            $stat->placed_orders += $stats->count;
            switch($stats->order_status){
                case Order::ORDER_STATUS_COMPLETED:
                    $stat->lifetime_sales = $stats->total;
                    $stat->total_sales = $stats->count;
                    break;
                case Order::ORDER_STATUS_PENDING:
                    $stat->pending_orders = $stats->count;
                    break;
                case Order::ORDER_STATUS_PROCESSING:
                    $stat->proccessing_orders = $stats->count;
                    break;
            }
        }
        if(!isset($stat->lifetime_sales)) $stat->lifetime_sales = 0;
        if(!isset($stat->total_sales)) $stat->total_sales = 0;
        if(!isset($stat->pending_orders)) $stat->pending_orders = 0;
        if(!isset($stat->processing_orders)) $stat->processing_orders = 0;


        if( $stat->total_sales != 0) $stat->average_sale_amount = number_format($stat->lifetime_sales / $stat->total_sales ,2) ;
        else $stat->average_sale_amount = 0;
        // get 5 recent orders
        $orderRepository = $this->model->getOrderRepository();
        $recent_orders = $orderRepository->getRecentOrders($this->module_info->module_srl);

        // get most ordered products
        $top_products = $orderRepository->getMostOrderedProducts($this->module_info->module_srl);

        //get top 5 customers
        $top_customers = $orderRepository->getTopCustomers($this->module_info->module_srl);

		Context::set('stat', $stat);
        Context::set('recent_orders',$recent_orders);
        Context::set('top_products',$top_products);
        Context::set('top_customers',$top_customers);

        /**
         * Notify user if he needs to update shop
         */
        // Get list of modules
        $oModuleModel = &getModel('module');
        $module_list = $oModuleModel->getModuleList();
        if(is_array($module_list))
        {
            $needUpdate = false;
            $addTables = false;
            foreach($module_list AS $key=>$value)
            {
                if($value->module !== 'shop') continue;
                if($value->need_install)
                {
                    $addTables = true;
                }
                if($value->need_update)
                {
                    $needUpdate = true;
                }
            }
        }
        Context::set('module_list', $module_list);
        Context::set('addTables', $addTables);
        Context::set('needUpdate', $needUpdate);

	}

    /**
     * display shop tool statistics visitor
     **/
    function dispShopToolStatisticsVisitor() {
        global $lang;

        $selected_date = Context::get('selected_date');
        if(!$selected_date) $selected_date = date("Ymd");
        Context::set('selected_date', $selected_date);

        $oCounterModel = &getModel('counter');

        $type = Context::get('type');
        if(!$type) {
            $type = 'day';
            Context::set('type',$type);
        }

        $site_module_info = Context::get('site_module_info');

        $xml = new stdClass();
        $xml->item = array();
        $xml->value = array(array(),array());
        $selected_count = 0;

        // total & today
        $counter = $oCounterModel->getStatus(array(0,date("Ymd")),$site_module_info->site_srl);
        $total = new stdClass();
        $total->total = $counter[0]->unique_visitor;
        $total->today = $counter[date("Ymd")]->unique_visitor;

        switch($type) {
            case 'month' :
                $xml->selected_title = Context::getLang('this_month');
                $xml->last_title = Context::getLang('before_month');

                $disp_selected_date = date("Y", strtotime($selected_date));
                $before_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)-60*60*24*365));
                $after_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)+60*60*24*365));
                $detail_status = $oCounterModel->getHourlyStatus('month', $selected_date, $site_module_info->site_srl);
                $i=0;
                foreach($detail_status->list as $key => $val) {
                    $_k = substr($selected_date,0,4).'.'.sprintf('%02d',$key);
                    $output = new stdClass();
                    $output->list[$_k]->val = $val;
                    if($selected_date == date("Ymd")&&$key == date("m")){
                        $selected_count = $val;
                        $output->list[$_k]->selected = true;
                    }else{
                        $output->list[$_k]->selected = false;
                    }
                    $output->list[$_k]->val = $val;
                    $xml->item[] = sprintf('<item id="%d" name="%s" />',$i++,$_k);
                    $xml->value[0][] = $val;
                }


                $last_date = date("Ymd",strtotime($selected_date)-60*60*24*365);
                $last_detail_status = $oCounterModel->getHourlyStatus('month', $last_date, $site_module_info->site_srl);
                foreach($last_detail_status->list as $key => $val) {
                    $xml->value[1][] = $val;
                }

                break;
            case 'week' :
                $xml->selected_title = Context::getLang('this_week');
                $xml->last_title = Context::getLang('last_week');

                $before_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)-60*60*24*7));
                $after_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)+60*60*24*7));
                $disp_selected_date = date("Y.m.d", strtotime($selected_date));
                $detail_status = $oCounterModel->getHourlyStatus('week', $selected_date, $site_module_info->site_srl);
                foreach($detail_status->list as $key => $val) {
                    $_k = date("Y.m.d", strtotime($key)).'('.$lang->unit_week[date('l',strtotime($key))].')';
                    $output = new stdClass();
                    if($selected_date == date("Ymd")&&$key == date("Ymd")){
                        $selected_count = $val;
                        $output->list[$_k]->selected = true;
                    }else{
                        $output->list[$_k]->selected = false;
                    }
                    $output->list[$_k]->val = $val;
                    $xml->item[] = sprintf('<item id="%s" name="%s" />',$_k,$_k);
                    $xml->value[0][] = $val;
                }

                $last_date = date("Ymd",strtotime($selected_date)-60*60*24*7);
                $last_detail_status = $oCounterModel->getHourlyStatus('week', $last_date, $site_module_info->site_srl);
                foreach($last_detail_status->list as $key => $val) {
                    $xml->value[1][] = $val;
                }


                break;
            case 'day' :
                $xml->selected_title = Context::getLang('today');
                $xml->last_title = Context::getLang('day_before');

                $before_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)-60*60*24));
                $after_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)+60*60*24));
                $disp_selected_date = date("Y.m.d", strtotime($selected_date));


                $detail_status = $oCounterModel->getHourlyStatus('hour', $selected_date, $site_module_info->site_srl);

                foreach($detail_status->list as $key => $val) {
                    $_k = sprintf('%02d',$key);
                    $output = new stdClass();
                    if($selected_date == date("Ymd")&&$key == date("H")){
                        $selected_count = $val;
                        $output->list[$_k]->selected = true;
                    }else{
                        $output->list[$_k]->selected = false;
                    }
                    $output->list[$_k]->val = $val;
                    $xml->item[] = sprintf('<item id="%d" name="%02d" />',$key,$key);
                    $xml->value[0][] = $val;
                }

                $last_date = date("Ymd",strtotime($selected_date)-60*60*24);
                $last_detail_status = $oCounterModel->getHourlyStatus('hour', $last_date, $site_module_info->site_srl);
                foreach($last_detail_status->list as $key => $val) {
                    $xml->value[1][] = $val;
                }


                break;
        }

        // set xml
        //  $xml->data = '<Graph><gdata title="Shop Visitor" id="'.$type.'"><fact>';
        $xml->data = '<Graph><gdata title="Shop Visitor" id="data"><fact>';
        $xml->data .= join("",$xml->item);
        $xml->data .= "</fact><subFact>";
        $xml->data .='<item id="0"><data name="'.$xml->selected_title.'">'. join("|",$xml->value[0]) .'</data></item>';
        $xml->data .='<item id="1"><data name="'.$xml->last_title.'">'. join("|",$xml->value[1]) .'</data></item>';
        $xml->data .= '</subFact></gdata></Graph>';


        //Context::set('xml', urlencode($xml->data));
        Context::set('xml', $xml->data);
        Context::set('before_url', $before_url);
        Context::set('after_url', $after_url);
        Context::set('disp_selected_date', $disp_selected_date);
        $output->sum = $detail_status->sum;
        $output->max = $detail_status->max;
        $output->selected_count = $selected_count;
        $output->total = $total->total;
        $output->today = $total->today;
        Context::set('detail_status', $output);
    }

	/**
	 * Login
	 **/
	public function dispShopToolLogin() {
		Context::addBodyClass('logOn');
	}


	public function dispShopToolLayoutConfigSkin() {
		$oModuleModel = getModel('module');

		$skins = $oModuleModel->getSkins($this->module_path);
		if(count($skins)) {
			foreach($skins as $skin_name => $info) {
				$large_screenshot = $this->module_path.'skins/'.$skin_name.'/screenshots/large.jpg';
				if(!file_exists($large_screenshot)) $large_screenshot = $this->module_path.'tpl/img/@large.jpg';
				$small_screenshot = $this->module_path.'skins/'.$skin_name.'/screenshots/small.jpg';
				if(!file_exists($small_screenshot)) $small_screenshot = $this->module_path.'tpl/img/@small.jpg';

				unset($obj);
                $obj = new stdClass();
				$obj->title = $info->title;
				$obj->description = $info->description;
				$_arr_author = array();
				for($i=0,$c=count($info->author);$i<$c;$i++) {
					$name =  $info->author[$i]->name;
					$homepage = $info->author[$i]->homepage;
					if($homepage) $_arr_author[] = '<a href="'.$homepage.'" onclick="window.open(this.href); return false;">'.$name.'</a>';
					else $_arr_author[] = $name;
				}
				$obj->author = implode(',',$_arr_author);
				$obj->large_screenshot = $large_screenshot;
				$obj->small_screenshot = $small_screenshot;
				$obj->date = $info->date;
				$output[$skin_name] = $obj;
			}
		}
		Context::set('skins', $output);
		Context::set('cur_skin', $output[$this->module_info->skin]);
	}

	public function dispShopToolLayoutConfigEdit() {
		$oShopModel = getModel('shop');
		$skin_path = $oShopModel->getShopPath($this->module_srl);

		$skin_file_list = $oShopModel->getShopUserSkinFileList($this->module_srl);
		$skin_file_content = array();
		foreach($skin_file_list as $file){
			if(preg_match('/^shop/',$file)){
				$skin_file_content[$file] = FileHandler::readFile($skin_path . $file);
			}
		}
		foreach($skin_file_list as $file){
			if(!in_array($file,$skin_file_content)){
				$skin_file_content[$file] = FileHandler::readFile($skin_path . $file);
			}
		}

		Context::set('skin_file_content',$skin_file_content);

		$user_image_path = sprintf("%suser_images/", $oShopModel->getShopPath($this->module_srl));
		$user_image_list = FileHandler::readDir($user_image_path);
		Context::set('user_image_path',$user_image_path);
		Context::set('user_image_list',$user_image_list);
	}

    public function dispShopToolLayoutConfigEditSettings()
    {
        // get the grant information from admin module
        $oModuleAdminModel = &getAdminModel('module');
        $skin_content = $oModuleAdminModel->getModuleSkinHTML($this->module_info->module_srl);
        Context::set('skin_content', $skin_content);
    }

    public function dispShopToolManageOrders()
    {
        $extraParams = array();
        if ($search = Context::get('search')) {
            $col = (Context::get('column') ? Context::get('column') : 'billing_address');
            $extraParams[$col] = $search;
        }
        $repo = new OrderRepository();
        $extraParams['order_type'] = 'desc';
        $orders = $repo->getList($this->module_info->module_srl, null, $extraParams, Context::get('page'));
        Context::set('orders', $orders->data);
        Context::set('page_navigation', $orders->page_navigation);
    }

    public function dispShopToolManageInvoices()
    {
        $extraParams = array();
        if ($search = Context::get('search')) $extraParams['search'] = $search;
        $repo = new InvoiceRepository();
        $invoices = $repo->getList($this->module_info->module_srl, $extraParams);
        Context::set('invoices', $invoices->data);
        Context::set('page_navigation', $invoices->page_navigation);
    }

    public function dispShopToolManageShipments()
    {
        $repo = new ShipmentRepository();
        $shipments = $repo->getList($this->module_info->module_srl);
        Context::set('shipments', $shipments->data);
        Context::set('page_navigation', $shipments->page_navigation);
    }

    public function dispShopToolViewOrder()
    {
        $repo = new OrderRepository();
        if ($order = $repo->getOrderBySrl(Context::get('order_srl'))) {
            $order_items = $repo->getOrderItems($order);
            Context::set('ordered_items',$order_items);
            Context::set('order', $order);
        }
        else throw new ShopException('No such order');
    }

    public function dispShopToolInvoiceOrder()
    {
        $this->dispShopToolViewOrder();
        $this->setTemplateFile('InvoiceOrder');
    }

    public function dispShopToolShipOrder()
    {
        $this->dispShopToolViewOrder();
        $this->setTemplateFile('ShipOrder');
    }

	public function dispShopToolManageAttributes()
	{
		$repository = new AttributeRepository();
        $extraParams = (Context::get('search') ? array('search'=> Context::get('search')) : null);
        $output = $repository->getAttributesList($this->module_info->module_srl, $extraParams);

		Context::set('attributes_list', $output->attributes);
		Context::set('page_navigation', $output->page_navigation);
	}

	/**
	 * attribute add page
	 */
	public function dispShopToolAddAttribute()
	{
		$attributeRepository = new AttributeRepository();
		Context::set('types', $attributeRepository->getTypes(Context::get('lang')));
		// Retrieve existing categories
		$categoryRepository = new CategoryRepository();
		$tree = $categoryRepository->getCategoriesTree($this->module_srl);

		// Prepare tree for display
		$tree_config = new HtmlCategoryTreeConfig();
		$tree_config->showCheckbox = TRUE;
		$tree_config->selected = array();
		$tree_config->checkboxesName = 'category_scope';
        $tree_config->HTMLmode = FALSE;
		$HTML_tree = $tree->toHTML($tree_config);

		Context::set('HTML_tree', $HTML_tree);
	}

    public function dispShopToolAddCoupon()
    {
        $coupon = new Coupon();
        $coupon->generateCode(12, RandomGenerator::TYPE_ALPHANUM, 'X', 4);
        Context::set('object', $coupon);
    }

    /**
     * display shop tool edit coupon
     * @return Object
     */
    public function dispShopToolEditCoupon()
    {
        $repo = new CouponRepository();
        $srl = Context::get('srl');
        if (!is_numeric($srl) || (!$coupon = $repo->get($srl))) {
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopToolDiscountCodes'));
            return new Object(-1, "Invalid srl $srl for identifying coupon");
        }
        Context::set('object', $coupon);
        $this->setTemplateFile('AddCoupon');
    }

    public function dispShopToolAddCouponGroup()
    {
        $coupon = new Coupon();
        $coupon->generateCode(7);
        Context::set('object', $coupon);
    }

    /**
     * display shop tool edit coupon group
     * @return Object
     * @throws ShopException
     */
    public function dispShopToolEditCouponGroup()
    {
        $repo = new CouponRepository();
        $srl = Context::get('srl');
        try {
            /** @var $coupon Coupon */
            if (!is_numeric($srl) || (!$coupon = $repo->get($srl))) throw new ShopException("No such coupon group");
            if ($coupon->type != Coupon::TYPE_PARENT) throw new ShopException("Invalid group");
        }
        catch (ShopException $e) {
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopToolDiscountCodes'));
            return new Object(-1, $e->getMessage());
        }
        Context::set('object', $coupon);

        $children = $coupon->getChildren($this->module_srl);
        Context::set('childCoupons', $children);

        $codes = array();
        /** @var $c Coupon */
        foreach ($children as $c) $codes[] = $c->code;
        Context::set('codesForCopy', implode("\n", $codes));

        $this->setTemplateFile('AddCouponGroup');
    }

    public function dispShopToolEditAttribute()
	{
        $srl = Context::get('attribute_srl');
        $attributeRepository = new AttributeRepository();
		if (!$attributes = $attributeRepository->getAttributes(array($srl))) throw new ShopException("Attribute doesn't exist");
		$attribute = array_shift($attributes);
        if(is_array($attribute->values)) $attribute->values = implode('|', $attribute->values);

        Context::set('attribute', $attribute);
		Context::set('types', $attributeRepository->getTypes(Context::get('lang')));

		// Retrieve existing categories
		$categoryRepository = new CategoryRepository();
		$tree = $categoryRepository->getCategoriesTree($this->module_srl);

		// Prepare tree for display
		$tree_config = new HtmlCategoryTreeConfig();
		$tree_config->showCheckbox = TRUE;
		$tree_config->selected = $attribute->category_scope;
		$tree_config->checkboxesName = 'category_scope';
        $tree_config->HTMLmode = FALSE;
		$HTML_tree = $tree->toHTML($tree_config);

		Context::set('HTML_tree', $HTML_tree);

		$this->setTemplateFile('AddAttribute');
	}

	/**
	 * Shop display product tool page
	 */
	public function dispShopToolManageProducts(){
		$module_srl = $this->module_info->module_srl;

		$args = new stdClass();
		$args->module_srl = $module_srl;

        if ($search = Context::get('search')) {
            $col = (Context::get('column') ? Context::get('column') : 'title');
            $args->$col = $search;
        }
        if ($cat_srl = Context::get('category_srl')) {
            if (!is_numeric($cat_srl)) throw new ShopException('invalid category srl');
            $cat = new Category($cat_srl);
            Context::set('filterCategory', $cat);
            $args->category_srls = array($cat_srl);
        }

        if ($page = Context::get('page')) $args->page = $page;

        Context::set('column_filters', array('title', 'description'));

        $pRepo = new ProductRepository();
        $output = $pRepo->getProductList($args);
        Context::set('product_list', $output->products);
        Context::set('page_navigation', $output->page_navigation);

        $category_repository = new CategoryRepository();
        $tree = $category_repository->getCategoriesTree($module_srl);
        $flat_tree = $tree->toFlatStructure();

        Context::set('productsCount', $pRepo->count('countProducts', array('module_srl' => $module_srl)));

        Context::set('category_list', $flat_tree);
    }

    /**
     * Shop display page for import products
     */
    public function dispShopToolImportProducts(){
        $shopModel = getModel('shop');

        $product_repository = $shopModel->getProductRepository();
        $module_srl = $this->module_info->module_srl;

        $args = new stdClass();
        $args->module_srl = $module_srl;

    }

	/**
	 * Shop display product edit page
	 */
	public function dispShopToolEditProduct(){
		$this->dispShopToolAddProduct();
		$this->setTemplateFile('AddProduct');
	}

	/**
	 * Shop display simple product add page
	 */
	public function dispShopToolAddProduct()
    {
		$args = Context::getRequestVars();
		if(isset($args->configurable_attributes))
		{
			if(count($args->configurable_attributes) > 2)
			{
				global $lang;
				$this->setMessage($lang->not_allowed_to_add_more_than_2_configurable_attributes, 'error');
				$error_return_url = Context::get('error_return_url');
				$this->setRedirectUrl($error_return_url);
				return;
			}
			Context::set('configurable_attributes',$args->configurable_attributes);
		}

		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$productRepository = $shopModel->getProductRepository();

		// Retrieve product if exists
		$product_srl = Context::get('product_srl');
		if($product_srl)
		{
			$product = $productRepository->getProduct($product_srl);
			if($product->parent_product_srl) {
				$parent_product = $productRepository->getProduct($product->parent_product_srl);
				Context::set('parent_product',$parent_product);
			}

			// Display associated products for Configurable products
			if($product->isConfigurable())
			{
				Context::set('product_list',$product->associated_products);
			}
		}
		else
		{
			if($args->configurable_attributes)
			{
				$product = new ConfigurableProduct();
			}
			else
			{
				$product = new SimpleProduct();
			}
		}
		Context::set('product',$product);

		// Retrieve all attributes
		$attributeRepository = $shopModel->getAttributeRepository();
		$output = $attributeRepository->getAttributesList($this->module_info->module_srl);
		foreach($output->attributes as $attribute)
		{
			$attributeRepository->getAttributeScope($attribute);
		}
		Context::set('attributes_list', $output->attributes);

		// Retrieve existing categories
		$categoryRepository = $shopModel->getCategoryRepository();
		$tree = $categoryRepository->getCategoriesTree($this->module_srl);

		// Prepare tree for display
		$tree_config = new HtmlCategoryTreeConfig();
		$tree_config->showCheckbox = TRUE;
		$tree_config->selected = $product->categories;
		$tree_config->checkboxesName = 'categories';
        $tree_config->HTMLmode = FALSE;
		$HTML_tree = $tree->toHTML($tree_config);

		Context::set('HTML_tree', $HTML_tree);
	}

	/**
	 * Shop display configurable product add page
	 */
	public function dispShopToolAddConfigurableProduct(){
		$shopModel = getModel('shop');
		$attributeRepository = $shopModel->getAttributeRepository();
		$output = $attributeRepository->getConfigurableAttributesList($this->module_info->module_srl);
		Context::set('attributes',$output->attributes);
	}


	/**
	 * Shop display associated products
	 */
	public function dispShopToolAddAssociatedProducts(){
		$shopModel = getModel('shop');
		$product_srl = Context::get('product_srl');
		$productRepository = $shopModel->getProductRepository();
		$product = $productRepository->getProduct($product_srl);

		Context::set('product',$product);
		$attributeRepository = $shopModel->getAttributeRepository();
		$configurable_attributes = $attributeRepository->getAttributes(array_keys($product->configurable_attributes));
		if(count($product->configurable_attributes) == 1){
            foreach($configurable_attributes as $conf_att){
                $values_combinations = $conf_att->values;
            }
            $config_atts[$configurable_attributes->attribute_srl] = $configurable_attributes;
            unset($configurable_attributes);
            $configurable_attributes = $config_atts;
		}else{
			foreach($configurable_attributes as $conf_att){
				$configurable_values[] = $conf_att->values;
			}
			$values_combinations = $attributeRepository->getValuesCombinations($configurable_values);
		}
        if(isset($product->associated_products)){
            foreach($product->associated_products as $associated_product){
                foreach($configurable_attributes as $key => $value){
                    $existing_combination[] = $associated_product->attributes[$key];
                }
                $existing_combinations[] = $existing_combination;
                unset($existing_combination);
            }
        }
        if(isset($existing_combinations)){
            foreach($values_combinations as $key => $value_comb){
                foreach($existing_combinations as $existing_comb){
                    if(count($value_comb) != 1) {
                        $val = trim(implode('',$value_comb));
                        $exist = trim(implode('',$existing_comb));
                        if($val == $exist){
                            $keys[] = $key;
                        }
                    } else {
                        if(trim($value_comb) == trim($existing_comb[0])){
                            $keys[] = $key;
                        }
                    }
                }
            }
        }
        if(isset($keys)){
            foreach($keys as $key){
                unset($values_combinations[$key]);
            }
        }
		Context::set('values_combinations',$values_combinations);

	}

    /**
     * Shop home page
     **/
    public function dispShopHome(){
        // Products list
        $this->loadShopCategoryTree();
        $productRepo = $this->model->getProductRepository();

        if (isset($_SESSION['grid_view'])) Context::set('grid_view', $_SESSION['grid_view']);
        if (isset($_SESSION['sort'])) Context::set('sort', $_SESSION['sort']);

        try {
            $args = new stdClass();
            $args->module_srl = $this->module_srl;
            FrontFilters::work($args);
            $args->status = 'enabled';
            $args->list_count = 9;
            if ($this->shop->getOutOfStockProducts() == 'N') $args->in_stock = 'Y';
            $output = $productRepo->getFeaturedProducts($args, TRUE, TRUE);
            Context::set('products', $output->products);
            //Context::set('page_navigation', $output->page_navigation);
            $datasourceJS = $this->getAssociatedProductsAttributesAsJavascriptArray($output->products);
            Context::set('datasourceJS', $datasourceJS);

            $this->setTemplateFile('index.html');
        }
        catch(Exception $e) {
            return new Object(-1, $e->getMessage());
        }
    }

	/**
	 * Shop view products list
	 **/
	public function dispShop() {

        $this->loadShopCategoryTree();

		// Products list
		$productRepo = new ProductRepository();

        if (isset($_SESSION['grid_view'])) Context::set('grid_view', $_SESSION['grid_view']);
        if (isset($_SESSION['sort'])) Context::set('sort', $_SESSION['sort']);

        try {
            $args = new stdClass();
            $args->module_srl = $this->module_srl;
            FrontFilters::work($args);
            $args->list_count = 9;
            $args->status = 'enabled';
            if ($this->shop->getOutOfStockProducts() == 'N') $args->in_stock = 'Y';
            $page = Context::get('page');
            if ($page) $args->page = $page;
            $category_srl = (($category = Context::get('category')) instanceof Category ? $category->category_srl : Context::get('category_srl'));
            if ($category_srl) $args->category_srls = array($category_srl);

            $args->status = 'enabled';
            if ($this->shop->getOutOfStockProducts() == 'N') $args->in_stock = 'Y';
            if ($_SESSION['sort'] == 'price_asc') {
                $args->index = 'price';
                $args->order_type = 'asc';
            } elseif ($_SESSION['sort'] == 'price_desc') {
                $args->index = 'price';
                $args->order_type = 'desc';
            }
            $output = $productRepo->getProductList($args, TRUE, TRUE);
            Context::set('products', $output->products);
            Context::set('page_navigation', $output->page_navigation);

            $datasourceJS = $this->getAssociatedProductsAttributesAsJavascriptArray($output->products);
            Context::set('datasourceJS', $datasourceJS);

            $this->setTemplateFile("product_list.html");
		}
		catch(Exception $e) {
			return new Object(-1, $e->getMessage());
		}
	}

    /**
     * load shop category tree
     * @param array $selected_categories
     */
    protected function loadShopCategoryTree($selected_categories = array()){
        // Categories left tree
        // Retrieve existing categories
        $category_srl = Context::get('category_srl');
        $category_repository = $this->model->getCategoryRepository();
        $tree = $category_repository->getNavigationCategoriesTree($this->module_srl);

        // Prepare tree for display
        $tree_config = new HtmlCategoryTreeConfig();
        if ($this->module_info->category_product_count == 'Yes') {
            $tree_config->showProductCount = TRUE;
        } else {
            $tree_config->showProductCount = FALSE;
        }
        $tree_config->linkCategoryName = TRUE;
        $tree_config->openCloseSign = TRUE;
        $tree_config->linkGetUrlParams = array('vid', $this->mid, 'act', 'dispShop','page','');
        $tree_config->selected = $selected_categories;
        if($category_srl) $tree_config->selected[] = $category_srl;
        $HTML_tree = $tree->toHTML($tree_config);
        Context::set('HTML_tree', $HTML_tree);

        // Current category details
        if($category_srl)
        {
            $current_category = $category_repository->getCategory($category_srl);
            Context::set('current_category', $current_category);

            $breadcrumbs_items = $category_repository->getCategoryParents($current_category);
            Context::set('breadcrumbs_items', $breadcrumbs_items);
        }
    }

	/**
	 * Frontend shop product page
	 */
	public function dispShopProduct()
	{
        /** @var $product Product */
        if ($product = Context::get('product')) {
            //came from from routing
            if (!($product instanceof Product) || !$product->isPersisted()) throw new ShopException('Wrong product');
        }
		else {
            if (!$product_srl = Context::get('product_srl')) throw new ShopException('Could not identify product');
            $product_repository = new ProductRepository();
            $product = $product_repository->getProduct($product_srl);
            if (!$product instanceof Product) throw new ShopException('Product does not exist');
            Context::set('product', $product);
        }

        //add product document if it does not exist  (link to comments)
        if(!isset($product->document_srl)){
            $documentController = getController('document');
            $document = new stdClass();
            $document->title = $product->product_srl;
            $document->commentStatus = 'ALLOW';
            $document->module_srl = $product->module_srl;
            $output = $documentController->insertDocument($document);
            $product->document_srl = $output->variables['document_srl'];
            unset($product->images);
            $product->repo->updateProduct($product);
        }
        $documentModel = getModel('document');
        $product->document = $documentModel->getDocument($product->document_srl);
        $product->comment_list = $_comment_list = $product->document->getComments();
        if (is_array($_comment_list)) {
            foreach ($product->comment_list as $comment){
                $comment->variables['relativeDate'] = $this->model->zdateRelative($comment->getRegdateTime());
            }
        }

		// Setup Javscript datasource for linked dropdowns
		$datasourceJS = $this->getAssociatedProductsAttributesAsJavascriptArray(array($product));
		Context::set('datasourceJS', $datasourceJS);

		// Setup attributes names for display
		if(count($product->attributes))
		{
			$attribute_repository = new AttributeRepository();
			$attributes = $attribute_repository->getAttributes(array_keys($product->attributes));
			Context::set('attributes', $attributes);
		}

		// Categories left tree
		// Retrieve existing categories
		$category_repository = new CategoryRepository();
		$tree = $category_repository->getNavigationCategoriesTree($this->module_srl);

		// Prepare tree for display
		$tree_config = new HtmlCategoryTreeConfig();
		$tree_config->linkCategoryName = TRUE;
		$tree_config->linkGetUrlParams = array('vid', $this->mid, 'act', 'dispShop');
		$tree_config->selected = $product->categories;
		$HTML_tree = $tree->toHTML($tree_config);
		Context::set('HTML_tree', $HTML_tree);

        $this->loadShopCategoryTree();

		$this->setTemplateFile('product.html');
	}

    public function dispShopMyAccount(){
        $logged_user = Context::get('logged_info');

        if(!isset($logged_user)){
            $this->setTemplateFile('not_logged.html');
            return;
        }

        $orderRepository = $this->model->getOrderRepository();
        $logged_user->recent_orders = $orderRepository->getRecentOrders($this->module_info->module_srl,$logged_user->member_srl);

        $addressRepository = $this->model->getAddressRepository();
        $logged_user->addresses = $addressRepository->getAddresses($logged_user->member_srl);

        Context::set('logged_user',$logged_user);
        $this->setTemplateFile('my_account.html');
    }

    public function dispShopMyOrders(){
        $logged_user = Context::get('logged_info');
        if(!isset($logged_user)){
            $this->setTemplateFile('not_logged.html');
            return;
        }
        $orderRepository = $this->model->getOrderRepository();
        $extraParams['order_type'] = 'desc';
        $output = $orderRepository->getList($this->module_info->module_srl,$logged_user->member_srl, $extraParams, Context::get('page'));
        Context::set('orders',$output->data);
        Context::set('page_navigation',$output->page_navigation);
        $this->setTemplateFile('my_orders.html');
    }

    public function dispShopAddressBook(){
        $shopModel = getModel('shop');
        $addressRepository = $shopModel->getAddressRepository();

        $logged_info = Context::get('logged_info');
        if(!isset($logged_info)){
            $this->setTemplateFile('not_logged.html');
            return;
        }
        $addresses = $addressRepository->getAddresses($logged_info->member_srl);

        Context::set('addresses',$addresses);
        $this->setTemplateFile('address_book.html');
    }

    public function dispShopAddAddress(){
        $shopModel = getModel('shop');
        $addressRepository = $shopModel->getAddressRepository();

        $address_srl = Context::get('address_srl');
        if($address_srl){
            $address = $addressRepository->getAddress($address_srl);
        } else {
            $address = new Address();
        }
        Context::set('address',$address);
        $this->setTemplateFile('address_book.html');
    }

    public function dispShopEditAddress(){
        $this->dispShopAddAddress();
        $this->setTemplateFile('address_book.html');
    }

	public function dispShopCart()
	{
        /** @var $cart Cart */
        if ($cart = Context::get('cart')) {
            $output = $cart->getProductsList(array('page' => Context::get('page')));
            Context::set('products_output', $output);
        }
        $this->setTemplateFile('cart.html');
	}

    public function dispShopSearch()
    {
        $product_repository = new ProductRepository();
        $page = Context::get('page');
        $search = Context::get('q');
        $args = new stdClass();
        $args->module_srl = $this->module_srl;
        FrontFilters::work($args);
        $args->sku = $search;
        $args->title = $search;
        $args->description = $search;
        $args->page = $page;
        $category_srl = Context::get('search_category_srl');
        if($category_srl) $args->category_srls = array($category_srl);

        $output = $product_repository->getProductList($args);
        Context::set('products', $output->products);
        Context::set('page_navigation', $output->page_navigation);
        Context::set('search_value', $search);

        $this->loadShopCategoryTree();

        $this->setTemplateFile("product_search.html");
    }

    /**
     * display shop view order
     * @return Object
     * @throws ShopException
     */
    public function dispShopViewOrder(){
        try{
            $orderRepo = new OrderRepository();
            $order = $orderRepo->getOrderBySrl(Context::get('order_srl'));
            $logged_info = Context::get('logged_info');
            if($order->member_srl != $logged_info->member_srl) throw new ShopException("This order does not belong to you");
        }
        catch (Exception $e) {
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopHome'));
            return new Object(-1, $e->getMessage());
        }
        $this->dispShopToolViewOrder();
        $this->setTemplateFile('view_order');
    }

    /**
     * display shop checkout
     * @return Object
     * @throws ShopException
     */
    public function dispShopCheckout()
    {
        try {
            /** @var $cart Cart */
            if (!(($cart = Context::get('cart')) instanceof Cart)) throw new ShopException("No cart, you shouldn't be in the checkout page");

            $products = $cart->getProducts(null, true);
            if (empty($products)) {
                throw new ShopException('Cart is empty, you have nothing to checkout');
            }
        }
        catch (Exception $e) {
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopHome'));
            return new Object(-1, $e->getMessage());
        }

        $shippingRepo = new ShippingMethodRepository();
        $paymentRepo = new PaymentMethodRepository();

		// Init the list of all available methods
		$shipping_methods = $shippingRepo->getAvailableShippingMethodsAndTheirPrices($this->module_srl, $cart);
        Context::set('shipping_methods', $shipping_methods);

		// Setup the selected shipping method - check if using default value or value from cart;
		$selected_shipping_method = $cart->getShippingMethodName();
		$selected_shipping_method .= $cart->getShippingMethodVariant() ? '__' . $cart->getShippingMethodVariant() : '';
		Context::set('selected_shipping_method', $selected_shipping_method);

        // payment methods
        $payment_methods = $paymentRepo->getActivePaymentMethods($this->module_srl);
        Context::set('payment_methods', $payment_methods);

        Context::set('addresses', $cart->getAddresses());
        Context::set('default_billing', $cart->getBillingAddress());
        Context::set('default_shipping', $cart->getShippingAddress());
        Context::set('needs_new_shipping', $cart->getBillingAddress() != $cart->getShippingAddress());
        Context::set('extra', $cart->getExtraArray());
        Context::set('cart_products', $products);
        if ($discount = $cart->getDiscount()) {
            Context::set('discount', $discount);
            Context::set('discount_value', $discount->getReductionValue());
            Context::set('discounted_value', $discount->getValueDiscounted());
        }
        $this->setTemplateFile('checkout.html');
    }

    /**
     * display shop place order
     * @return Object
     * @throws ShopException
     */
    public function dispShopPlaceOrder()
    {
        /** @var $cart Cart  */
        try {
            if ((!$cart = Context::get('cart')) || !$cart->items) {
                throw new ShopException("Cart doesn't exist anymore.");
            }
        }
        catch (Exception $e) {
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopHome'));
            return new Object(-1, $e->getMessage());
        }

        // 1. Setup payment info
        /**
         * @var shopModel $shopModel
         */
        $shopModel = getModel('shop');

        // Get selected payment method name
        $payment_method_name = $cart->getPaymentMethodName();

        // Get payment class
        $payment_repository = new PaymentMethodRepository();
        $payment_method = $payment_repository->getPaymentMethod($payment_method_name, $this->module_srl);

        $payment_method->onPlaceOrderFormLoad();

        Context::set('payment_method', $payment_method);
        Context::set('payment_method_name', $payment_method_name);

        // 2. Setup all other order info
        Context::set('billing_address', $cart->getBillingAddress());
        Context::set('shipping_address', $cart->getShippingAddress());

        $shipping_method_name = $cart->getShippingMethodName();
        $shipping_repository = new ShippingMethodRepository();
        $shipping_method = $shipping_repository->getShippingMethod($shipping_method_name, $this->module_srl);
        Context::set('shipping_method_name', $shipping_method->getDisplayName());
		$shipping_variants = $shipping_method->getVariants();
		Context::set('shipping_method_variant', $shipping_variants[$cart->getShippingMethodVariant()]);

        Context::set('extra', $cart->getExtraArray());
        Context::set('cart_products', $cart->getProducts());

        if ($discount = $cart->getDiscount()) {
            Context::set('discount', $discount);
            Context::set('discount_value', $discount->getReductionValue());
            Context::set('discounted_value', $discount->getValueDiscounted());
        }

        if ($coupon = $cart->getCoupon()) {
            Context::set('coupon', $coupon);
        }

        $this->setTemplateFile('place_order.html');
    }

    public function dispShopOrderConfirmation()
    {
        $cart = Context::get('cart');

        $payment_method_name = Context::get('payment_method_name');
        if ($payment_method_name) {
            $payment_repository = new PaymentMethodRepository();
            $payment_method = $payment_repository->getPaymentMethod($payment_method_name, $this->module_srl);
            try {
                $payment_method->onOrderConfirmationPageLoad($cart, $this->module_srl);
            }
			catch(PaymentProcessingException $exception)
			{
				Context::set('error_details', $exception->getMessage());
				$this->setTemplateFile("order_failed");;
				return;
			}
            catch(NetworkErrorException $exception)
            {
                $this->setTemplateFile("order_confirmation_coming_soon");
                return;
            }
        }

        $this->setTemplateFile('order_confirmation.html');
    }

	/**
	 * Returns the javascript code used as datasource for linked dropdowns
	 */
	private function getAssociatedProductsAttributesAsJavascriptArray($products, $reverse = NULL)
	{
		if(is_null($reverse))
		{
			return $this->getAssociatedProductsAttributesAsJavascriptArray($products, FALSE) . PHP_EOL .
						$this->getAssociatedProductsAttributesAsJavascriptArray($products, TRUE);
		}

		$datasource_name = 'associated_products';
		if($reverse) $datasource_name = 'reverse_' . $datasource_name;

		 $datasource = "var $datasource_name = new Object();" . PHP_EOL;
		 if(isset($products)){
			 foreach($products as $product)
			 {
				 if($product->isSimple()) continue;

				 $datasource .= $datasource_name . "[$product->product_srl] = new Object();" . PHP_EOL;

				 $already_added = array();
				 foreach($product->associated_products as $asoc_product)
				 {
					 $attribute_values = array_values($asoc_product->attributes);

					 // Take just first two attributes
					 if(!$reverse)
					 {
						 $attribute1 = $attribute_values[0];
						 $attribute2 = $attribute_values[1];
					 }
					 else
					 {
						 $attribute2 = $attribute_values[0];
						 $attribute1 = $attribute_values[1];
					 }


					 if($attribute2)
					 {
						 if(!$already_added[$attribute1])
						 {
							 $datasource .= $datasource_name . "[$product->product_srl]['$attribute1'] = new Object();" . PHP_EOL;
							 $already_added[$attribute1] = TRUE;
						 }

						 $datasource .= $datasource_name . "[$product->product_srl]['$attribute1']['$attribute2'] = $asoc_product->product_srl;" . PHP_EOL;
					 }
					 else
					 {
						 $datasource .= $datasource_name . "[$product->product_srl]['$attribute1'] = $asoc_product->product_srl;" . PHP_EOL;
					 }
				 }
			 }
		 }

		return $datasource;
	}

    /**
     * Customer management view (Admin)
     */
    public function dispShopToolManageCustomers()
    {
        $extraParams = array();
        if ($search = Context::get('search')) $extraParams = array('search' => $search);
        $cRepo = new CustomerRepository();
        $output = $cRepo->getCustomersList($this->site_srl, $extraParams);
        Context::set('customers_list',$output->customers);
        Context::set('page_navigation',$output->page_navigation);
    }

    /**
     * Send newsletter to subscribers view (Admin)
     */
    public function dispShopToolSendNewsletter(){
        $shopModel = $this->model;
        $newsletterRepository = $shopModel->getNewsletterRepository();
        $newsletter_srl = Context::get('newsletter_srl');
        if($newsletter_srl){
            $newsletter = $newsletterRepository->getNewsletter($newsletter_srl);
        }

        Context::set('newsletter',$newsletter);
    }

    /**
     * Subscribed customers management view
     */
    public function dispShopToolManageNewsletterSubscribers(){
        $shopModel = getModel('shop');
        $customerRepository = $shopModel->getCustomerRepository();
        $output = $customerRepository->getNewsletterCustomers($this->site_srl,'Y');

        Context::set('customers_list',$output->customers);
        Context::set('page_navigation',$output->page_navigation);
    }

    /**
     * Newsletters management view
     */
    public function dispShopToolManageNewsletters(){
        $shopModel = $this->model;
        $newsletterRepository = $shopModel->getNewsletterRepository();
        $output = $newsletterRepository->getList($this->module_info->module_srl);

        Context::set('newsletters',$output->data);
        Context::set('page_navigation',$output->page_navigation);
    }

    /**
     * Newsletters edit view
     */
    public function dispShopToolViewNewsletter(){
        $shopModel = $this->model;
        $newsletterRepository = $shopModel->getNewsletterRepository();
        $newsletter_srl = Context::get('newsletter_srl');
        if($newsletter_srl){
            $newsletter = $newsletterRepository->getNewsletter($newsletter_srl);
        }

        Context::set('newsletter',$newsletter);
    }

    /**
     * Newsletters resend view
     */
    public function dispShopToolResendNewsletter(){
        $this->dispShopToolSendNewsletter();
        $this->setTemplateFile('SendNewsletter');
    }

    /**
     * Customer manage addresses view (Admin)
     */
    public function dispShopToolManageAddresses(){
        $shopModel = getModel('shop');
        $member_srl = Context::get('member_srl');
        $memberModel = getModel('member');
        $member_info = $memberModel->getMemberInfoByMemberSrl($member_srl);
        $addressRepository = $shopModel->getAddressRepository();
        $output = $addressRepository->getAddressesList($member_srl);

        Context::set('member_info',$member_info);
        Context::set('addresses_list',$output->addresses);
        Context::set('page_navigation',$output->page_navigation);
        Context::set('member_srl',$member_srl);
    }

    /**
     * Customer add view (Admin)
     */
    public function dispShopToolAddCustomer(){
        $shopModel = getModel('shop');
        $oMemberAdminView = getAdminView('member');
        $oMemberModel = getModel('member');
        $customerRepository = $shopModel->getCustomerRepository();
        $member_srl = Context::get('member_srl');
        if($member_srl){
            $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
            $customer = new Customer($member_info);
            if($customer->password) Context::set('password_exists','Y');
            unset($customer->password);
        }

        $oMemberAdminView->dispMemberAdminInsert();
        $default_group = $oMemberModel->getDefaultGroup($this->module_info->site_srl);
        Context::set('customer',$customer);
        Context::set('default_group',$default_group->group_srl);
    }

    /**
     * Address add view (Admin)
     */
    public function dispShopToolAddAddress(){
        $shopModel = getModel('shop');
        $addressRepository = $shopModel->getAddressRepository();
        $address_srl = Context::get('address_srl');
        if($address_srl){
            $address = $addressRepository->getAddress($address_srl);
        }

        Context::set('address',$address);
    }

    /**
     * Edit customer view (Admin)
     */
    public function dispShopToolEditCustomer(){
        $this->dispShopToolAddCustomer();
        $this->setTemplateFile('AddCustomer');
    }

    /**
     * Edit address view (Admin)
     */
    public function dispShopToolEditAddress(){
        $this->dispShopToolAddAddress();
        $this->setTemplateFile('AddAddress');
    }


	// region Product category
	/**
	 * Category management view (Admin)
	 */
	public function dispShopToolManageCategories()
	{
		// Retrieve existing categories
		$shopModel = getModel('shop');
		$repository = $shopModel->getCategoryRepository();
		$tree = $repository->getCategoriesTree($this->module_srl);

		// Prepare tree for display
		$tree_config = new HtmlCategoryTreeConfig();
		$tree_config->showManagingLinks = TRUE;
        $tree_config->HTMLmode = FALSE;
        $tree_config->linkCategoryName = TRUE;
        $tree_config->linkGetUrlParams = array('act', 'dispShopToolManageProducts');
		$HTML_tree = $tree->toHTML($tree_config);

		Context::set('HTML_tree', $HTML_tree);

        // Load jQuery tree plugin
        Context::loadJavascriptPlugin('ui.tree');

		// Initialize new empty Category object
		$category = new Category();
		$category->module_srl = $this->module_srl;
		Context::set('category', $category);
	}
	// endregion

	// region Payment methods

	/**
	 * Displays the Payment methods management page
	 */
	public function dispShopToolManagePaymentMethods()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$repository = $shopModel->getPaymentMethodRepository();
        $payment_methods = $repository->getAvailablePaymentMethods($this->module_srl);

		Context::set('payment_methods',$payment_methods);
	}

    /**
     * Display settings for a payment method
     */
    public function dispShopToolEditPaymentMethod()
    {
        $name = Context::get('name');
        if(!$name)
        {
            return new Object(-1, 'msg_invalid_request');
        }
        /**
         * @var shopModel $shopModel
         */
        $shopModel = getModel('shop');
        $payment_repository = $shopModel->getPaymentMethodRepository();

        // Retrieve payment method, and save in context for it to be accessible from the plugin template
        $payment_method = $payment_repository->getPaymentMethod($name, $this->module_srl);
        Context::set('payment_method', $payment_method);

        // Retrieve backend form fields
        $payment_method_settings_HTML = $payment_method->getAdminSettingsFormHTML();
        Context::set('payment_method_settings_HTML', $payment_method_settings_HTML);
    }

    // endregion

    // region Extra menu
    /**
	 * Displays all extra menu elements
	 * @return object
	 */
	function dispShopToolPages(){
        /**
         * @var moduleModel $oModuleModel
         */
        $oModuleModel = getModel('module');
        $args = new stdClass();
        $args->site_srl = $this->site_srl;
        $args->module = 'page';
        $shop_pages = $oModuleModel->getMidList($args);
        Context::set('shop_pages', $shop_pages);
	}

    /**
     * Edit admin account from backend
     */
    public function dispShopToolManageAccount(){
        $oMemberModel = &getModel('member');
        $member_config = $oMemberModel->getMemberConfig();
    }

    /**
     * Change admin password from backend
     */
    public function dispShopToolChangePassword(){

    }

    /**
     * Change shop configuration from backend
     */
    public function dispShopToolConfigInfo(){

        $currencies = require_once(_XE_PATH_.'modules/shop/shop.currencies.php');
        Context::set('currencies',$currencies);

        Context::set('langs', Context::loadLangSelected());

        Context::set('time_zone_list', $GLOBALS['time_zone']);
        Context::set('time_zone', $GLOBALS['_time_zone']);
    }

    /**
     * Change shop discount configuration from backend
     */
    public function dispShopToolDiscountInfo(){}

    /**
     * Backend for discount codes / coupons
     */
    public function dispShopToolDiscountCodes() {
        $cRepo = new CouponRepository();
        $params1 = $params2 = array('module_srl' => $this->module_srl);
        if (Context::get('s')) $params1['search'] = $params2['search'] = Context::get('s');

        $params1['type'] = Coupon::TYPE_SINGLE;
        $output1 = $cRepo->getList('getCouponList', Context::get('page1'), $params1);
        Context::set('objects1', $output1->data);
        Context::set('page_navigation1', $output1->page_navigation);

        $params2['type'] = Coupon::TYPE_PARENT;
        $output2 = $cRepo->getList('getCouponList', Context::get('page2'), $params2);
        Context::set('objects2', $output2->data);
        Context::set('page_navigation2', $output2->page_navigation);
    }

    /**
     *
     */
    public function dispShopToolMenus()
    {
        global $lang;

        // Load menu areas
        $shop = Context::get('shop');
        $menus = $shop->getMenus();
        if(!$menus)
        {
            // Updata database
            // TODO Remove this after shop release
            $args = new stdClass();
            $args->module_srl = $this->module_srl;
            $args->menus = serialize(array(ShopMenu::MENU_TYPE_HEADER => "", ShopMenu::MENU_TYPE_FOOTER => ""));
            $output = executeQuery('shop.updateShopInfo', $args);
            if(!$output->toBool())
            {
                return $output;
            }
        }
        Context::set('menus', $menus);

        // Load langs for menu areas
        // Needed because XE template language doesn't support nested braces: {$lang->{$menu_key}}
        $menu_lang = array();
        foreach($menus as $menu_key => $menu_srl)
        {
            $menu_lang[$menu_key] = $lang->{$menu_key};
        }
        Context::set('menu_lang', $menu_lang);

        // Load available menus
        $oMenuAdminModel = getAdminModel('menu');
        $all_site_menus = $oMenuAdminModel->getMenus();
        Context::set('all_site_menus', $all_site_menus);
    }

	/**
	 * Add new module (page) to custom menu
	 *
	 * @return Object
	 */
	function dispShopToolInsertPage(){
		// Check if editing an existing page
        //  $document_srl = Context::get('document_srl');
        $module_srl = Context::get('module_srl');

        if($module_srl){
            // Editing existing item
            /**
             * @var moduleModel $oModuleModel
             */
            $oModuleModel = getModel('module');
            $page_module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
            Context::set('page_module_info', $page_module_info);

            /**
             * @var shopModel $shopModel
             */
            $shopModel = getModel('shop');
            $document_srl = $shopModel->getPageDocumentSrl($page_module_info->content);
        }

		$oDocumentModel = &getModel('document');

		if($document_srl){
			$oDocument = $oDocumentModel->getDocument($document_srl,FALSE,FALSE);
            Context::set('document_srl', $document_srl);
		}else{
			$document_srl=0;
			$oDocument = $oDocumentModel->getDocument(0);
		}

        /**
         * @var editorModel $oEditorModel
         */
        $oEditorModel = &getModel('editor');
        $option = new stdClass();
        $option->skin = 'xpresseditor';
		$option->primary_key_name = 'document_srl';
		$option->content_key_name = 'content';
		$option->allow_fileupload = TRUE;
		$option->enable_autosave = TRUE;
		$option->enable_default_component = TRUE;
		$option->enable_component = $option->skin =='dreditor' ? FALSE : TRUE;
		$option->resizable = TRUE;
		$option->height = 500;
		$editor = $oEditorModel->getEditor($document_srl, $option);
		Context::set('editor', $editor);
		Context::set('editor_skin', $option->skin);

		if($oDocument->get('module_srl') != $this->module_srl && !$document_srl){
			Context::set('from_saved',TRUE);
		}

		Context::set('oDocument', $oDocument);
	}

	// endregion

    // region Shipping
    public function dispShopToolManageShippingMethods()
    {
        /**
         * @var shopModel $shopModel
         */
        $shopModel = getModel('shop');
        $shipping_repository = $shopModel->getShippingRepository();

        $shipping_methods = $shipping_repository->getAvailableShippingMethods($this->module_srl);
        Context::set('shipping_methods', $shipping_methods);
    }

    public function dispShopToolEditShipping()
    {
        $name = Context::get('name');
        /**
         * @var shopModel $shopModel
         */
        $shopModel = getModel('shop');
        $shipping_repository = $shopModel->getShippingRepository();
        $shipping_instance = $shipping_repository->getShippingMethod($name, $this->module_srl, $this->module_srl);
        Context::set('shipping_method', $shipping_instance);

        $shipping_form_html = $shipping_instance->getFormHtml();
        Context::set('shipping_form_html', $shipping_form_html);
    }

    /**
     * display comment editor
     * @return Object
     */
    public function dispCommentEditor()
    {
        $document_srl = Context::get('document_srl');

        $oDocumentModel = &getModel("document");
        $oDocument = $oDocumentModel->getDocument($document_srl);

        if (!$oDocument->isExists())
        {
            return new Object(-1, 'msg_invalid_request');
        }

        if (!$oDocument->allowComment())
        {
            return new Object(-1, 'comments_disabled');
        }

        Context::set('oDocument', $oDocument);

        $oModuleModel = &getModel('module');
        $module_info = $oModuleModel->getModuleInfoByModuleSrl($oDocument->get('module_srl'));

        Context::set("module_info", $module_info);

        $module_path = './modules/' . $module_info->module . '/';
        $skin_path = $module_path . 'skins/' . $module_info->skin . '/';

        if(!$module_info->skin || !is_dir($skin_path))
        {
            $skin_path = $module_path . 'skins/default/';
        }

        $oTemplateHandler = &TemplateHandler::getInstance();
        $html = base64_encode($oTemplateHandler->compile($skin_path, 'comment_form.html'));
        $this->add('html', $html);
        $this->add('message_type', 'info');
    }

    /**
     * display modify comment
     * @return Object
     */
    function dispModifyComment()
    {
        // allow only logged in users to comment.
        if (!Context::get('is_logged'))
        {
            return new Object(-1, 'login_to_modify_comment');
        }

        $comment_srl = Context::get('comment_srl');

        if (!$comment_srl)
        {
            return new Object(-1, 'msg_invalid_request');
        }

        $oCommentModel = &getModel('comment');
        $oComment = $oCommentModel->getComment($comment_srl, $this->grant->manager);

        if (!$oComment->isExists())
        {
            return new Object(-1, "There is no comment");
        }

        Context::set('oComment', $oComment);

        $oModuleModel = &getModel('module');
        $module_info = $oModuleModel->getModuleInfoByModuleSrl($oComment->get('module_srl'));

        if (!$oComment->isGranted())
        {
            return new Object(-1, 'no_rights_to_modify_comment');
        }

        Context::set("module_info", $module_info);

        $module_path = './modules/' . $module_info->module . '/';
        $skin_path = $module_path . 'skins/' . $module_info->skin . '/';

        if(!$module_info->skin || !is_dir($skin_path))
        {
            $skin_path = $module_path . 'skins/default/';
        }

        $oTemplateHandler = &TemplateHandler::getInstance();

        $html = base64_encode($oTemplateHandler->compile($skin_path, 'comment_form.html'));
        $this->add('html', $html);

        $this->add('html', $html);
        $this->add('comment_srl', $oComment->comment_srl);
    }

    /**
     * display reply comment
     * @return Object
     */
    public function dispReplyComment()
    {
        $parent_srl = Context::get('comment_srl');

        if (!$parent_srl)
        {
            return new Object(-1, 'msg_invalid_request');
        }

        $oCommentModel = &getModel('comment');
        $oSourceComment = $oCommentModel->getComment($parent_srl, $this->grant->manager);

        if (!$oSourceComment->isExists())
        {
            return new Object(-1, 'msg_invalid_request');
        }

        $oDocumentModel = &getModel("document");
        $oDocument = $oDocumentModel->getDocument($oSourceComment->get('document_srl'));

        if (!$oDocument->isExists())
        {
            return new Object(-1, 'msg_invalid_request');
        }

        if (!$oDocument->allowComment())
        {
            return new Object(-1, 'comments_disabled');
        }

        $oComment = $oCommentModel->getComment();
        $oComment->add('parent_srl', $parent_srl);
        $oComment->add('document_srl', $oSourceComment->get('document_srl'));
        $oComment->add('module_srl', $this->module_srl);

        Context::set('oComment', $oComment);

        $oModuleModel = &getModel('module');
        $module_info = $oModuleModel->getModuleInfoByModuleSrl($oDocument->get('module_srl'));

        Context::set("module_info", $module_info);

        $module_path = './modules/' . $module_info->module . '/';
        $skin_path = $module_path . 'skins/' . $module_info->skin . '/';

        if(!$module_info->skin || !is_dir($skin_path))
        {
            $skin_path = $module_path . 'skins/default/';
        }

        $oTemplateHandler = &TemplateHandler::getInstance();

        $html = base64_encode($oTemplateHandler->compile($skin_path, 'comment_form.html'));
        $this->add('html', $html);
        $this->add('parent_srl', $parent_srl);
    }


}
