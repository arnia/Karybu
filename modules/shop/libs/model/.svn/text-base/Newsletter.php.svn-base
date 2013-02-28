<?php
/**
 * Base model class for Customer
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class Newsletter extends BaseItem
{

    public
        $newsletter_srl,
        $module_srl,
        $subject,
        $sender_name,
        $sender_email,
        $content,
        $regdate;

    /** @var InvoiceRepository */
    public $repo;

    /**
     * save function
     * @return object
     */
    public function save()
    {
        return $this->newsletter_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

}