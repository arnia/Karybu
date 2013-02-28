<?php
/**
 * File containing the classes used for integrating Paypal with XE Shop
 */
/**
 * Allows user to pay for their order using PaypalExpressCheckout
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class PaypalExpressCheckout extends PaymentMethodAbstract
{
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/webscr'
		, LIVE_URL = 'https://www.paypal.com/webscr';

	/**
	 * HTML to display for a given payment gateway
	 * on the checkout page, when the user is prompted to
	 * choose his preferred payment method
	 *
	 * @return string
	 */
	public function getSelectPaymentHtml()
    {
        return '<img src="https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif"
                    align="left"
                    style="margin-right:7px;">
                    <span style="font-size:11px; font-family: Arial, Verdana;">
                    The safer, easier way to pay.
                    </span>';
    }

	/**
	 * Returns cart items in a format useful for the
	 * API request
	 *
	 * Also adds discount as a cart item, since this is
	 * how Paypal expects to receive discounts
	 *
	 * @param Cart $cart
	 * @return array
	 */
	private function getItemsFromCart(Cart $cart)
    {
        $items = array();
        foreach($cart->getProducts() as $product)
        {
            $item = new stdClass();
            $item->name = $product->title;
            $item->number = $product->sku;
            $item->description = substr($product->short_description, 0, 127);
            $item->amount = ShopDisplay::numberFormat($product->price);
            $item->quantity = $product->quantity;
            $items[] = $item;
        }

		if($cart->getDiscountAmount() > 0)
		{
			$item = new stdClass();
			$item->name = 'Discount';
			$item->description = $cart->getDiscountName();
			$item->amount = -1 * $cart->getDiscountAmount();
			$item->quantity = 1;
			$items[] = $item;
		}
        return $items;
    }

	/**
	 * Calls the Paypal API on submit of the checkout
	 * form in order to initialize the payment process
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return bool
	 */
	public function onCheckoutFormSubmit(Cart $cart, &$error_message)
    {
        $success_url = $this->getPlaceOrderPageUrl();
        $cancel_url = $this->getCheckoutPageUrl();

        $paypalAPI = new PaypalExpressCheckoutAPI( $this->gateway_api == PaypalExpressCheckout::LIVE_URL
			, $this->api_username
            , $this->api_password
            , $this->signature
        );

        // Get shop info
        $shop_info = new ShopInfo($cart->module_srl);

        // Prepare cart info
        $items = $this->getItemsFromCart($cart);

        $paypalAPI->setExpressCheckout(
            $items
            , ShopDisplay::numberFormat($cart->getItemTotal() - $cart->getDiscountAmount())
            , 0
            , ShopDisplay::numberFormat($cart->getShippingCost())
            , ShopDisplay::numberFormat($cart->getTotal())
            , $shop_info->getCurrency()
            , $success_url
            , $cancel_url);

        if(!$paypalAPI->success)
        {
            $error_message = $paypalAPI->error_message;
            return FALSE;
        }
        else
        {
            // Redirect to PayPal login
            $this->redirect($this->gateway_api
                            . '?cmd=_express-checkout'
                            . '&token=' . $paypalAPI->token);
        }
    }


	/**
	 * Initializes data needed for the frontend payment
	 * form, when the Place order form is displayed
	 */
	public function onPlaceOrderFormLoad()
    {
        $token = Context::get('token');
        $paypalAPI = new PaypalExpressCheckoutAPI($this->gateway_api == PaypalExpressCheckout::LIVE_URL
			, $this->api_username
            , $this->api_password
            , $this->signature
        );
        $customer_info = $paypalAPI->getExpressCheckoutDetails($token);
        Context::set('payer_id', $customer_info['PAYERID']);
    }

	/**
	 * Calls the Paypal API for executing the payment
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return bool|mixed
	 */
	public function processPayment(Cart $cart, &$error_message)
    {
        $payer_id = Context::get('payer_id');
        $token = Context::get('token');

        $paypalAPI = new PaypalExpressCheckoutAPI($this->gateway_api == PaypalExpressCheckout::LIVE_URL
			, $this->api_username
            , $this->api_password
            , $this->signature
        );

        // Get shop info
        $shop_info = new ShopInfo($cart->module_srl);

        // Prepare cart info
        $items = $this->getItemsFromCart($cart);

        $paypalAPI->doExpressCheckoutPayment($token
            , $payer_id
            , $items
            , ShopDisplay::numberFormat($cart->getItemTotal())
            , 0
            , ShopDisplay::numberFormat($cart->getShippingCost())
            , ShopDisplay::numberFormat($cart->getTotal())
            , $shop_info->getCurrency()
        );

        if(!$paypalAPI->success)
        {
            $error_message = $paypalAPI->error_message;
            return FALSE;
        }
        else
        {
            Context::set('payment_status', $paypalAPI->payment_status);
            return TRUE;
        }
    }

	/**
	 * Make sure all mandatory fields are set
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(isset($this->api_username)
			&& isset($this->api_password)
			&& isset($this->gateway_api)
			&& isset($this->signature))
		{
			$error_message = 'msg_paypal_express_missing_fields';
			return TRUE;
		}
		return FALSE;
	}
}

/**
 * Wrapper for calling Paypal API
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class PaypalExpressCheckoutAPI extends APIAbstract
{
    const SANDBOX_API_URL = 'https://api-3t.sandbox.paypal.com/nvp'
		, LIVE_API_URL = 'https://api-3t.paypal.com/nvp';

	/**
	 * URL to which all data will be posted
	 *
	 * @var null|string
	 */
	private $gateway_api = NULL;

	/**
	 * Info included with all requests
	 *
	 * @var array
	 */
	private $setup = array(
        'VERSION' => '94'
    );

	/**
	 * Data to be posted
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor
	 *
	 * @param $is_live
	 * @param $api_username
	 * @param $api_password
	 * @param $signature
	 */
	public function __construct($is_live, $api_username, $api_password, $signature)
    {
		if($is_live)
		{
			$this->gateway_api = PaypalExpressCheckoutAPI::LIVE_API_URL;
		}
		else
		{
			$this->gateway_api = PaypalExpressCheckoutAPI::SANDBOX_API_URL;
		}
        $this->setup['USER'] = $api_username;
        $this->setup['PWD'] = $api_password;
        $this->setup['SIGNATURE'] = $signature;
    }

	/**
	 * Transforms the cart items to the format expected by the API
	 *
	 * @param $items
	 */
	private function addItemsInfo($items)
    {
        $i = 0;
        foreach($items as $item)
        {
            $this->data["L_PAYMENTREQUEST_0_NAME" . $i] = $item->name;
            $this->data["L_PAYMENTREQUEST_0_NUMBER" . $i]  = $item->number;
            $this->data["L_PAYMENTREQUEST_0_DESC" . $i]  = $item->description;
            $this->data["L_PAYMENTREQUEST_0_AMT" . $i]  = $item->amount;
            $this->data["L_PAYMENTREQUEST_0_QTY" . $i]  = $item->quantity;
            $i++;
        }
    }

	/**
	 * Adds cart Items total
	 *
	 * @param $items_total
	 */
	private function addItemsTotal($items_total)
    {
        $this->data["PAYMENTREQUEST_0_ITEMAMT"] = $items_total;
    }

	/**
	 * Adds tax info
	 *
	 * @param $tax_total
	 */
	private function addTaxTotal($tax_total)
    {
        $this->data["PAYMENTREQUEST_0_TAXAMT"] = $tax_total;
    }

	/**
	 * Sets the flag for not displaying the shipping address on the Paypal
	 * website (using the one provided by XE Shop instead)
	 */
	private function disablePaypalShippingAddresses()
	{
		$this->data["NOSHIPPING"] = 1;
	}

	/**
	 * Adds shipping total cost
	 *
	 * @param $shipping_total
	 */
	private function addShippingTotal($shipping_total)
    {
        $this->data["PAYMENTREQUEST_0_SHIPPINGAMT"] = $shipping_total;
    }

	/**
	 * Adds order total
	 *
	 * @param $order_total
	 */
	private function addOrderTotal($order_total)
    {
        $this->data["PAYMENTREQUEST_0_AMT"] = $order_total;
    }

	/**
	 * Adds currency
	 *
	 * @param string $currency
	 */
	private function addCurrency($currency = 'USD')
    {
        $this->data['PAYMENTREQUEST_0_CURRENCYCODE'] = $currency;
    }

	/**
	 * Adds payment action
	 *
	 * @param string $action
	 */
	private function addPaymentAction($action = 'Sale')
    {
        $this->data['PAYMENTREQUEST_0_PAYMENTACTION'] = $action;
    }

	/**
	 * Calls the setExpressCheckout method of the Paypal API
	 *
	 * @param        $items
	 * @param        $item_total
	 * @param        $tax_total
	 * @param        $shipping_total
	 * @param        $order_total
	 * @param string $currency
	 * @param        $success_url
	 * @param        $cancel_url
	 */
	public function setExpressCheckout(
        $items
        , $item_total
        , $tax_total
        , $shipping_total
        , $order_total
        , $currency = 'USD'
        , $success_url
        , $cancel_url
    ){

        $this->data['METHOD'] = 'SetExpressCheckout';

        if($items) $this->addItemsInfo($items);
        if($item_total) $this->addItemsTotal($item_total);
        if($tax_total) $this->addTaxTotal($tax_total);
        if($shipping_total) $this->addShippingTotal($shipping_total);
		$this->disablePaypalShippingAddresses();
        $this->addOrderTotal($order_total);
        $this->addCurrency($currency);
        $this->addPaymentAction();

        $this->data['RETURNURL'] = $success_url;
        $this->data['CANCELURL'] = $cancel_url;

        $response = $this->request($this->gateway_api, array_merge($this->setup, $this->data));

        unset($this->data);
        $this->data = array();

        $this->ack = $response['ACK'];
        if($this->ack != 'Success')
        {
            $this->success = FALSE;
            $this->error_message = $response['L_SHORTMESSAGE0'] . ' (' .  $response['L_ERRORCODE0'] . ' ' . $response['L_LONGMESSAGE0'] . ')';
        }
        else
        {
            $this->success = TRUE;
            $this->token = $response['TOKEN'];
            $this->correlation_id = $response['CORRELATIONID'];
        }
    }

	/**
	 * Calls the getExpressCheckoutDetails method of the Paypal API
	 *
	 * @param $token
	 * @return array
	 */
	public function getExpressCheckoutDetails($token)
    {
        $this->data['METHOD'] = 'GetExpressCheckoutDetails';
        $this->data['TOKEN'] = $token;

        $response = $this->request($this->gateway_api, array_merge($this->setup, $this->data));

        unset($this->data);
        $this->data = array();
        return $response;
    }

	/**
	 * Calls the doExpressCheckoutPayment method of the Paypal API
	 *
	 * @param        $token
	 * @param        $payer_id
	 * @param        $items
	 * @param        $item_total
	 * @param        $tax_total
	 * @param        $shipping_total
	 * @param        $order_total
	 * @param string $currency
	 * @internal        param \the $amount format must have a decimal point with exactly
	 *                  two digits to the right and an optional thousands
	 *                  separator to the left, which must be a comma.
	 * @return bool
	 */
    public function doExpressCheckoutPayment($token, $payer_id
        , $items
        , $item_total
        , $tax_total
        , $shipping_total
        , $order_total
        , $currency = 'USD')
    {
        $this->data['METHOD'] = 'DoExpressCheckoutPayment';
        $this->data['TOKEN'] = $token;
        $this->data['PAYERID'] = $payer_id;

        if($items) $this->addItemsInfo($items);
        if($item_total) $this->addItemsTotal($item_total);
        if($tax_total) $this->addTaxTotal($tax_total);
        if($shipping_total) $this->addShippingTotal($shipping_total);
        $this->addOrderTotal($order_total);
        $this->addCurrency($currency);
        $this->addPaymentAction();

        $response = $this->request($this->gateway_api, array_merge($this->setup, $this->data));

        unset($this->data);
        $this->data = array();

        $this->ack = $response['ACK'];
        if($this->ack != 'Success')
        {
            $this->success = FALSE;
            $this->error_message = $response['L_SHORTMESSAGE0'] . ' (' .  $response['L_ERRORCODE0'] . ' ' . $response['L_LONGMESSAGE0'] . ')';
        }
        else
        {
            $this->success = TRUE;
            $this->token = $response['TOKEN'];
            $this->correlation_id = $response['CORRELATIONID'];

            // TODO Retrieve payment info and send to final form user sees
        }

        return TRUE;
    }

	/**
	 * Makes a request and formats the returned data as an array
	 *
	 * @param API     $url
	 * @param Request $data
	 * @return array|mixed
	 */
	public function request($url, $data)
    {
        $response = parent::request($url, $data);

        $response_array = array();
        parse_str($response, $response_array);
        return $response_array;
    }


}