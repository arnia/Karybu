<?php
/**
 * Base model class for Customer
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class Customer extends BaseItem
{

    public
        $member_srl,
        $user_id,
        $password,
        $email_address,
        $email_id,
        $email_host,
        $user_name,
        $nick_name,
        $find_account_question,
        $find_account_answer,
        $homepage,
        $blog,
        $birthdate,
        $denied,
        $last_login,
        $vid,
        $module_srl,
        $profile_image,
        $regdate,
        $last_update,
        $telephone,
        $postal_code,
        $country,
        $region,
        $newsletter,
        $addresses,
        $groups = array();

    /** @var CustomerRepository */
    public $repo;

}