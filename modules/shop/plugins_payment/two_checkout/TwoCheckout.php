<?php
/**
 * File containing the TwoCheckout class
 */
/**
 * Class used for integrating 2Checkout with XE Shop
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class TwoCheckout extends PaymentMethodAbstract
{
	const GATEWAY_API_URL = 'https://www.2checkout.com/checkout/spurchase';

	/**
	 * Checks to see if demo mode is enabled or not in XE settings
	 *
	 * @return bool
	 */
	public function isLive()
	{
		if($this->use_demo_mode === 'Y') return FALSE;
		return TRUE;
	}

	/**
	 * 2Checkout form action
	 *
	 * @return string
	 */
	public function getPaymentFormAction()
	{
		return TwoCheckout::GATEWAY_API_URL;
	}

	/**
	 * Text that will be displayed on the payment form Submit button
	 *
	 * @return string
	 */
	public function getPaymentSubmitButtonText()
	{
		return "Proceed to 2checkout.com to pay";
	}

	/**
	 * Customize the way 2checkout is displayed on the checkout page
	 * as a payment option
	 *
	 * @return string
	 */
	public function getSelectPaymentHtml()
	{
		return '<img src="modules/shop/plugins_payment/two_checkout/paymentlogoshorizontal.png"
                    align="left"
                    style="margin-right:7px;">';
	}

	/**
	 * Retrieve payment info from 2checkout and create a new order
	 *
	 * @param $cart
	 * @param $module_srl
	 * @throws PaymentProcessingException
	 * @return void
	 */
	public function onOrderConfirmationPageLoad($cart, $module_srl)
	{
		// first of all, check that the data received
		// is actually from 2checkout
		$key = Context::get('key');

		// Create expected key
		$secret_word = $this->secret_word;
		$account_number = $this->sid;
		$order_number = Context::get('order_number');
		$total = Context::get('total');
		$expected_key = strtoupper(md5($secret_word . $account_number . $order_number . $total));

		// Check if using demo mode, since on demo, all responses have invalid key
		$is_demo = Context::get('demo') == 'Y';

		if(strtoupper($key) != $expected_key && !$is_demo)
		{
			ShopLogger::log("Invalid 2 checkout message received - key " . $key . ' ' . print_r($_REQUEST, TRUE));
				throw new PaymentProcessingException("There was a problem processing your transaction");
		}

		// We need a unique identifier for this transaction - we will use order number
		$transaction_id = $order_number;

		$order_repository = new OrderRepository();

		// Check if order has already been created for this transaction
		$order = $order_repository->getOrderByTransactionId($transaction_id);
		if(!$order) // If not, create it
		{
			$this->createNewOrderAndDeleteExistingCart($cart, $transaction_id);
		}
		else
		{
			Context::set('order_srl', $order->order_srl);
		}
	}

	/**
	 * Handle all incoming IPN notifications from 2checkout
	 */
	public function notify()
	{
		// Check the sender is 2Checkout
		$key = Context::get('md5_hash');

		$sale_id = Context::get('sale_id');
		$vendor_id = $this->sid;
		$invoice_id = Context::get('invoice_id');
		$secret_word = $this->secret_word;
		$expected_key = strtoupper(md5($sale_id . $vendor_id . $invoice_id . $secret_word));

		if(strtoupper($key) != $expected_key)
		{
			ShopLogger::log("Invalid 2checkout IPN message received - key " . $key . ' ' . print_r($_REQUEST, TRUE));
			return;
		}

		$message_type = Context::get('message_type');
		if($message_type != 'ORDER_CREATED')
		{
			ShopLogger::log("Unsupported IPN 2checkout message received: " . print_r($_REQUEST, TRUE));
			return;
		}

		$cart_srl = Context::get('vendor_order_id');
		$transaction_id = $sale_id; // Hopefully, this is order number

		$order_repository = new OrderRepository();

		// Check if order has already been created for this transaction
		$order = $order_repository->getOrderByTransactionId($transaction_id);
		if(!$order) // If not, create it
		{
			$cart = new Cart($cart_srl);
			$this->createNewOrderAndDeleteExistingCart($cart, $transaction_id);
		}
	}

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @param string $error_message
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->sid)) return FALSE;
		if(!isset($this->secret_word)) return FALSE;
		return TRUE;
	}

	/**
	 * Nothing happens here since user is redirected to 2checkout's page
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return mixed|void
	 */
	public function processPayment(Cart $cart, &$error_message)
	{
	}
}