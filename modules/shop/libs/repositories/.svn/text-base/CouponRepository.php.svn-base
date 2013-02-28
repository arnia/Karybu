<?php
/**
 * User: florin
 * Date: 12/5/12
 * Time: 2:45 PM
 */
class CouponRepository extends BaseRepository
{
    /**
     * @param $code
     * @param $module_srl
     * @return array|null
     * @throws ShopException
     */
    public function getByCode($code, $module_srl)
    {
        if (!is_numeric($module_srl)) {
            $info = Context::get('site_module_info');
            $module_srl = $info->index_module_srl;
        }
        if (!$module_srl) throw new ShopException('Missing module_srl');
        $output = $this->query('getCouponByCode', array('code' => $code, 'module_srl' => $module_srl), true);
        if (empty($output->data)) return null;
        $arr = array();
        foreach ($output->data as $i=>$data) $arr[] = new Coupon($data);
        return $arr;
    }
}