<?php
/**
 * File containing the PaymentMethodAbstract class
 */
/**
 * Base class that all payment plugins must extend
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
abstract class PaymentMethodAbstract extends AbstractPlugin
{
    static protected $frontend_form = 'form_payment.html';
    static protected $backend_form = 'form_admin_settings.html';

	/**
	 * Returns the folder where the current payment plugin is located
	 *
	 * @return string
	 */
	public function getPaymentMethodDir()
    {
        return $this->getPluginDir();
    }

	/**
	 * Compiles form template files into HTML for display
	 *
	 * @param $filename
	 * @return string
	 */
	private function getFormHtml($filename)
    {
        if(!file_exists($this->getPaymentMethodDir() . DIRECTORY_SEPARATOR . $filename))
        {
            return '';
        }

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->getPaymentMethodDir(), $filename);
    }

	/**
	 * Returns the HTML to display on the checkout form
	 * to gather info from users - like credit card number
	 *
	 * @return string
	 */
	public function getPaymentFormHTML()
    {
        return $this->getFormHtml(self::$frontend_form);
    }

	/**
	 * Returns the HTML for the backend admin form
	 * used to set up the payment method settings
	 *
	 * @return string
	 */
	public function getAdminSettingsFormHTML()
    {
        return $this->getFormHtml(self::$backend_form);
    }

	/**
	 * Address where the payment info is posted
	 *
	 * Default to a method in the shopController;
	 * can be overridden to post the data directly to
	 * a payment provider - like Paypal
	 *
	 * @return string
	 */
	public function getPaymentFormAction()
    {
        return './';
    }

	/**
	 * Text to be displayed on the Submit button
	 * of the payment form
	 *
	 * @return string
	 */
	public function getPaymentSubmitButtonText()
    {
        return "Place your order";
    }

	/**
	 * HTML to display for a given payment gateway
	 * on the checkout page, when the user is prompted to
	 * choose his preferred payment method
	 *
	 * Default to the payment method display name, but can be
	 * overridden to return any HTML
	 *
	 * @return string
	 */
	public function getSelectPaymentHtml()
    {
        return $this->display_name;
    }

	/**
	 * URL of the checkout page
	 * (Step 1 of the checkout process)
	 *
	 * @return string
	 */
	protected function getCheckoutPageUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'dispShopCheckout'
            , 'error_return_url', ''
        );
    }

	/**
	 * URL of the Place order page
	 * (Step 2 of the checkout process)
	 *
	 * @return string
	 */
	protected function getPlaceOrderPageUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'dispShopPlaceOrder'
            , 'payment_method_name', $this->getName()
            , 'error_return_url', ''
        );
    }

	/**
	 * URL of the Order confirmation page
	 * (Step 3 of the checkout process)
	 *
	 * @return string
	 */
	public function getOrderConfirmationPageUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'dispShopOrderConfirmation'
            , 'payment_method_name', $this->getName()
            , 'error_return_url', ''
        );
    }

    /**
     * Get URL for IPN notifications
     */
    public function getNotifyUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'procShopPaymentNotify'
            , 'payment_method_name', $this->getName()
            , 'error_return_url', ''
        );
    }

	/**
	 * Redirect the user to another page
	 *
	 * @param $url
	 */
	protected function redirect($url)
    {
        header('location:' . $url);
        exit();
    }

	/**
	 * Hook executed when the checkout form is submitted
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return bool
	 */
	public function onCheckoutFormSubmit(Cart $cart, &$error_message)
    {
        return TRUE;
    }

	/**
	 * Hook executed when the Place order page is displayed
	 */
	public function onPlaceOrderFormLoad()
    {

    }

	/**
	 * Code for executing the payment
	 *
	 * @param Cart $cart
	 * @param      $error_message
	 * @return mixed
	 */
	abstract public function processPayment(Cart $cart, &$error_message);

	/**
	 * Hook executed when the Order confirmation page is loaded
	 *
	 * @param $cart
	 * @param $module_srl
	 */
	public function onOrderConfirmationPageLoad($cart, $module_srl)
    {
    }

	/**
	 * Method used for IPN notifications; this is the code
	 * that will execute when a new IPN notification is received
	 */
	public function notify()
    {

    }

	/**
	 * Store an error message in the cart in order to
	 * let the user know there was a problem with his
	 * payment; used for errors that were received by IPN
	 *
	 * @param $cart_srl
	 * @param $transaction_id
	 * @param $error_message
	 */
	protected function markTransactionAsFailedInUserCart($cart_srl, $transaction_id, $error_message)
	{
		$cart = new Cart($cart_srl);
		$cart->setExtra("transaction_id", $transaction_id);
		$cart->setExtra("transaction_message", $error_message);
		$cart->save();
	}

	/**
	 * Creates a new order and deletes the corresponding cart
	 *
	 * @param $cart
	 * @param $transaction_id
	 */
	protected function createNewOrderAndDeleteExistingCart($cart, $transaction_id)
	{
		$order = new Order($cart);
		$order->transaction_id = $transaction_id;
		$order->save(); //obtain srl
		$order->saveCartProducts($cart);
		Order::sendNewOrderEmails($order->order_srl);
		$cart->delete();

		Context::set('order_srl', $order->order_srl);
		// Override cart, otherwise it would still show up with products
		Context::set('cart', NULL);
	}

}