<?php
/**
 * Base model class for Invoice
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class Invoice extends BaseItem
{

    public
        $invoice_srl,
        $order_srl,
        $module_srl,
        $order,
        $comments,
        $regdate;

    /** @var InvoiceRepository */
    public $repo;

    /**
     * save function
     * @return object
     */
    public function save()
    {
        return $this->invoice_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

}