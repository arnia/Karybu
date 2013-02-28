<?php

/**
 * Handles database operations for Orders table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class OrderRepository extends BaseRepository
{

    public function getList($module_srl, $member_srl=null, array $extraParams = array(), $page = null)
    {
        $params = array('module_srl'=> $module_srl);
        if ($member_srl) $params['member_srl'] = $member_srl;
        if ($page) $params['page'] = $page;
        $params = array_merge($extraParams, $params);
        $output = $this->query('getOrdersList', $params, 'Order');
        return $output;
    }

    public function getRecentOrders($module_srl, $member_srl=null)
    {
        $params = array('module_srl'=> $module_srl);
        if ($member_srl) $params = array_merge($params, array('member_srl'=> $member_srl));
        $output = $this->query('getRecentOrders', $params,true);
        $orders = array();
        foreach ($output->data as $data) $orders[] = new Order((array) $data);
        return $orders;
    }

    public function getMostOrderedProducts($module_srl)
    {
        $params = array('module_srl'=> $module_srl);
		try
		{
			$output = $this->query('getMostOrderedProducts', $params, true);
		}catch (DbQueryException $e)
		{
			$output = new stdClass();
			$output->data = array();
		}


        foreach ($output->data as $data) {
            if($data->product_type == 'simple') $product = new SimpleProduct((array) $data);
            elseif($data->product_type == 'configurable') $product = new ConfigurableProduct((array) $data);
            $product->order_count = $data->order_count;
            $products[] = $product;
        }
        return $products;
    }

    public function getTopCustomers($module_srl)
    {
        $params = array('module_srl'=> $module_srl);
        $output = $this->query('getTopCustomers', $params, true);

        return $output->data;
    }

    public function getLastOrder($module_srl, $member_srl)
    {
        $params = array(
            'module_srl' => $module_srl ,
            'member_srl' => $member_srl ,
            'list_count' => 1
        );
        $output = $this->query('getRecentOrders', $params);
        $order = new Order((array) $output->data);
        return $order;
    }

    public function insert(Order &$order)
    {
        if ($order->order_srl) throw new ShopException('A srl must NOT be specified for the insert operation!');
        $order->order_srl = getNextSequence();
        return $this->query('insertOrder', get_object_vars($order));
    }

    public function update(Order $order)
    {
        if (!is_numeric($order->order_srl)) throw new ShopException('You must specify a srl for the updated order');
        return $this->query('updateOrder', get_object_vars($order));
    }

    public function deleteOrders(array $order_srls)
    {
        return $this->query('deleteOrders', array('order_srls' => $order_srls));
    }

    public function insertOrderProduct($order_srl, CartProduct $product)
    {
        $params = array(
            'order_srl' => $order_srl,
            'product_srl' => $product->product_srl,
            'quantity' => $product->quantity,
            'member_srl' => $product->member_srl,
            'parent_product_srl' => $product->parent_product_srl,
            'product_type' => $product->product_srl,
            'title' => $product->title,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'sku' => $product->sku,
            'weight' => $product->weight,
            'status' => $product->status,
            'friendly_url' => $product->friendly_url,
            'price' => $product->price,
            'discount_price' => $product->discount_price,
            'qty' => $product->qty,
            'in_stock' => $product->in_stock,
            'primary_image_filename' => $product->primary_image_filename,
            'regdate' => $product->regdate,
            'last_update' => $product->last_update
        );
        return $this->query('insertOrderProduct', $params);
    }

    public function deleteOrderProducts($order_srl, array $product_srls=null)
    {
        return $this->query('deleteOrderProducts', array('order_srl' => $order_srl, 'product_srls' => $product_srls));
    }

    public function getOrderBySrl($srl)
    {
        $output = $this->query('getOrderBySrl', array('order_srl'=> $srl));
        if(empty($output->data)){
            return null;
        }else{
            $order = new Order((array) $output->data);
            $this->getOrderShipment($order);
            $this->getOrderInvoice($order);
            return $order;
        }
    }


    public function getOrderByTransactionId($transaction_id)
    {
        $output = $this->query('getOrderByTransactionId', array('transaction_id'=> $transaction_id));
        if(empty($output->data)){
            return null;
        }else{
            $order = new Order((array) $output->data);
            $this->getOrderShipment($order);
            $this->getOrderInvoice($order);
            return $order;
        }
    }

    public function getOrderShipment($order){
        $shopModel = getModel('shop');
        $shipmentRepository = $shopModel->getShipmentRepository();
        $order->shipment = $shipmentRepository->getShipmentByOrderSrl($order->order_srl);
    }

    public function getOrderInvoice($order){
        $shopModel = getModel('shop');
        $invoiceRepository = $shopModel->getInvoiceRepository();
        $order->invoice = $invoiceRepository->getInvoiceByOrderSrl($order->order_srl);
    }

    public function getOrderItems($order)
    {   $shopModel = getModel('shop');
        $productRepository = $shopModel->getProductRepository();
        $args = new stdClass();
        $args->order_srl = $order->order_srl;
        $output = $this->query('getOrderItems',$args,true);
        foreach($output->data as $item){
            $product = new OrderProduct($item);
            $ordered_items[] = $product;
        }
        return $ordered_items;
    }

    public function getOrderProductItems($order)
    {   $shopModel = getModel('shop');
        $productRepository = $shopModel->getProductRepository();
        $args = new stdClass();
        $args->order_srl = $order->order_srl;
        $output = $this->query('getOrderItems',$args,true);
        foreach($output->data as $item){
            $product = new SimpleProduct($item);
            $ordered_items[] = $product;
        }
        return $ordered_items;
    }


}