<?php
/**
 * Base model class for Shipment
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class Shipment extends BaseItem
{

    public
        $shipment_srl,
        $order_srl,
        $module_srl,
        $package_number,
        $comments,
        $regdate;
    /** @var Order */
    public $order;
    /** @var ShipmentRepository */
    public $repo;

    /**
     * save function
     * @return object
     */
    public function save()
    {
        return $this->shipment_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

    /**
     * check and update stocks
     * @return array
     */
    public function checkAndUpdateStocks()
    {
        $products = $this->order->getProducts();
        $productRepo = new ProductRepository();
        /** @var $orderProduct OrderProduct */
        $productsEmptyStocks = array();
        foreach($products as $orderProduct){
            /** @var $product SimpleProduct */
            $product = $productRepo->getProduct($orderProduct->product_srl, false);
            if($orderProduct->quantity == $product->qty){
                $productsEmptyStocks[] = $product;
            }
            $product->substractFromStock($orderProduct->quantity);
        }
        return $productsEmptyStocks;
    }


}