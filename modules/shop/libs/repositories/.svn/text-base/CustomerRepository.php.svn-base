<?php

/**
 * Handles database operations for Customer
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class CustomerRepository extends BaseRepository
{
    /**
     * get customer list
     * @param $site_srl
     * @param array $extraParams
     * @return array
     * @throws ShopException
     */
    public function getCustomersList($site_srl, array $extraParams=array()){
      if (!$site_srl) {
          throw new ShopException("Missing arguments for get customers list : please provide [site_srl]");
      }
      $addressRepository = new AddressRepository();
      $page = (Context::get('page') ? Context::get('page') : 1);
      $output = $this->getSiteMemberList($site_srl, $page, $extraParams);
      foreach ($output->data as $member) {
          $customer = new Customer($member);
          $customer->addresses = $addressRepository->getAddresses($customer->member_srl);
          $customer->telephone = $customer->addresses->default_billing->telephone;
          $customer->postal_code = $customer->addresses->default_billing->postal_code;
          $customer->country = $customer->addresses->default_billing->country;
          $customer->region = $customer->addresses->default_billing->region;
          $extra_vars = unserialize($member->extra_vars);
          $customer->newsletter = $extra_vars->newsletter;
          $customers[] = $customer;
      }
      $output->customers = $customers;
      return $output;
    }

    /**
     * Get a memebr list for site
     * @param $site_srl
     * @param $page
     * @param array $extraParams
     * @return object
     */
    function getSiteMemberList($site_srl, $page, array $extraParams=array())
    {
        $params = array(
            'site_srl' => $site_srl,
            'page' => $page,
            'list_count' => 40,
            'page_count' => 10
        );
        $params = array_merge($params, $extraParams);
        return $this->query('getSiteMemberList', $params, true);
    }

    /**
     * get customers subscribed to newsletter
     * @param $site_srl
     * @return object
     */
    public function getNewsletterCustomers($site_srl){
        $args = new stdClass();
        $args->site_srl = $site_srl;
        $output = $this->query('getAllSiteMemberList',$args,true);
        foreach($output->data as $member){
            $customer = new Customer($member);
            $extra_vars = unserialize($member->extra_vars);
            $customer->newsletter = $extra_vars->newsletter;
            if($customer->newsletter == 'Y') $customers[] = $customer;
        }
        $output->customers = $customers;
        return $output;
    }

    /**
     * get member extra vars
     * @param $member_srl
     * @return mixed
     */
    public function getMemberExtraVars($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $output = $this->query('getMemberExtraVars',$args);
        return $output->data->extra_vars;
    }

    /**
     * update member extra vars
     * @param $member_srl
     * @param $extra_vars
     * @return object
     */
    public function updateMemberExtraVars($member_srl,$extra_vars){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $args->extra_vars = $extra_vars;
        $output = $this->query('updateMemberExtraVars',$args);
        return $output;
    }
}