<?php
/**
 * File containing the ShippingMethodAbstract class
 */
/**
 * Base class that all shipping plugins must extend
 */
abstract class ShippingMethodAbstract extends AbstractPlugin
{
    public $shipping_info;
    protected $shipping_method_dir;
    static protected $template_file_name = 'template.html';

	/**
	 * Alias for getName; kept for backwards compatibility
	 *
	 * @return mixed
	 */
	public function getCode()
    {
        return $this->getName();
    }

	/**
	 * Returns the HTML of the shipping method settings
	 * form in the admin
	 *
	 * @return string
	 */
	public function getFormHtml()
    {
        if(!file_exists($this->getPluginDir() . DIRECTORY_SEPARATOR . self::$template_file_name))
        {
            return '';
        }

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->getPluginDir(), self::$template_file_name);
    }

	/**
	 * Checks to see if shipping method has more types (like Standard/Priority for example)
	 *
	 * @return bool
	 */
	public function hasVariants()
	{
		return FALSE;
	}


	/**
	 * Defines the variants of a certain shipping type
	 * For instance, for UPS we can have: Expedited, Saver etc.
	 */
	public function getVariants()
	{
		return array();
	}

    /**
     * Calculates shipping rates
     *
     * @param Cart $cart Shipping cart for which to calculate shipping; includes shipping address
	 * @param String $service Represents the specific service for which to calcualte shipping (e.g. Standard or Priority)
     */
    abstract public function calculateShipping(Cart $cart, $service = NULL);

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
		$variant = new stdClass();
		$variant->name = $this->getName();
		$variant->display_name = $this->getDisplayName();
		$variant->variant = NULL;
		$variant->variant_display_name = NULL;
		$variant->price = $this->calculateShipping($cart);
		return array($variant);
	}


}