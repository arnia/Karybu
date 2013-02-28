<?php


require_once(_XE_PATH_.'modules/shop/shop.info.php');
require_once(__DIR__ . '/libs/autoload/autoload.php');

define(__XE_SHOP_DEBUG__, 0);
/**
 * @class  shop
 * @author Arnia (xe_dev@arnia.ro)
 *  shop module main class
 **/
class shop extends ModuleObject {

    /**
     * default mid
     **/
    public $shop_mid = 'shop';

    /**
     * default skin
     **/
    public $skin = 'default';

    public $add_triggers = array(
        array('display', 'shop', 'controller', 'triggerMemberMenu', 'before'),
        array('moduleHandler.proc', 'shop', 'controller', 'triggerApplyLayout', 'after'),
        array('member.doLogin', 'shop', 'controller', 'triggerLoginBefore', 'before'),
        array('member.doLogin', 'shop', 'controller', 'triggerLoginAfter', 'after'),
        array('moduleHandler.init', 'shop', 'controller', 'triggerDeleteOldLogs', 'after'),
        array('display', 'shop', 'controller', 'triggerDisplayLogMessages', 'after'),
        array('member.insertMember', 'shop', 'controller', 'triggerSendSignUpEmail', 'after')
    );

    /**
     * module install
     **/
    public function moduleInstall() {
        $oModuleController = getController('module');

        foreach($this->add_triggers as $trigger) {
            $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
        }

    }

    /**
     * check for update method
     **/
    public function checkUpdate() {
        $oDB = &DB::getInstance();
        $oModuleModel = getModel('module');

        foreach($this->add_triggers as $trigger) {
            if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
        }

        if(!$oDB->isColumnExists("shop_cart_products","price")) return true;
        if(!$oDB->isColumnExists("shop_attributes","is_filter")) return true;
        if(!$oDB->isColumnExists("shop_orders","transaction_id")) return true;
        if(!$oDB->isColumnExists("shop","currency_symbol")) return true;
        if(!$oDB->isColumnExists("shop_products","discount_price")) return true;
        if(!$oDB->isColumnExists("shop_products","is_featured")) return true;
        if(!$oDB->isColumnExists("shop","discount_min_amount")) return true;
        if(!$oDB->isColumnExists("shop","discount_type")) return true;
        if(!$oDB->isColumnExists("shop","discount_amount")) return true;
        if(!$oDB->isColumnExists("shop","discount_tax_phase")) return true;
        if(!$oDB->isColumnExists("shop","out_of_stock_products")) return true;
        if(!$oDB->isColumnExists("shop","minimum_order")) return true;
        if(!$oDB->isColumnExists("shop","show_VAT")) return true;
        if(!$oDB->isColumnExists("shop_order_products","member_srl")) return true;
        if(!$oDB->isColumnExists("shop_order_products","parent_product_srl")) return true;
        if(!$oDB->isColumnExists("shop_order_products","product_type")) return true;
        if(!$oDB->isColumnExists("shop_order_products","title")) return true;
        if(!$oDB->isColumnExists("shop_order_products","description")) return true;
        if(!$oDB->isColumnExists("shop_order_products","short_description")) return true;
        if(!$oDB->isColumnExists("shop_order_products","sku")) return true;
        if(!$oDB->isColumnExists("shop_order_products","weight")) return true;
        if(!$oDB->isColumnExists("shop_order_products","status")) return true;
        if(!$oDB->isColumnExists("shop_order_products","friendly_url")) return true;
        if(!$oDB->isColumnExists("shop_order_products","price")) return true;
        if(!$oDB->isColumnExists("shop_order_products","discount_price")) return true;
        if(!$oDB->isColumnExists("shop_order_products","qty")) return true;
        if(!$oDB->isColumnExists("shop_order_products","in_stock")) return true;
        if(!$oDB->isColumnExists("shop_order_products","primary_image_filename")) return true;
        if(!$oDB->isColumnExists("shop_order_products","related_products")) return true;
        if(!$oDB->isColumnExists("shop_order_products","regdate")) return true;
        if(!$oDB->isColumnExists("shop_order_products","last_update")) return true;
        if(!$oDB->isColumnExists("shop_cart_products","title")) return true;
        if(!$oDB->isColumnExists("shop_orders","discount_min_order")) return true;
        if(!$oDB->isColumnExists("shop_orders","discount_type")) return true;
        if(!$oDB->isColumnExists("shop_orders","discount_amount")) return true;
        if(!$oDB->isColumnExists("shop_orders","discount_tax_phase")) return true;
        if(!$oDB->isColumnExists("shop_orders","currency")) return true;
        if(!$oDB->isColumnExists("shop_orders","discount_reduction_value")) return true;

        if($oDB->isColumnExists("shop_categories","order")) return true;
        if(!$oDB->isColumnExists("shop_categories","list_order")) return true;

        if(!$oDB->isColumnExists("shop_addresses","firstname")) return true;
        if(!$oDB->isColumnExists("shop_addresses","lastname")) return true;

        if(!$oDB->isColumnExists("shop_payment_methods","module_srl")) return true;
        if(!$oDB->isColumnExists("shop_shipping_methods","module_srl")) return true;

        if($oDB->isIndexExists("shop_payment_methods","unique_name")) return true;
        if($oDB->isIndexExists("shop_shipping_methods","unique_name")) return true;

        if(!$oDB->isColumnExists("shop","menus")) return true;

        // if($oDB->isColumnExists("shop_orders","total")) return true;

        if(!$oDB->isColumnExists("shop_payment_methods","is_default")) return true;
        if(!$oDB->isColumnExists("shop_shipping_methods","is_default")) return true;

        if(!$oDB->isColumnExists("shop","shop_email")) return true;

		if(!$oDB->isColumnExists("shop_orders","shipping_variant")) return true;

		if(!$oDB->isColumnExists("shop","unit_of_measure")) return true;

		if(!$oDB->isColumnExists("shop_products","document_srl")) return true;

        return false;
    }

