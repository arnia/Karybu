<?php
/**
 * File containing the TableRateShipping class
 */
/**
 * Class for the Table rate shipping method
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class TableRateShipping extends ShippingMethodAbstract
{

	const TYPE_PRICE_DESTINATION = 'price_and_destination'
		, TYPE_WEIGHT_DESTINATION = 'weight_and_destination'
		, TYPE_ITEMS_COUNT_DESTINATION = 'items_count_and_destination';

	/**
	 * Returns an array of table rates, as follows:
	 * array(
	 * 	stdClass ( id, country, unit, price)
	 * )
	 *
	 * @return array
	 */
	public function getTableRates()
	{
		return json_decode($this->serialized_table_rates);
	}

	/**
	 * Returns an array of table rates, filtered by country
	 *
	 * The * refers to all countries;
	 *
	 * @param $country_code
	 * @return array
	 */
	public function getTableRatesForCountry($country_code)
	{
		$rates_for_all_countries = array();
		$rates_for_this_country = array();
		foreach($this->getTableRates() as $table_rate)
		{
			if($table_rate->country == $country_code)
				$rates_for_this_country[] = $table_rate;
			else if($table_rate->country == "*")
				$rates_for_all_countries[] = $table_rate;
		}

		if(count($rates_for_this_country) > 0)
			return $rates_for_this_country;
		return $rates_for_all_countries;
	}

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @param string $error_message
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->serialized_table_rates))
		{
			$error_message = 'Please enter at least one rule for the table rates.';
			return FALSE;
		}
		$table_rates = $this->getTableRates();
		if(!(count($table_rates) > 0))
		{
			$error_message = 'Please enter at least one rule for the table rates.';
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Calculates shipping rates
	 *
	 * @param Cart   $cart    Shipping cart for which to calculate shipping; includes shipping address
	 * @param String $service Represents the specific service for which to calcualte shipping (e.g. Standard or Priority)
	 * @return null
	 */
	public function calculateShipping(Cart $cart, $service = NULL)
	{
		/** @var $unit The thing we will compare to the table rates: price, weight or item count */
		$unit = NULL;
		switch($this->type){
			case TableRateShipping::TYPE_PRICE_DESTINATION:
				$unit = $cart->getItemTotal(); // TODO Check if maybe getTotalAfterDiscount should be used instead
				break;
			case TableRateShipping::TYPE_WEIGHT_DESTINATION:
				$unit = $cart->getTotalWeight();
				break;
			case TableRateShipping::TYPE_ITEMS_COUNT_DESTINATION:
				$unit = count($cart->getProducts());
				break;
		}

		$shipping_address = $cart->getShippingAddress();
		$shipping_country = $shipping_address->country;

		$table_rates = $this->getTableRatesForCountry($shipping_country);
		$shipping_price = NULL;
		foreach($table_rates as $table_rate)
		{
			if($table_rate->unit <= $unit)
			{
				$shipping_price = $table_rate->price;
			}
			else
			{
				break;
			}
		}

		// If no shipping price was calculated, just take the last
		if(is_null($shipping_price))
		{
			ShopLogger::log("Couldn't match any rules from the table;");
			$shipping_price = $table_rate->price;
		}

		return $shipping_price;
	}

	/**
	 * Returns a list of available variants
	 * The structure is:
	 * array(
	 * 	stdclass(
	 * 		'name' => 'ups'
	 * 		, 'display_name' => 'UPS'
	 * 		, 'variant' => '01'
	 * 		, 'variant_display_name' => 'Domestic'
	 * 		,  price => 12
	 * ))
	 *
	 * @param Cart $cart
	 * @return array
	 */
	public function getAvailableVariants(Cart $cart)
	{
		try{
			$variant = new stdClass();
			$variant->name = $this->getName();
			$variant->display_name = $this->getDisplayName();
			$variant->variant = NULL;
			$variant->variant_display_name = NULL;
			$variant->price = $this->calculateShipping($cart);
			return array($variant);
		}
		catch(ShopException $e)
		{
			return array();
		}
	}

}