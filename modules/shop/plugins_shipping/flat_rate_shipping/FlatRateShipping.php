<?php
/**
 * File containing the FlatRateShippingClass
 */
require_once dirname(__FILE__) . '/../ShippingMethodAbstract.php';

/**
 * Class for adding Flat Rate Shipping as a shipping method in XE Shop
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class FlatRateShipping extends ShippingMethodAbstract
{
	/**
	 * Constructor
	 */
	public function __construct()
    {
        $this->shipping_method_dir = _XE_PATH_ . 'modules/shop/plugins_shipping/flat_rate_shipping';
        parent::__construct();
    }

	/**
	 * Calculates shipping rates
	 *
	 * @param Cart $cart SHipping cart for which to calculate shipping
	 * @param null $service
	 * @internal param \Address $shipping_address Address to which products should be shipped
	 * @return int|mixed
	 */
    public function calculateShipping(Cart $cart, $service = NULL)
    {
        if($this->type == 'per_item')
        {
            $products = $cart->getProducts();
            $total_quantity = 0;
            foreach($products as $product)
                $total_quantity += $product->quantity;
            return $total_quantity * $this->price;
        }

        return $this->price;
    }

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @param string $error_message
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->price))
		{
			$error_message = 'msg_missing_shipping_price';
			return FALSE;
		}

		if(!isset($this->type))
		{
			$error_message = 'msg_missing_shipping_type';
			return FALSE;
		}

		if(!in_array($this->type, array('per_item', 'per_order')))
		{
			$error_message = 'msg_invalid_shipping_type';
			return FALSE;
		}
		if(!is_numeric($this->price))
		{
			$error_message = 'msg_invalid_shipping_price';
			return FALSE;
		}
		return TRUE;
	}
}