    /**
     * module update
     **/
    public function moduleUpdate() {
        $oDB = &DB::getInstance();
        $oModuleModel = getModel('module');
        $oModuleController = getController('module');

        foreach($this->add_triggers as $trigger) {
            if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }
        }

        if(!$oDB->isColumnExists("shop_orders","transaction_id")) {
            $oDB->addColumn('shop_orders',"transaction_id","varchar",128);
        }

        if(!$oDB->isColumnExists("shop_attributes","is_filter")) {
            $oDB->addColumn('shop_attributes',"is_filter","char",1);
        }

        if(!$oDB->isColumnExists("shop","currency_symbol")) {
            $oDB->addColumn('shop',"currency_symbol","varchar",5);
        }

        if(!$oDB->isColumnExists("shop_products","discount_price")) {
            $oDB->addColumn('shop_products',"discount_price","float",20);
        }

        if(!$oDB->isColumnExists("shop_products","is_featured")) {
            $oDB->addColumn('shop_products',"is_featured","char",1);
        }

        if(!$oDB->isColumnExists("shop","show_VAT")) {
            $oDB->addColumn('shop',"show_VAT","char",1);
        }

        if(!$oDB->isColumnExists("shop","discount_min_amount")) {
            $oDB->addColumn('shop',"discount_min_amount","float",20);
        }

        if(!$oDB->isColumnExists("shop","discount_type")) {
            $oDB->addColumn('shop',"discount_type","varchar",40);
        }

        if(!$oDB->isColumnExists("shop","discount_amount")) {
            $oDB->addColumn('shop',"discount_amount","float",20);
        }

        if(!$oDB->isColumnExists("shop","discount_tax_phase")) {
            $oDB->addColumn('shop',"discount_tax_phase","varchar",40);
        }

