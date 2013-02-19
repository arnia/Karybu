<?php

/**
 * Will be replaced with MobileInstance
 *
 * @deprecated
 */
class Mobile
{
    /** @var MobileInstance */
    private static $mobile;

    public static function setRequestMobileInfo(MobileInstance $mobile)
    {
        self::$mobile = $mobile;
    }

    public static function &getInstance()
    {
        return self::$mobile;
    }

    public static function isFromMobilePhone()
    {
        return self::$mobile->isFromMobilePhone();
    }

    public static function isMobileCheckByAgent()
    {
        return self::$mobile->isMobileCheckByAgent();
    }

    public static function isMobilePadCheckByAgent()
    {
        return self::$mobile->isMobilePadCheckByAgent();
    }

    public static function setMobile($mobile)
    {
        self::$mobile->setMobile($mobile);
    }

}