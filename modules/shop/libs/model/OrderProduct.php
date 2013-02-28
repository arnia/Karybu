<?php
/**
 * File containing the OrderProduct class
 */
/**
 * Class that models a product that belongs to an order
 *
 * An "order product" represents a snapshot of a product
 * at a given time. Once an order is placed, the data it holds cannot
 * change; that is why this object does not use a reference to a Product
 * but instead copies all of its fields.
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class OrderProduct extends BaseItem implements IProductItem
{
    public $order_srl;
    public $product_srl;
    public $quantity; // Ordered quantity

    public $member_srl;
    public $parent_product_srl;
    public $product_type;
    public $title;
    public $description;
    public $short_description;
    public $sku;
    public $weight;
    public $status;
    public $friendly_url;
    public $price;
    public $discount_price;
    public $qty; // Stock quantity
    public $in_stock;
    public $primary_image_filename;
    public $related_products;
    public $regdate;
    public $last_update;

	/**
	 * Repository for this model class
	 */
    public function getRepo()
    {
        return "OrderRepository";
    }

    /**
     * Number of items ordered
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Product title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Price
     */
    public function getPrice()
    {
        return $this->price;
    }

	/**
	 * Returns path to product image thumbnail
	 *
	 * @param int    $width
	 * @param int    $height
	 * @param string $thumbnail_type
	 * @return mixed|string
	 */
	function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        $thumbnail = new ShopThumbnail($this->order_srl, $this->primary_image_filename);
        return $thumbnail->getThumbnailPath($width, $height, $thumbnail_type);
    }
}