        if(!$oDB->isColumnExists("shop","out_of_stock_products")) {
            $oDB->addColumn('shop',"out_of_stock_products","char",1);
        }

        if(!$oDB->isColumnExists("shop","minimum_order")) {
            $oDB->addColumn('shop',"minimum_order","float",20);
        }

        if(!$oDB->isColumnExists("shop_order_products","member_srl")) {
            $oDB->addColumn('shop_order_products',"member_srl","number",11, null, true);
        }

        if(!$oDB->isColumnExists("shop_order_products","parent_product_srl")) {
            $oDB->addColumn('shop_order_products',"parent_product_srl","number", 11);
        }

        if(!$oDB->isColumnExists("shop_order_products","product_type")) {
            $oDB->addColumn('shop_order_products',"product_type","varchar", 250, null, true);
        }

        if(!$oDB->isColumnExists("shop_cart_products","price")) {
            $oDB->addColumn('shop_cart_products',"price","float", 20);
        }

        if(!$oDB->isColumnExists("shop_order_products","title")) {
            $oDB->addColumn('shop_order_products',"title","varchar", 250, null, true);
        }

        if(!$oDB->isColumnExists("shop_order_products","description")) {
            $oDB->addColumn('shop_order_products',"description","bigtext");
        }

        if(!$oDB->isColumnExists("shop_order_products","short_description")) {
            $oDB->addColumn('shop_order_products',"short_description","varchar", 500);
        }

        if(!$oDB->isColumnExists("shop_order_products","sku")) {
            $oDB->addColumn('shop_order_products',"sku","varchar", 250, null, true);
        }

        if(!$oDB->isColumnExists("shop_order_products","weight")) {
            $oDB->addColumn('shop_order_products',"weight","float", 10);
        }

        if(!$oDB->isColumnExists("shop_order_products","status")) {
            $oDB->addColumn('shop_order_products',"status","varchar", 50);
        }

        if(!$oDB->isColumnExists("shop_order_products","friendly_url")) {
            $oDB->addColumn('shop_order_products',"friendly_url","varchar", 50);
        }

        if(!$oDB->isColumnExists("shop_order_products","price")) {
            $oDB->addColumn('shop_order_products',"price","float", 20, null, true);
        }

        if(!$oDB->isColumnExists("shop_order_products","discount_price")) {
            $oDB->addColumn('shop_order_products',"discount_price","float", 20);
        }

        if(!$oDB->isColumnExists("shop_order_products","qty")) {
            $oDB->addColumn('shop_order_products',"qty","float", 10);
        }

        if(!$oDB->isColumnExists("shop_order_products","in_stock")) {
            $oDB->addColumn('shop_order_products',"in_stock","char", 1, 'N');
        }

        if(!$oDB->isColumnExists("shop_order_products","primary_image_filename")) {
            $oDB->addColumn('shop_order_products',"primary_image_filename","varchar", 250);
        }

        if(!$oDB->isColumnExists("shop_order_products","related_products")) {
            $oDB->addColumn('shop_order_products',"related_products","varchar", 500);
        }

        if(!$oDB->isColumnExists("shop_cart_products","title")) {
            $oDB->addColumn('shop_cart_products',"title","varchar", 255);
        }

        if(!$oDB->isColumnExists("shop_order_products","regdate")) {
            $oDB->addColumn('shop_order_products',"regdate","date");
        }

        if(!$oDB->isColumnExists("shop_order_products","last_update")) {
            $oDB->addColumn('shop_order_products',"last_update","date");
        }

        if($oDB->isColumnExists("shop_categories","order")) {
            $oDB->dropColumn('shop_categories',"order");
        }

        if(!$oDB->isColumnExists("shop_categories","list_order")) {
            $oDB->addColumn('shop_categories',"list_order","number", 11, 0, true);
            executeQuery('shop.fixCategoriesOrder');
        }

