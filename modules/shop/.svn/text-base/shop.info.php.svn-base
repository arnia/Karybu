<?php
    /**
     * @class  ShopInfo
     * @author Arnia (xe_dev@arnia.ro)
     *  shop module Shop info class
     **/

    class ShopInfo extends Object {

		const UNIT_OF_MEASURE_KGS = 'Kg'
			, UNIT_OF_MEASURE_LBS = 'Lbs';

        public $site_srl = null,
            $domain = null,
            $shop_srl = null,
            $module_srl = null,
            $member_srl = null,
            $shop_title = null,
            $colorset = null,
            $timezone = null;

        /**
         * shop info
         * @param int $shop_srl
         */
        public function ShopInfo($shop_srl = 0) {
            if(!$shop_srl) return;
            $this->setShop($shop_srl);
        }

        /**
         * set shop
         * @param $shop_srl
         */
        public function setShop($shop_srl) {
            $this->module_srl = $this->shop_srl = $shop_srl;
            $this->_loadFromDB();
        }

        public function _loadFromDB() {
            $oShopModel = getModel('shop');

            if(!$this->shop_srl) return;
            $args = new stdClass();
            $args->module_srl = $this->shop_srl;
            $output = executeQuery('shop.getShop', $args);
            if(!$output->toBool()||!$output->data) return;
            $this->setAttribute($output->data);

            $config = $oShopModel->getModulePartConfig($this->module_srl);
            if($config && count($config)) {
                foreach($config as $key => $val) {
                    $this->add($key, $val);
                }
            }
        }

        /**
         * set attribute
         * @param $attribute
         */
        public function setAttribute($attribute) {
            if(!$attribute->module_srl) {
                $this->shop_srl = null;
                return;
            }
            $this->module_srl = $this->shop_srl = $attribute->module_srl;
            $this->member_srl = $attribute->member_srl;
            $this->colorset = $attribute->colorset;
            $this->domain = $attribute->domain;
            $this->site_srl = $attribute->site_srl;
            $this->timezone = $attribute->timezone;
            $this->default_language = $attribute->default_language;

            $this->adds($attribute);
        }

        /**
         * is home
         * @return bool
         */
        public function isHome() {
            $module_info = Context::get('module_info');
            if($this->getModuleSrl() == $module_info->module_srl) return true;
            return false;
        }

        /**
         * get browser title
         * @return string
         */
        public function getBrowserTitle() {
            if(!$this->isExists()) return;
            return $this->get('browser_title');
        }

        /**
         * get shop title
         * @return string
         */
        public function getShopTitle() {
            if(!$this->isExists()) return;
            return $this->get('shop_title');
        }

        /**
         * get shop email
         * @return string
         */
        public function getShopEmail(){
			if(!$this->isExists()) return;
			return $this->get('shop_email');
		}

        /**
         * get favicon source
         * @return mixed
         */
        function getFaviconSrc(){
            if(!$this->isExists()) return;
            $oShopModel = &getModel('shop');
            return $oShopModel->getShopFaviconSrc($this->module_srl);
        }

        /**
         * get default favicon srl
         * @return mixed
         */
        function getDefaultFaviconSrc(){
            $oShopModel = &getModel('shop');
            $src = $oShopModel->getShopDefaultFaviconSrc();
            return $src;
        }

        /**
         * get mid
         * @return string
         */
        public function getMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        /**
         * get member srl
         * @return string
         */
        public function getMemberSrl() {
            if(!$this->isExists()) return;
            return $this->get('member_srl');
        }

        /**
         * get module srl
         * @return null
         */
        public function getModuleSrl() {
            if(!$this->isExists()) return;
            return $this->getShopSrl();
        }

        /**
         * get shop srl
         * @return null
         */
        public function getShopSrl() {
            if(!$this->isExists()) return;
            return $this->shop_srl;
        }

        /**
         * get shop mid
         * @return string
         */
        public function getShopMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        /**
         * get nick name
         * @return string
         */
        public function getNickName() {
            if(!$this->isExists()) return;
            $nick_name = $this->get('nick_name');
            if(!$nick_name) $nick_name = $this->getUserId();
            return $nick_name;
        }

        /**
         * get user name
         * @return string
         */
        public function getUserName() {
            if(!$this->isExists()) return;
            return $this->get('user_name');
        }

        /**
         * get profile content
         * @return string
         */
        public function getProfileContent() {
            if(!$this->isExists()) return;
            return $this->get('profile_content');
        }

        /**
         * get shop content
         * @return string
         */
        public function getShopContent() {
            if(!$this->isExists()) return;
            return $this->get('shop_content');
        }

        /**
         * get telephone
         * @return string
         */
        public function getTelephone() {
            if(!$this->isExists()) return;
            return $this->get('telephone');
        }

        /**
         * get address
         * @return string
         */
        public function getAddress() {
            if(!$this->isExists()) return;
            return $this->get('address');
        }

        /**
         * get currency
         * @return string
         */
        public function getCurrency() {
            if(!$this->isExists()) return;
            return $this->get('currency');
        }

        /**
         * get measurement unit
         * @return string
         */
        public function getUnitOfMeasure() {
			if(!$this->isExists()) return;
			return $this->get('unit_of_measure');
		}

        /**
         * get currency symbol
         * @return string
         */
        public function getCurrencySymbol() {
            if(!$this->isExists()) return;
            return $this->get('currency_symbol');
        }

        /**
         * get VAT
         * @return string
         */
        public function getVAT() {
            if(!$this->isExists()) return;
            return $this->get('VAT');
        }

        /**
         *  get show VAT
         * @return string
         */
        public function getShowVAT() {
            if(!$this->isExists()) return;
            return $this->get('show_VAT');
        }

        /**
         * get shop discount minimum amount
         * @return string
         */
        public function getShopDiscountMinAmount() {
            if(!$this->isExists()) return;
            return $this->get('discount_min_amount');
        }

        /**
         * get shop discount type
         * @return string
         */
        public function getShopDiscountType() {
            if(!$this->isExists()) return;
            return $this->get('discount_type');
        }

        /**
         * get shop discount amount
         * @return string
         */
        public function getShopDiscountAmount() {
            if(!$this->isExists()) return;
            return $this->get('discount_amount');
        }

        /**
         * get shop discount tax phase
         * @return string
         */
        public function getShopDiscountTaxPhase() {
            if(!$this->isExists()) return;
            return $this->get('discount_tax_phase');
        }

        /**
         * get out of stock products
         * @return string
         */
        public function getOutOfStockProducts() {
            if(!$this->isExists()) return;
            return $this->get('out_of_stock_products');
        }

        /**
         * get minimum order
         * @return string
         */
        public function getMinimumOrder() {
            if(!$this->isExists()) return;
            return $this->get('minimum_order');
        }

        /**
         * get email
         * @return string
         */
        public function getEmail() {
            if(!$this->isExists()) return;
            return $this->get('email_address');
        }

        /**
         * get input email
         * @return string
         */
        public function getInputEmail(){
            if(!$this->isExists()) return;
            return $this->get('input_email');
        }

        /**
         * get input website
         * @return string
         */
        public function getInputWebsite(){
            if(!$this->isExists()) return;
            return $this->get('input_website');
        }

        /**
         * get User ID
         * @return string
         */
        public function getUserID() {
            if(!$this->isExists()) return;
            return $this->get('user_id');
        }

        /**
         * get menus
         * @return array|mixed|string
         */
        public function getMenus()
        {
            if(!$this->isExists()) return;
            if(is_array($this->get('menus')))
            {
                return $this->get('menus');
            }
            else
            {
                $menus = unserialize($this->get('menus'));
                if($menus)
                    return $menus;
                return array();
            }
        }

        /**
         * get menu
         * @param $menu_type
         * @return null
         */
        public function getMenu($menu_type)
        {
            if(!$this->isExists()) return;
            $menus = $this->get('menus');
            if(isset($menus))
            {
                if(isset($menus[$menu_type]))
                {
                    return $menus[$menu_type];
                }
                return null;
            }
            return null;
        }

        /**
         * verify if exists
         * @return bool
         */
        public function isExists() {
            return $this->shop_srl?true:false;
        }

        /**
         * get permanent url
         * @return string
         */
        public function getPermanentUrl() {
            if(!$this->isExists()) return;
            return getUrl('','mid',$this->getMid());
        }

        /**
         * show vat
         * @return bool
         */
        public function showVAT()
        {
            return $this->getShowVAT() == 'Y' && $this->getVAT() > 0;
        }
   }
?>
