<?php
/**
 * User: florin
 * Date: 12/4/12
 * Time: 10:42 AM
 */
class RandomGenerator
{
    const
        TYPE_ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        TYPE_ALPHANUM = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ',
        TYPE_NUM = '0123456789';

    public static $types = array(self::TYPE_ALPHA, self::TYPE_ALPHANUM, self::TYPE_NUM);

    public static function generate($howMany=1, $length=10, $type=self::TYPE_ALPHANUM, $pattern='X', $separateEvery=0, $separator='-')
    {
        if (!in_array($type, self::$types)) throw new ShopException('Wrong type');
        if (ctype_digit((string)$howMany) && $howMany > 1) {
            $arr = array();
            for ($i=0; $i<$howMany; $i++) {
                $arr[] = self::generateOne($length, $type, $pattern, $separateEvery, $separator);
            }
            return $arr;
        }
        else return self::generateOne($length, $type, $pattern, $separateEvery, $separator);
    }

    public static function generateOne($length=10, $type=self::TYPE_ALPHANUM, $pattern='X', $separateEvery=0, $separator='-')
    {
        $rand = self::randomString($length, $type, $separateEvery, $separator);
        return str_replace('X', $rand, $pattern);
    }

    public static function randomString($length=10, $type=self::TYPE_ALPHANUM, $separateEvery=0, $separator='-')
    {
        if (!in_array($type, self::$types)) throw new ShopException('Wrong type');
        $possibilities = $type;
        $len = strlen($possibilities);
        if ($length > $len) $possibilities = str_repeat($possibilities, ceil($length/$len));
        $rand = substr(str_shuffle($possibilities), 0, $length);
        if (ctype_digit((string)$separateEvery) && $separateEvery > 0) { //natural number inputted, need to add dashes
            $rand = implode($separator, str_split($rand, $separateEvery));
        }
        return $rand;
    }

}