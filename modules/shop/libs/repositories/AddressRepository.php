<?php

/**
 * Handles database operations for the Address table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class AddressRepository extends BaseRepository
{
    const
            TYPE_BILLING = 1,
            TYPE_SHIPPING = 2;

    protected $addresses, $bulkAddresses;

    /**
     * insert Address
     * @param Address $address
     * @return object
     * @throws ShopException
     */
    public function insert(Address &$address)
    {
        if ($address->address_srl) throw new ShopException('A srl must NOT be specified for the insert operation!');
        $address->address_srl = getNextSequence();
        return $this->query('insertAddress', get_object_vars($address));
    }

    /**
     * update Address
     * @param Address $address
     * @return object
     * @throws ShopException
     */
    public function update(Address &$address)
    {
        if (!$address->address_srl) throw new ShopException('A srl must be specified for the update operation!');
        return $this->query('updateAddress', get_object_vars($address));
    }

    /**
     * Make all existing address not to be default for billing
     *
     * @author Dan Dragan   (dev@xpressengine.org)
     * @param $member_srl
     * @return mixed
     */
    public function unsetDefaultBillingAddress($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $args->default_billing = 'N';
        return $this->query('updateDefaultBillingAddress',$args);
    }

    /**
     * Make all existing address not to be default  for shipping
     *
     * @author Dan Dragan  (dev@xpressengine.org)
     * @param $member_srl
     * @return mixed
     */
    public function unsetDefaultShippingAddress($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $args->default_shipping = 'N';
        return $this->query('updateDefaultShippingAddress',$args);
    }

    /**
     * get address by address_srl
     *
     * @author Dan Dragan   (dev@xpressengine.org)
     * @param $address_srl
     * @return Address
     */
    public function getAddress($address_srl){
        $args = new stdClass();
        $args->address_srl = $address_srl;
        $output = $this->query('getAddress',$args);
        return new Address($output->data);
    }

    /**
     * return all addresses separated into default and additional addresses
     * @param $member_srl
     * @param bool $returnBulk
     * @param bool $refresh
     * @return array|null|stdClass
     */
    public function getAddresses($member_srl, $returnBulk=false, $refresh=false)
    {
        if ((!$this->addresses || (!$this->bulkAddresses && $returnBulk)) || $refresh)
        {
            $output = $this->query('getAddresses', array('member_srl'=>$member_srl), true);
            $bulk = array();
            if(count($output->data)){
                foreach($output->data as $data){
                    $address = new Address($data);
                    if ($returnBulk) {
                        $bulk[] = $address;
                        continue;
                    }
                    if($address->default_billing == 'Y') $default_billing = $address;
                    if($address->default_shipping == 'Y') $default_shipping = $address;
                    if($address->default_billing == 'N' && $address->default_shipping == 'N') $additional_addresses[] = $address;
                }
            }
            if ($returnBulk) {
                return $this->bulkAddresses = (empty($bulk) ? null : $bulk);
            }
            $addresses = new stdClass();
            $addresses->default_billing = $default_billing;
            $addresses->default_shipping = $default_shipping;
            $addresses->additional_addresses = $additional_addresses;
            $addresses->count = count($output->data);
            return $this->addresses = $addresses;
        }
        return $returnBulk ? $this->bulkAddresses : $this->addresses;
    }

    /**
     * Get address list method with pagination
     * @param $member_srl
     * @return object
     * @throws ShopException
     */
    public function getAddressesList($member_srl)
    {
        if (!is_numeric($member_srl)) throw new ShopException('member_srl must be a valid int');
        $args = new stdClass();
        $args->page = Context::get('page');
        if (!$args->page) $args->page = 1;
        Context::set('page', $args->page);

        $args->member_srl = $member_srl;
        if (!isset($args->member_srl)) throw new ShopException("Missing arguments for attributes list : please provide member_srl");

        $output = executeQueryArray('shop.getAddressesList', $args);
        $addresses = array();
        foreach ($output->data as $properties) {
            $address = new Address($properties);
            $addresses[] = $address;
        }
        $output->addresses = $addresses;
        return $output;
    }

    /**
     * delete address by address_srl
     *
     * @author Dan Dragan   (dev@xpressengine.org)
     * @param $address_srl
     * @return mixed
     */
    public function deleteAddress($address_srl){
        return $this->query('deleteAddress',array('address_srl' => $address_srl));
    }

    /**
     * verify if it has a default address
     * @param $member_srl
     * @param int $type
     * @return bool
     * @throws ShopException
     */
    public function hasDefaultAddress($member_srl, $type=self::TYPE_BILLING)
    {
        if (!in_array($type, array(self::TYPE_BILLING, self::TYPE_SHIPPING))) throw new ShopException('Type should be "billing" or "shipping"');
        $addresses = $this->getAddresses($member_srl, true);
        /** @var $a Address */
        foreach ($addresses as $a) {
            if (
                ($a->default_billing == 'Y' && $type == self::TYPE_BILLING) ||
                ($a->default_shipping == 'Y' && $type == self::TYPE_SHIPPING)
            ) return true;
        }
        return false;
    }
}