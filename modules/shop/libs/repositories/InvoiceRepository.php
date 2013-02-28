<?php

/**
 * Handles database operations for Invoices table
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class InvoiceRepository extends BaseRepository
{
    /**
     * insert function
     * @param Invoice $invoice
     * @return object
     * @throws ShopException
     */
    public function insert(Invoice &$invoice)
    {
        if ($invoice->invoice_srl) throw new ShopException('A srl must NOT be specified for the insert operation!');
        $invoice->invoice_srl = getNextSequence();
        return $this->query('insertInvoice', get_object_vars($invoice));
    }

    /**
     * update function
     * @param Invoice $invoice
     * @return object
     * @throws ShopException
     */
    public function update(Invoice $invoice)
    {
        if (!is_numeric($invoice->order_srl)) throw new ShopException('You must specify a srl for the updated invoice');
        return $this->query('updateInvoice', get_object_vars($invoice));
    }

    /**
     * get list of invoices
     * @param string $module_srl
     * @param array $extraParams
     * @return object
     */
    public function getList($module_srl, array $extraParams=array())
    {
        $params = array('module_srl'=> $module_srl, 'order_type' => 'desc');
        $params = array_merge($params, $extraParams);
        return $this->query('getInvoiceList', $params, 'Invoice');
    }

    /**
     * get invoice by order srl
     * @param $order_srl
     * @return Invoice|null
     */
    public function getInvoiceByOrderSrl($order_srl)
    {
        $output = $this->query('getInvoiceByOrderSrl',array('order_srl'=> $order_srl));
        return empty($output->data) ? null : $invoice = new Invoice((array) $output->data);
    }

}