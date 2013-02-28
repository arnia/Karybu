<?php
/**
 * File containing the Authorize.NET payment plugin class
 */
require_once dirname(__FILE__) . '/../PaymentMethodAbstract.php';
require_once dirname(__FILE__) . '/anet_php_sdk/AuthorizeNet.php';

/**
 * Class for integrating Authorize.NET payment gateway in XE Shop
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class Authorize extends PaymentMethodAbstract
{
    const APPROVED = 1,
        DECLINED = 2,
        ERROR = 3,
        HELD_FOR_REVIEW = 4;

	/**
	 * User friendly name for this payment plugin
	 *
	 * @return string
	 */
	public function getDisplayName()
    {
        return 'Authorize.net AIM';
    }

	/**
	 * HTML to display on the checkout page to the user
	 * when listing the available payment methods
	 *
	 * @return string
	 */
	public function getSelectPaymentHtml()
    {
        return '<img src="modules/shop/plugins_payment/authorize/visa_mastercard.gif"
                    align="left"
                    style="margin-right:7px;">
                    <span style="font-size:11px; font-family: Arial, Verdana;">
                    Credit card
                    </span>';
    }

//    public function authorizePayment(Cart $cart)
//    {
//        $data = array();
//
//        // Transaction info
//        $data['x_type'] = 'AUTH_ONLY';
//        $data['x_card_num'] = '4007000000027';
//        $data['x_exp_date'] = '201412';
//        $data['x_card_code'] = '278';
//
//        // Setup login info
//        $data['x_login'] = $this->api_login_id;
//        $data['x_tran_key'] = $this->transaction_key;
//
//        // Setup Advanced Integration Method values (AIM)
//        $data['x_version'] = '3.1';
//        $data['x_delim_data'] = 'TRUE';
//        $data['x_delim_char'] = '|';
//        $data['x_relay_response'] = 'FALSE';
//
//        // Indicate transaction method; CC = credit card; another option would be ECHECK
//        $data['x_method'] = 'CC';
//
//        // Setup order information
//        // TODO Retrieve values from cart
//        $data['x_amount'] = '100';
//        $data['x_invoice_num'] = '1';
//        $data['x_cust_id'] = '1';
//
//        // Convert data to name-value pairs string
//        $post_string = '';
//        foreach( $data as $k => $v ) {
//            $post_string .= "$k=" . urlencode($v) . "&";
//        }
//        $post_string = rtrim($post_string, '& ');
//
//        // Request
//        $request = curl_init($this->gateway_api_url);
//        curl_setopt($request, CURLOPT_HEADER, 0);
//        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
//        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
//        $response = curl_exec($request);
//        curl_close ($request);
//
//        $response_array = explode($data["x_delim_char"], $response);
//
//        var_dump($response_array);
//        exit();
//
//    }

	/**
	 * Process the payment
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return bool|mixed
	 */
	public function processPayment(Cart $cart, &$error_message)
    {
		$cc_number = Context::get('cc_number');
		$cc_exp_month = Context::get('cc_exp_month');
		$cc_exp_year = Context::get('cc_exp_year');
		$cc_cvv = Context::get('cc_cvv');

		// Unset credit card info so that XE won't put it in session
		Context::set('cc_number', NULL);
		Context::set('cc_exp_month', NULL);
		Context::set('cc_exp_year', NULL);
		Context::set('cc_cvv', NULL);

		if(!$cc_number)
		{
			$error_message = "Please enter you credit card number"; return FALSE;
		}
		if(!$cc_exp_month || !$cc_exp_year)
		{
			$error_message = "Please enter you credit card expiration date"; return FALSE;
		}
		if(!$cc_cvv)
		{
			$error_message = "Please enter you credit card verification number"; return FALSE;
		}

		$cc_number = str_replace(array(' ', '-'), '', $cc_number);
		if (!preg_match ('/^4[0-9]{12}(?:[0-9]{3})?$/', $cc_number) // Visa
			&& !preg_match ('/^5[1-5][0-9]{14}$/', $cc_number) // MasterCard
			&& !preg_match ('/^3[47][0-9]{13}$/', $cc_number) // American Express
			&& !preg_match ('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cc_number) //Discover
		){
			$error_message = 'Please enter your credit card number!';
		}

		$cc_exp = sprintf('%02d%d', $cc_exp_month, $cc_exp_year);

        $transaction = new AuthorizeNetAim($this->api_login_id, $this->transaction_key);
		// 1. Set payment info
		$transaction->amount = $cart->getTotal();
        $transaction->card_num = $cc_number;
        $transaction->exp_date = $cc_exp;
		$transaction->invoice_num = $cart->cart_srl;

		// 2. Set billing address info
		$transaction->first_name = $cart->getCustomerFirstname();
		$transaction->last_name = $cart->getCustomerLastname();
		$transaction->company = $cart->getBillingAddress()->company;
		$transaction->address = $cart->getBillingAddress()->address;
		$transaction->city = $cart->getBillingAddress()->city;
		$transaction->zip = $cart->getBillingAddress()->postal_code;
		$transaction->country = $cart->getBillingAddress()->country;
		$transaction->email = $cart->getBillingAddress()->email;

		// 3. Set shipping address info
		$transaction->ship_to_first_name = $cart->getShippingAddress()->firstname;
		$transaction->ship_to_last_name = $cart->getShippingAddress()->lastname;
		$transaction->ship_to_company = $cart->getShippingAddress()->company;
		$transaction->ship_to_address = $cart->getShippingAddress()->address;
		$transaction->ship_to_city = $cart->getShippingAddress()->city;
		$transaction->ship_to_zip = $cart->getShippingAddress()->postal_code;
		$transaction->ship_to_country = $cart->getShippingAddress()->country;

        $response = $transaction->authorizeAndCapture();

        if ($response->approved) {
            return TRUE;
        } else {
			ShopLogger::log("Authorize.NET transaction failed: " . print_r($response, TRUE));
            $error_message = "There was a problem with charging your credit card; Please try again or try a different payment method";
            return FALSE;
        }
    }

	/**
	 * Make sure all mandatory fields are set
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->api_login_id) || !isset($this->transaction_key) || !isset($this->gateway_api_url))
		{
			$error_message = 'msg_authorize_missing_fields';
			return FALSE;
		}
		return TRUE;
	}
}

?>