        if(!$oDB->isColumnExists("shop_addresses","firstname")) {
            $oDB->addColumn('shop_addresses',"firstname","varchar", 45);
        }

        if(!$oDB->isColumnExists("shop_addresses","lastname")) {
            $oDB->addColumn('shop_addresses',"lastname","varchar", 45);
        }

        if(!$oDB->isColumnExists("shop_payment_methods","module_srl")) {
            $oDB->addColumn('shop_payment_methods',"module_srl","number", 11, 0, true);
        }

        if(!$oDB->isColumnExists("shop_shipping_methods","module_srl")) {
            $oDB->addColumn('shop_shipping_methods',"module_srl","number", 11, 0, true);
        }

        if($oDB->isIndexExists("shop_payment_methods","unique_name"))
        {
            $oDB->dropIndex("shop_payment_methods", "unique_name", true);
            $oDB->addIndex("shop_payment_methods", "unique_module_srl_name", array('module_srl', 'name'), true);
        }

        if($oDB->isIndexExists("shop_shipping_methods","unique_name"))
        {
            $oDB->dropIndex("shop_shipping_methods", "unique_name", true);
            $oDB->addIndex("shop_shipping_methods", "unique_module_srl_name", array('module_srl', 'name'), true);
        }

        if(!$oDB->isColumnExists("shop","menus")) {
            $oDB->addColumn('shop',"menus","varchar", 500);
        }

        if (!$oDB->isColumnExists("shop_orders","discount_min_order")) $oDB->addColumn('shop_orders',"discount_min_order","float", 20);
        if (!$oDB->isColumnExists("shop_orders","discount_type")) $oDB->addColumn('shop_orders',"discount_type","varchar", 45);
        if (!$oDB->isColumnExists("shop_orders","discount_amount")) $oDB->addColumn('shop_orders',"discount_amount","float", 20);
        if (!$oDB->isColumnExists("shop_orders","discount_tax_phase")) $oDB->addColumn('shop_orders',"discount_tax_phase","varchar", 20);
        if (!$oDB->isColumnExists("shop_orders","discount_reduction_value")) $oDB->addColumn('shop_orders',"discount_reduction_value","float", 20);
        if (!$oDB->isColumnExists("shop_orders","currency")) $oDB->addColumn('shop_orders',"currency","varchar", 10);

//			if ($oDB->isColumnExists("shop_orders","total"))
//			{
//				$oDB->dropColumn('shop_orders',"total");
//				$oDB->addColumn('shop_orders',"total","float", 20, 0 , true);
//			}

        if(!$oDB->isColumnExists("shop_payment_methods","is_default")) {
            $oDB->addColumn('shop_payment_methods',"is_default","number", 1, 0);
        }
        if(!$oDB->isColumnExists("shop_shipping_methods","is_default")) {
            $oDB->addColumn('shop_shipping_methods',"is_default","number", 1, 0);
        }

        if(!$oDB->isColumnExists("shop","shop_email")) {
            $oDB->addColumn('shop',"shop_email","varchar", 250);
        }

		if(!$oDB->isColumnExists("shop_orders","shipping_variant")) {
			$oDB->addColumn('shop_orders',"shipping_variant","varchar", 250);
		}

		if(!$oDB->isColumnExists("shop","unit_of_measure")) {
			$oDB->addColumn('shop',"unit_of_measure","varchar",5);
		}

        if(!$oDB->isColumnExists("shop_products","document_srl")) {
            $oDB->addColumn('shop_products',"document_srl","number", 11);
        }

        return new Object(0, 'success_updated');
    }

    /**
     * recompile cache
     **/
    public function recompileCache() {
    }

    /**
     * check xe core version
     * @param $requried_version
     * @return bool
     */
    public function checkXECoreVersion($requried_version){
        $result = version_compare(__XE_VERSION__, $requried_version, '>=');
        if ($result != 1) return false;
        return true;
    }

}