<?php
/**
 * File containing the ShippingMethodRepository class
 */
/**
 * Handles logic for Shipping
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class ShippingMethodRepository extends AbstractPluginRepository
{
    /**
     * Returns all available shipping methods
     */
    public function getAvailableShippingMethods($module_srl)
    {
        return $this->getAvailablePlugins($module_srl);
    }

	/**
	 * Returns all active shipping methods
	 */
	public function getActiveShippingMethods($module_srl)
	{
		return $this->getActivePlugins($module_srl);
	}

	/**
	 * Returns all available shipping methods for the current cart
	 * together with the appropriate prices
	 *
	 * Sample return
	 * return array(
	 *  'flat_rate_shipping' => 'Flat rate shipping 10$'
	 * , 'ups__01' => 'UPS Domestic bla bla 5$'
	 * , 'ups__65' => 'UPS International saver 4$'
	 * )
	 */
	public function getAvailableShippingMethodsAndTheirPrices($module_srl, Cart $cart)
	{
		$cache_key = 'available_shipping_' . $module_srl . '_' . $cart->cart_srl;
		$available_shipping_methods = self::$cache->get($cache_key);

		if(!$available_shipping_methods)
		{
			$shop_info = new ShopInfo($module_srl);
			$active_shipping_methods = $this->getActiveShippingMethods($module_srl);

			$available_shipping_methods = array();
			foreach($active_shipping_methods as $shipping_method)
			{
				$available_variants = $shipping_method->getAvailableVariants($cart);
				foreach($available_variants as $variant)
				{
					if(!$variant->price)
					{
						$key = "";
						$value = $variant->display_name . ' - ' . $variant->variant_display_name;
					}
					else
					{
						$key = $variant->name;
						if($variant->variant) $key .= '__' . $variant->variant;

						$value = $variant->display_name;
						if($variant->variant) $value .= ' - ' . $variant->variant_display_name;
						$value .= ' - ' . ShopDisplay::priceFormat($variant->price, $shop_info->getCurrencySymbol());
					}

					$available_shipping_methods[$key] = $value;
				}
			}
			self::$cache->set($cache_key, $available_shipping_methods);
		}
		return $available_shipping_methods;
	}

	/**
	 * Returns the default shipping method
	 *
	 * @param      $module_srl
	 * @param null $cart
	 * @return mixed|null|ShippingMethodAbstract
	 */
	public function getDefault($module_srl, $cart = NULL)
	{
		if(!$cart)
		{
			return parent::getDefault($module_srl);
		}

		$shipping_methods = $this->getAvailableShippingMethodsAndTheirPrices($module_srl, $cart);
		if(!count($shipping_methods) > 0) return NULL;

		$shipping_method_keys = array_keys($shipping_methods);

		$default_shipping = parent::getDefault($module_srl);

		// We check to see if the default shipping method is available
		foreach($shipping_method_keys as $key)
		{
			if(strpos($key, $default_shipping->name) !== FALSE)
			{
				return $default_shipping;
			}
		}

		return $this->getShippingMethod($shipping_method_keys[0], $module_srl);
	}


	/**
	 * Get a certain shipping method instance
	 *
	 * @param $name
	 * @param $module_srl
	 * @internal param string $code Folder name of the shipping method
	 *
	 * @return ShippingMethodAbstract
	 */
    public function getShippingMethod($name, $module_srl)
    {
        return $this->getPlugin($name, $module_srl);
    }

	/**
	 * Update shipping method properties
	 *
	 * @param $shipping_info
	 */
	public function updateShippingMethod($shipping_info)
    {
        if(isset($shipping_info->is_active))
        {
            $shipping_info->status = $shipping_info->is_active == 'Y' ? 1 : 0;
            unset($shipping_info->is_active);
        }
        $this->updatePlugin($shipping_info);
    }

	/**
	 * Directory where shipping plugins should be put
	 *
	 * @return mixed|string
	 */
	function getPluginsDirectoryPath()
    {
        return _XE_PATH_ . 'modules/shop/plugins_shipping';
    }

	/**
	 * Returns the name of the base class for all shipping plugins
	 *
	 * @return mixed|string
	 */
	function getClassNameThatPluginsMustExtend()
    {
        return "ShippingMethodAbstract";
    }

	/**
	 * Returns the shipping method properties from the database
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	protected function getPluginInfoFromDatabase($name, $module_srl)
    {
        $output = $this->query('shop.getShippingMethod', array('name' => $name, 'module_srl' => $module_srl));
        return $output->data;
    }

	/**
	 * Updates the module srl of a shipping method
	 *
	 * @param $name
	 * @param $old_module_srl
	 * @param $new_module_srl
	 * @return mixed|void
	 */
	protected function fixPlugin($name, $old_module_srl, $new_module_srl)
    {
        $this->query('shop.fixShippingMethod', array('name' => $name, 'module_srl' => $new_module_srl, 'source_module_srl' => $old_module_srl));
    }

	/**
	 * Updates the properties of a shipping method
	 *
	 * @param $plugin
	 * @return mixed|void
	 */
	protected function updatePluginInfo($plugin)
    {
        $this->query('shop.updateShippingMethod', $plugin);
    }

	/**
	 * Insert a new shipping method
	 *
	 * @param AbstractPlugin $plugin
	 * @return mixed|void
	 */
	protected function insertPluginInfo(AbstractPlugin $plugin)
    {
        $plugin->id = getNextSequence();
        $this->query('shop.insertShippingMethod', $plugin);
    }

	/**
	 * Deletes a shipping method from the database
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed|void
	 */
	protected function deletePluginInfo($name, $module_srl)
    {
        $this->query('shop.deleteShippingMethod', array('name' => $name, 'module_srl' => $module_srl));
    }

	/**
	 * Returns all shipping methods in the database
	 *
	 * @param $module_srl
	 * @param $args
	 * @return mixed
	 */
	protected function getAllPluginsInDatabase($module_srl, $args)
    {
		if(!$args) $args = new stdClass();
		$args->module_srl = $module_srl;

        $output = $this->query('shop.getShippingMethods', $args, TRUE);
		return $output->data;
    }

	/**
	 * Returns all enabled shipping methods in the database
	 *
	 * @param $module_srl
	 * @return mixed
	 */
	protected function getAllActivePluginsInDatabase($module_srl)
    {
        $output = $this->query('shop.getShippingMethods', array('status' => 1, 'module_srl' => $module_srl), TRUE);
		return $output->data;
    }

	/**
	 * Updates all shipping methods' properties except for one
	 *
	 * @param $is_default
	 * @param $name
	 * @param $module_srl
	 * @return mixed|void
	 */
	protected function updatePluginsAllButThis($is_default, $name, $module_srl)
	{
		$args = new stdClass();
		$args->except_name = $name;
		$args->module_srl = $module_srl;
		$args->is_default = 0;
		$this->query('shop.updateShippingMethods', $args);
	}
}