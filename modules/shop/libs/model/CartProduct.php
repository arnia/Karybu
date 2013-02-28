<?php
/**
 * File containing the CartProduct class
 */
/**
 * Models a product belonging to a cart;
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class CartProduct extends BaseItem implements IProductItem
{
	/** @var Reference to the original product */
    private $product;
	/** @var int The cart quantity for this product  */
    public $quantity = 1;
	/** @var Database id of the cart product */
    public $cart_product_srl;
	/** @var Name of the cart product */
    public $cart_product_title;
	/** @var Price of the cart product */
    public $cart_product_price;

	/**
	 * Constructor
	 *
	 * @param null $data
	 */
	public function __construct($data)
    {
        $this->setProduct(new SimpleProduct($data));
        $this->cart_product_srl = $data->cart_product_srl;
        $this->cart_product_title = $data->cart_product_title;
        $this->cart_product_price = $data->cart_product_price;
        $this->quantity = $data->quantity ? $data->quantity : 1;

        parent::__construct();
    }

	/**
	 * Returns the repository for this model class
	 *
	 * @return null|string
	 */
	public function getRepo()
    {
        return "CartRepository";
    }

	/**
	 * Sets the product that was added to cart
	 *
	 * @param SimpleProduct $product
	 * @return CartProduct
	 */
	public function setProduct(SimpleProduct $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
	 * Returns the product added to cart
	 *
     * @return SimpleProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

	/**
	 * Getter
	 *
	 * @param $property
	 * @return mixed|null
	 */
	public function __get($property)
    {
        /**
         * If property is not defined, call getter, if any
         */
        if(method_exists($this, 'get' . ucfirst($property)))
        {
            return call_user_func(array($this, 'get' . ucfirst($property)));
        }
        if(method_exists($this, 'is' . ucfirst($property)))
        {
            return call_user_func(array($this, 'is' . ucfirst($property)));
        }
        if(isset($this->product->$property))
        {
            return $this->product->$property;
        }
        return NULL;
    }

    /**
     * Product title
     */
    public function getTitle()
    {
        return $this->product ? $this->product->title : $this->cart_product_title;
    }

    /**
     * Ordered quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Price
     */
    public function getPrice($discounted = TRUE)
    {
        return $this->product ? $this->product->getPrice($discounted) : $this->cart_product_price;
    }

	/**
	 * Path to the product image thumbnail
	 *
	 * @param int    $width
	 * @param int    $height
	 * @param string $thumbnail_type
	 * @return mixed|string
	 */
	function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        if($this->product) {
            if($this->product->parent_product_srl){
                $productRepo = new ProductRepository();
                $parent_product = $productRepo->getProduct($this->product->parent_product_srl);
                return $parent_product->getPrimaryImage()->getThumbnailPath($width, $height, $thumbnail_type);
            }else{
                return $this->product->getPrimaryImage()->getThumbnailPath($width, $height, $thumbnail_type);
            }
        }
        else{
            return '';
        }
    }

	/**
	 * Check if product is available
	 *
	 * TODO When accesing availability like this: $cart_product->available
	 * the default value for $checkIfInStock=true is used; This in not always correct! To investigate
	 *
	 * @internal param bool $checkIfInStock
	 * @return bool
	 */
    public function isAvailable()
    {
		$shopInfo = new ShopInfo($this->product->module_srl);
		$checkIfInStock = ($shopInfo->getOutOfStockProducts() == 'Y');

        if ($this->product->isPersisted()) {
            return $this->product->isAvailable($checkIfInStock);
        }
        return FALSE;
    }
}