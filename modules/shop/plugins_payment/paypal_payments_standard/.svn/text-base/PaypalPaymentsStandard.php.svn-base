<?php
/**
 * File containing classes used for integrating Paypal Payments Standard with XE Shop
 */
/**
 * Plugin for doing payments in XE Shop
 * using Paypal Payments Standard
 *
 * https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_WebsitePaymentsStandard_IntegrationGuide.pdf
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class PaypalPaymentsStandard extends PaymentMethodAbstract
{
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr'
        , LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * Paypal form action
     *
     * @return string
     */
    public function getPaymentFormAction()
    {
        return $this->gateway_api;
    }

    /**
     * Text that will be displayed on the payment form Submit button
     *
     * @return string
     */
    public function getPaymentSubmitButtonText()
    {
        return "Proceed to PayPal.com to pay";
    }

    /**
     * Used for getting the money from the customer
     *
     * Nothing happens here since payment is done on the Paypal website
     * And the payment confirmation notification comes via IPN
     *
     * @param Cart $cart
     * @param $error_message
	 * @return mixed|void
	 */
    public function processPayment(Cart $cart, &$error_message)
    {
    }

	/**
	 * Page where user is redirected back to after
	 * he completed the payment on the Paypal website
	 *
	 * If an order has not been created, we create it now
	 * If payment is complete, we update order status to Processing
	 *
	 * If an error occurred, we show it to the user
	 *
	 * @param $cart
	 * @param $module_srl
	 * @throws NetworkErrorException
	 * @return void
	 */
    public function onOrderConfirmationPageLoad($cart, $module_srl)
    {
        // Retrieve unique transaction id
        $tx_token = Context::get('tx');

        // If no transaction token was retrieved
        // or the user did not configure his PDT (Payment Data Transfer) token in backend
        // do nothing
        if(!$tx_token || !$this->pdt_token)
        {
            // TODO What do we do in this case?
            return;
        }

        if(!$order = $this->orderCreatedForThisTransaction($tx_token))
        {
            if($this->thisTransactionWasAlreadyProcessedAndWasInvalid($cart, $tx_token))
            {
                $this->redirectUserToOrderUnsuccessfulPageAndShowHimTheErrorMessage($cart->getTransactionErrorMessage());
                return;
            }

            // Retrieve payment info from Paypal
            $response = $this->getTransactionInfoFromPDT($tx_token);
            if($response->requestWasSuccessful())
            {
                $this->createNewOrderAndDeleteExistingCart($cart, $tx_token);
            }
            else
            {
                // We couldn't retrieve transaction info from Paypal
                ShopLogger::log("PDT request FAIL: " .print_r($response));
                throw new NetworkErrorException("There was some error from PDT");
            }
        }
        else
        {
            // Order already exists for this transaction, so we'll just display it
            // skipping any requests to paypal
            Context::set('order_srl', $order->order_srl);
            return;
        }
    }

	/**
	 * Given a transaction id, checks if an order was created or not for it
	 * (from an IPN call, for instance)
	 *
	 * @param $transaction_id
	 * @return boolean
	 */
    private function orderCreatedForThisTransaction($transaction_id)
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->getOrderByTransactionId($transaction_id);
        return $order;
    }

    /**
     * Checks if a transaction was already processed but was invalid
     * causing the order not to be created;
     * Thus, even though there is no order created, we should not parse this again
     */
    private function thisTransactionWasAlreadyProcessedAndWasInvalid(Cart $cart, $transaction_id)
    {
        return $cart->getTransactionId()
            && $cart->getTransactionId() == $transaction_id;
    }

	/**
	 * Redirects the user to an error page, informing him why the payment failed
	 *
	 * @param $error_message
	 */
	private function redirectUserToOrderUnsuccessfulPageAndShowHimTheErrorMessage($error_message)
    {
        $shopController = getController('shop');
        $shopController->setMessage($error_message, "error");
        $this->redirect($this->getPlaceOrderPageUrl());
    }

    /**
     * Retrieve payment info from Paypal through
     * Payment Data Transfer
     */
    private function getTransactionInfoFromPDT($tx_token)
    {
        $params = array();
        $params['cmd'] = '_notify-synch';
        $params['tx'] = $tx_token;
        $params['at'] = $this->pdt_token;

        $paypalAPI = new PaypalPaymentsStandardAPI();
        $response = $paypalAPI->request($this->gateway_api, $params);
        $response_array = explode("\n", $response);
        return new PDTResponse($response_array);
    }

    /**
     * Handles all IPN notifications from Paypal
     */
    public function notify($cart)
    {
        // 1. Retrieve all POST data received and post back to paypal, to make sure
        // the request sender is not fake

        // Do not retrieve data with Context::getRequestVars() because it skips empty values
        // causing the Paypal validation to fail
        $args = $_POST;
        if(__DEBUG__)
        {
            ShopLogger::log("Received IPN Notification: " . http_build_query($args));
        }

        $response = $this->postDataBackToPaypalToValidateSenderIdentity($args);

        if($response->isVerified())
        {
            ShopLogger::log("Successfully validated IPN data");

            $payment_info = $this->getIPNPaymentInfo($args);

            if(!$payment_info->isRelatedToCartPayment())
                return;

            // 2. If the source of the POST is correct, we now need to check that data is also valid
            if(!$order = $this->orderCreatedForThisTransaction($payment_info->txn_id))
            {
                // check that receiver_email is your Primary PayPal email
                if(!$payment_info->paymentReceiverIsMe($this->business_account))
                {
                    ShopLogger::log("Possible fraud - invalid receiver email: " . $payment_info->receiver_email);
                    $this->markTransactionAsFailedInUserCart(
                        $payment_info->cart_srl,
                        $payment_info->txn_id,
                        "There was a problem processing your payment. Your order could not be completed."
                    );
                    return;
                }

                // check the payment_status is Completed
                if(!$payment_info->paymentIsComplete())
                {
                    ShopLogger::log("Payment is not completed. Payment status [" . $payment_info->payment_status. "] received");
                    $this->markTransactionAsFailedInUserCart(
                        $payment_info->cart_srl,
                        $payment_info->txn_id,
                        "Your payment was not completed. Your order was not created."
                    );
                    return;
                }

                $cart = new Cart($payment_info->cart_srl);
                if(!$payment_info->paymentIsForTheCorrectAmount($cart->getTotal(), $cart->getCurrency()))
                {
                    ShopLogger::log("Invalid payment. " . PHP_EOL
                        . "Payment amount [" . $payment_info->payment_amount . "] instead of " . $cart->getTotal() . PHP_EOL
                        . "Payment currency [" . $payment_info->payment_currency . "] instead of " . $cart->getCurrency()
                    );
                    $this->markTransactionAsFailedInUserCart(
                        $payment_info->cart_srl,
                        $payment_info->txn_id,
                        "Your payment was invalid. Your order was not created."
                    );
                    return;
                }

                // 3. If the source of the POST is correct, we can now use the data to create an order
                // based on the message received
                $this->createNewOrderAndDeleteExistingCart($cart, $payment_info->txn_id);
            }
        }
        else
        {
            ShopLogger::log("Invalid IPN data received: " . $response);
        }

    }

    /**
     * Post all received data to paypal for it to confirm
     * it was issued by Paypal and not by someone else
     */
    private function postDataBackToPaypalToValidateSenderIdentity($posted_data)
    {
        $paypalAPI = new PaypalPaymentsStandardAPI();
        $paypal_info = $paypalAPI->decodeArray($posted_data);
        $decoded_args = array_merge(array('cmd' => '_notify-validate'), $paypal_info);

        $response = $paypalAPI->request($this->gateway_api, $decoded_args);
        return new IPNIdentityValidationResponse($response);
    }

    /**
     * Retrieve raw post data and return prettified data
     */
    private function getIPNPaymentInfo($posted_data)
    {
        $paypalAPI = new PaypalPaymentsStandardAPI();
        $paypal_info = $paypalAPI->decodeArray($posted_data);
        return new IPNPaymentInfo($paypal_info);
    }

	/**
	 * Make sure all mandatory fields are set
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->business_account) || !isset($this->pdt_token) || !isset($this->gateway_api))
		{
			$error_message = 'msg_paypal_standard_missing_fields';
			return FALSE;
		}
		return TRUE;
	}
}

/**
 * Wraps commonly used properties sent by IPN
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class IPNPaymentInfo
{
    public $payment_status;
    public $payment_amount;
    public $payment_currency;
    public $txn_id;
    public $txn_type;
    public $receiver_email;
    public $payer_email;
    public $cart_srl;

	/**
	 * @param $paypal_info
	 */
	public function __construct($paypal_info)
    {
        $this->payment_status = $paypal_info['payment_status'];
        $this->payment_amount = $paypal_info['mc_gross'];
        $this->payment_currency = $paypal_info['mc_currency'];
        $this->txn_id = $paypal_info['txn_id'];
        $this->txn_type = $paypal_info['txn_type'];
        $this->receiver_email = $paypal_info['receiver_email'];
        $this->payer_email = $paypal_info['payer_email'];
        $this->cart_srl = $paypal_info['custom'];
    }

    /**
     * The IPN can send notifications related to all kinds of events
     * like recurring payments, refunds and such
     *
     * We only need to answer to notifications related to paying cart products
     * There is nothing we will do for the rest of the cases at the moment;
     * settlements must be done by hand for special situations
     *
     * @return bool
     */
    public function isRelatedToCartPayment()
    {
        return $this->txn_type == 'cart';
    }

	/**
	 * Checks that the payee sent by IPN is valid
	 *
	 * @param $my_business_account_email_address
	 * @return bool
	 */
	public function paymentReceiverIsMe($my_business_account_email_address)
    {
        return $this->receiver_email == $my_business_account_email_address;
    }

	/**
	 * Checks that payment was executed successfully
	 *
	 * @return bool
	 */
	public function paymentIsComplete()
    {
        return $this->payment_status == 'Completed';
    }

	/**
	 * Checks that the total amount received from the IPN is correct
	 *
	 * @param $cart_total_amount
	 * @param $shop_currency
	 * @return bool
	 */
	public function paymentIsForTheCorrectAmount($cart_total_amount, $shop_currency)
    {
        return $cart_total_amount == $this->payment_amount
            && $shop_currency == $this->payment_currency;
    }
}

