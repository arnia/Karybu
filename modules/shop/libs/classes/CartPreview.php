<?php
/**
 * File containing the CartPreview class
 */
/**
 * Class used for cart displays
 *
 * The cart is displayed on many views on the frontend and all the views
 * need a set of specific info; this class encapsulates the fields most used
 * in order to hide the logic from the view template file.
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class CartPreview implements IProductItemsContainer
{
	/** @var \Cart Reference to the cart that is being displayed */
	private $cart;
	/** @var int Number of products that will be displayed (eg. just the first 2 products in cart) */
	private $products_to_show;

	/**
	 * Constructor
	 *
	 * @param Cart $cart
	 * @param int  $products_to_show
	 */
	public function __construct(Cart $cart, $products_to_show = 3)
	{
		$this->cart = $cart;
		$this->products_to_show = $products_to_show;
	}

	/**
	 * Returns the cart products list; if a limit is given, just the first N products are returned
	 *
	 * @return mixed
	 */
	public function getProducts()
	{
		return $this->cart->getProducts($this->products_to_show);
	}

	/**
	 * Returns total number of products in the crat
	 *
	 * @return mixed
	 */
	public function getCartProductsCount()
	{
		return $this->cart->count(TRUE);
	}

	/**
	 * Checks if cart has any products in it or not
	 *
	 * @return bool
	 */
	public function hasProducts()
	{
		return count($this->getProducts()) > 0;
	}

	/**
	 * Returns the total number of products in cart based on the products added
	 * and their quantities
	 *
	 * @return int
	 */
	private function getCartPreviewProductsCount()
	{
		$products = $this->getProducts();
		$count = 0;
		foreach($products as $product)
		{
			$count += $product->getQuantity();
		}
		return $count;
	}

	/**
	 * Returns the number of products that are not displayed
	 *
	 * @return mixed
	 */
	public function getNumberOfProductsNotDisplayed()
	{
		return $this->getCartProductsCount() - $this->getCartPreviewProductsCount();
	}

	/**
	 * Checks to see if all products in cart are displayed
	 * or there still are hidden products
	 *
	 * @return bool
	 */
	public function hasMoreProducts()
	{
		return $this->getNumberOfProductsNotDisplayed() > 0;
	}

	/**
	 * Shipping cost
	 */
	public function getShippingCost()
	{
		return $this->cart->getShippingCost();
	}

	/**
	 * Total before applying discount
	 *
	 * @return float
	 */
	public function getTotalBeforeDiscount()
	{
		return $this->cart->getTotalBeforeDiscount();
	}

	/**
	 * Discount name
	 */
	public function getDiscountName()
	{
		return $this->cart->getDiscountName();
	}

	/**
	 * Discount description
	 */
	public function getDiscountDescription()
	{
		return $this->cart->getDiscountDescription();
	}

	/**
	 * Discount amount
	 */
	public function getDiscountAmount()
	{
		return $this->cart->getDiscountAmount();
	}

	/**
	 * Returns global total
	 */
	public function getTotal()
	{
		return $this->cart->getTotal();
	}

	/**
	 * Returns amount of total that represents taxes
	 */
	public function getVAT()
	{
		return $this->cart->getVAT();
	}

	/**
	 * Returns the current shipping method selected
	 *
	 * @return null
	 */
	public function getShippingMethodName(){
        return $this->cart->getShippingMethodName();
    }
}