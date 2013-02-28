<?php
/**
 * File containing the CashOnDelivery class
 */
/**
 * Allows the users to pay with cash on the delivery of their products
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class CashOnDelivery extends PaymentMethodAbstract
{
	/**
	 * Process the payment
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return bool|mixed
	 */
	public function processPayment(Cart $cart, &$error_message)
    {
        return TRUE;
    }

	/**
	 * Make sure all mandatory fields are set
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		return TRUE;
	}
}