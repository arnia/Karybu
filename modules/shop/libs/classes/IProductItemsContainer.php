<?php
/**
 * File containing the IProductItemsContainer
 */
/**
 * Defines a common structure for products container
 * like an order or a cart;
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
interface IProductItemsContainer
{
    /**
     * Returns a list of all products
     * Products must implement IProductItem
     *
     * @return mixed
     */
    public function getProducts();

    /**
     * Shipping cost
     */
    public function getShippingCost();

    /**
     * Shipping method name
     */
    public function getShippingMethodName();

    /**
     * Total before applying discount
     *
     * @return float
     */
    public function getTotalBeforeDiscount();

    /**
     * Discount name
     */
    public function getDiscountName();

    /**
     * Discount description
     */
    public function getDiscountDescription();

    /**
     * Discount amount
     */
    public function getDiscountAmount();

    /**
     * Returns global total
     */
    public function getTotal();

    /**
     * Returns amount of total that represents taxes
     */
    public function getVAT();


}