/**
 * Models a response from IPN
 */
class IPNIdentityValidationResponse
{
    public $response;

	/**
	 * @param $response
	 */
	public function __construct($response)
    {
        $this->response = $response;
    }

	/**
	 * Checks that the response validated the data
	 *
	 *
	 * @return bool
	 */
	public function isVerified()
    {
        return $this->response == 'VERIFIED';
    }
}

/**
 * Models a response from PDT
 */
class PDTResponse
{
    public $response_array = NULL;

	/**
	 * Constructor
	 *
	 * @param $response_array
	 */
	public function __construct($response_array)
    {
        $this->response_array = $response_array;
    }

	/**
	 * Checks if the request was successful
	 *
	 * @return bool
	 */
	public function requestWasSuccessful()
    {
        return $this->response_array[0] == 'SUCCESS';
    }
}

/**
 * Wrapper for the Paypal Payments Standard API
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class PaypalPaymentsStandardAPI extends APIAbstract
{
	/**
	 * Calls a given function for every element in $data
	 *
	 * @param $data
	 * @param $function_name
	 * @return array
	 */
	private function processArray($data, $function_name)
    {
        $new_data = array();
        $keys = array_keys($data);
        foreach($keys as $key)
        {
            $new_data[$key] = $function_name($data[$key]);
        }
        return $new_data;
    }

	/**
	 * Decodes all elements of an array
	 *
	 * @param $data
	 * @return array
	 */
	public function decodeArray($data)
    {
        return $this->processArray($data, 'urldecode');
    }

	/**
	 * Encodes all elements of an array
	 *
	 * @param $data
	 * @return array
	 */
	public function encodeArray($data)
    {
        return $this->processArray($data, 'urlencode');
    }
}

