<?php
/**
 * Created by JetBrains PhpStorm.
 * User: florin
 * Date: 11/5/12
 * Time: 6:57 PM
 */
class FrontFilters
{
    const
        FROM_PRICE_MIN = 'min',
        FROM_PRICE_MAX = 'max',
        TO_PRICE_MIN = 'minPrice',
        TO_PRICE_MAX = 'maxPrice',
        TO_ATTRIBUTE_NUMERIC_MIN = 'minSRL',
        TO_ATTRIBUTE_NUMERIC_MAX = 'maxSRL',
        TO_ATTRIBUTE_SELECT = 'selSRL',
        TO_ATTRIBUTE_SELECT_MULTIPLE = 'mSelSRL',
        SEPARATOR_MULTIPLE = ',';

    //adds proper variables to $args and sets values for _filters template
    public static function work(stdClass &$args)
    {
        if (!$args->module_srl) throw new ShopException('need module_srl in args');
        self::addFiltersToQueryArgs($args);
        self::setValuesForTemplate($args->module_srl);
    }

    //sets values used to display filter widgets
    public static function setValuesForTemplate($module_srl)
    {
        $productRepo = new ProductRepository();
        $maxPrice = $productRepo->getMaxPrice($module_srl);
        $minPrice = 0;
        $priceFilter = array(
            //start value
            $minPrice,
            //selected left value
            Context::get(self::TO_PRICE_MIN) ? Context::get(self::TO_PRICE_MIN) : $minPrice,
            //selected right value
            Context::get(self::TO_PRICE_MAX) ? Context::get(self::TO_PRICE_MAX) : $maxPrice,
            //end value
            $maxPrice
        );
        Context::set('priceFilter', $priceFilter);

        $attributeRepo = new AttributeRepository();
        $catSrls = array();
        if (is_numeric($catSrl = Context::get('category_srl'))) $catSrls[] = $catSrl;
        elseif ((Context::get('type') == 'category') && ($slug = Context::get('identifier'))) {
            //friendly url
            $cRepo = new CategoryRepository();
            $cat = $cRepo->getCategoryByFriendlyUrl($slug);
            $catSrls[] = $cat->category_srl;
        }
        $attrs = $attributeRepo->getFilterAttributes($module_srl, $catSrls);
        foreach ($attrs as $i=>$attribute) {
            /** @var $attribute Attribute */
            if ($attribute->isNumeric()) {
                $min = $attribute->getMinValue(); $min |= 0;
                //if there is no max value for this attribute unset it
                if (!$max = (int) $attribute->getMaxValue()) { unset($attrs[$i]); continue; }
                $attribute->setMeta('min', $min);
                $attribute->setMeta('max', $max);

                $minKey = str_replace('SRL', $attribute->attribute_srl, self::TO_ATTRIBUTE_NUMERIC_MIN);
                if (isset($_GET[$minKey]) && is_numeric($minValue = $_GET[$minKey])) {
                    $attribute->setMeta('minValue', $minValue);
                }
                $maxKey = str_replace('SRL', $attribute->attribute_srl, self::TO_ATTRIBUTE_NUMERIC_MAX);
                if (isset($_GET[$maxKey]) && is_numeric($maxValue = $_GET[$maxKey])) {
                    $attribute->setMeta('maxValue', $maxValue);
                }
            }
            elseif ($attribute->isSelect()) {
                $key = str_replace('SRL', $attribute->attribute_srl, self::TO_ATTRIBUTE_SELECT);
                if (isset($_GET[$key]) && $val = $_GET[$key]) {
                    $attribute->setMeta('filterValue', $val);
                }
            }
            elseif ($attribute->isMultipleSelect()) {
                $key = str_replace('SRL', $attribute->attribute_srl, self::TO_ATTRIBUTE_SELECT_MULTIPLE);
                if (isset($_GET[$key]) && $val = $_GET[$key]) {
                    $values = explode(self::SEPARATOR_MULTIPLE, $val);
                    $attribute->setMeta('filterValues', $values);
                }
            }
        }
        Context::set('filter_attributes', $attrs);
    }

    public static function addFiltersToQueryArgs(stdClass &$args)
    {
        //min_price
        if (ctype_digit($min = Context::get(self::TO_PRICE_MIN)) || (is_float($min) && $min > 0)) {
            $args->min_price = $min;
        }
        //max_price
        if (ctype_digit($max = Context::get(self::TO_PRICE_MAX)) || (is_float($max) && $max > 0)) {
            $args->max_price = $max;
        }

        $attributeGetPatterns = array(
            self::TO_ATTRIBUTE_NUMERIC_MIN,
            self::TO_ATTRIBUTE_NUMERIC_MAX,
            self::TO_ATTRIBUTE_SELECT,
            self::TO_ATTRIBUTE_SELECT_MULTIPLE
        );
        $i = 1;
        while ((list($key, $value) = each($_GET)) && $i < 6) {
            foreach ($attributeGetPatterns as $pattern) {
                if (preg_match("/^" . str_replace('SRL', '(\d+)', $pattern) . "$/i", $key, $matches)) {
                    $srl = $matches[1];
                    if ($pattern == self::TO_ATTRIBUTE_NUMERIC_MIN) {
                        $srlVar = 'attr_' . $i . '_range_srl';
                        $val = 'attr_' . $i . '_range_a';
                        $value = (int) $value;
                        $increment = false;
                    }
                    elseif ($pattern == self::TO_ATTRIBUTE_NUMERIC_MAX) {
                        $srlVar = 'attr_' . $i . '_range_srl';
                        $val = 'attr_' . $i . '_range_b';
                        $value = (int) $value;
                        $increment = true;
                    }
                    elseif ($pattern == self::TO_ATTRIBUTE_SELECT) {
                        $srlVar = 'attr_' . $i . '_eq_srl';
                        $val = 'attr_' . $i . '_eq_value';
                        $increment = true;
                    }
                    elseif ($pattern == self::TO_ATTRIBUTE_SELECT_MULTIPLE) {
                        $srlVar = 'attr_' . $i . '_in_srl';
                        $val = 'attr_' . $i . '_in_value';
                        $value = explode(self::SEPARATOR_MULTIPLE, $value);
                        $increment = true;
                    }
                    else {
                        throw new ShopException('Wrong attribute type when adding to query');
                    }
                    $args->$srlVar = (int) $srl;
                    $args->$val = $value;
                    if ($increment) $i++;
                }
            }
        }
        return;
    }

    public static function redirectUrl($originalUrl, array $filters)
    {
        $params = array();
        if (isset($filters['price'])) {
            $price = $filters['price'];
            $minPriceKey = self::FROM_PRICE_MIN;
            if (isset($price[$minPriceKey]) && is_numeric($price[$minPriceKey])) {
                $params[self::TO_PRICE_MIN] = ($price[$minPriceKey] > 0 ? $price[$minPriceKey] : null);
            }
            $maxPriceKey = self::FROM_PRICE_MAX;
            if (isset($price[$maxPriceKey]) && is_numeric($price[$maxPriceKey]) && $price[$maxPriceKey] > 0) {
                //TODO: set to null if max price
                $params[self::TO_PRICE_MAX] = $price[$maxPriceKey];
            }
        }
        if (isset($filters['attributes']) && is_array($attributes = $filters['attributes'])) {
            $aRepo = new AttributeRepository();
            $out = $aRepo->get(array_keys($attributes), 'getAttributesBySrls');
            $objects = array(); foreach ($out as $o) $objects[$o->attribute_srl] = $o; unset($out);
            foreach ($attributes as $srl=>$filterValue) {
                if (array_key_exists($srl, $objects)) {
                    /** @var $attribute Attribute */
                    $attribute = $objects[$srl];
                    if ($filterValue) {
                        if ($attribute->isNumeric()) {
                            if (is_array($filterValue)) {
                                if (isset($filterValue['min']) && ctype_digit($filterValue['min']) && $filterValue['min']) {
                                    $key = str_replace('SRL', $srl, self::TO_ATTRIBUTE_NUMERIC_MIN);
                                    if ($filterValue['min'] != $attribute->getMinValue()) {
                                        $params[$key] = $filterValue['min'];
                                    }
                                }
                                if (isset($filterValue['max']) && ctype_digit($filterValue['max']) && $filterValue['max']) {
                                    $key = str_replace('SRL', $srl, self::TO_ATTRIBUTE_NUMERIC_MAX);
                                    if ($filterValue['max'] != $attribute->getMaxValue()) {
                                        $params[$key] = $filterValue['max'];
                                    }
                                }
                            }
                        }
                        elseif ($attribute->isSelect()) {
                            $key = str_replace('SRL', $srl, self::TO_ATTRIBUTE_SELECT);
                            $params[$key] = $filterValue;
                        }
                        elseif ($attribute->isMultipleSelect()) {
                            $key = str_replace('SRL', $srl, self::TO_ATTRIBUTE_SELECT_MULTIPLE);
                            $params[$key] = implode(self::SEPARATOR_MULTIPLE, $filterValue);
                        }
                    }
                }
            }
        }
        //force it go to dispShop
        $params = array_merge(array('act'=>'dispShop'), $params);

        //unset empty filters meant to be removed
        $originalQuery = parse_url($originalUrl, PHP_URL_QUERY);
        parse_str($originalQuery, $originalQueryParts);
        $newQueryParts = array_merge($originalQueryParts, $params);
        $patterns = array(self::TO_ATTRIBUTE_NUMERIC_MIN, self::TO_ATTRIBUTE_NUMERIC_MAX, self::TO_ATTRIBUTE_SELECT, self::TO_ATTRIBUTE_SELECT_MULTIPLE);
        foreach ($newQueryParts as $k=>$p) {
            if (!isset($params[$k])) {
                foreach ($patterns as $pattern) {
                    if (preg_match("/" . str_replace('SRL', '(\d+)', $pattern) . "/i", $k)) {
                        unset($newQueryParts[$k]);
                    }
                }
            }
        }
        $newQuery = http_build_query($newQueryParts);
        $goto = $originalQuery ? str_replace("?$originalQuery", "?$newQuery", $originalUrl) :
            FrontFilters::http_build_url(
                $originalUrl,
                array('query' => http_build_query($params)),
                HTTP_URL_JOIN_QUERY
            );
        return $goto;
    }

    /**
     * HTTP Build URL, ported from PECL
     * Combines arrays in the form of parse_url() into a new string based on specific options
     * @name http_build_url
     * @param string|array $url		The existing URL as a string or result from parse_url
     * @param array $parts			Same as $url
     * @param int $flags			URLs are combined based on these
     * @param array &$new_url		If set, filled with array version of new url
     * @return string
     * @url http://php.net/manual/en/function.http-build-url.php
     */
    public static function http_build_url(/*string|array*/ $url, /*string|array*/ $parts = array(), /*int*/ $flags = HTTP_URL_REPLACE, /*array*/ &$new_url = false)
    {
        // If the $url is a string
        if(is_string($url))
        {
            $url = parse_url($url);
        }

        // If the $parts is a string
        if(is_string($parts))
        {
            $parts	= parse_url($parts);
        }

        // Scheme and Host are always replaced
        if(isset($parts['scheme']))	$url['scheme']	= $parts['scheme'];
        if(isset($parts['host']))	$url['host']	= $parts['host'];

        // (If applicable) Replace the original URL with it's new parts
        if(HTTP_URL_REPLACE & $flags)
        {
            // Go through each possible key
            foreach(array('user','pass','port','path','query','fragment') as $key)
            {
                // If it's set in $parts, replace it in $url
                if(isset($parts[$key]))	$url[$key]	= $parts[$key];
            }
        }
        else
        {
            // Join the original URL path with the new path
            if(isset($parts['path']) && (HTTP_URL_JOIN_PATH & $flags))
            {
                if(isset($url['path']) && $url['path'] != '')
                {
                    // If the URL doesn't start with a slash, we need to merge
                    if($url['path'][0] != '/')
                    {
                        // If the path ends with a slash, store as is
                        if('/' == $parts['path'][strlen($parts['path'])-1])
                        {
                            $sBasePath	= $parts['path'];
                        }
                        // Else trim off the file
                        else
                        {
                            // Get just the base directory
                            $sBasePath	= dirname($parts['path']);
                        }

                        // If it's empty
                        if('' == $sBasePath)	$sBasePath	= '/';

                        // Add the two together
                        $url['path']	= $sBasePath . $url['path'];

                        // Free memory
                        unset($sBasePath);
                    }

                    if(false !== strpos($url['path'], './'))
                    {
                        // Remove any '../' and their directories
                        while(preg_match('/\w+\/\.\.\//', $url['path'])){
                            $url['path']	= preg_replace('/\w+\/\.\.\//', '', $url['path']);
                        }

                        // Remove any './'
                        $url['path']	= str_replace('./', '', $url['path']);
                    }
                }
                else
                {
                    $url['path']	= $parts['path'];
                }
            }

            // Join the original query string with the new query string
            if(isset($parts['query']) && (HTTP_URL_JOIN_QUERY & $flags))
            {
                //TODO !watchout for these changes!
                if (isset($url['query'])) {
                    parse_str($parts['query'], $p1);
                    parse_str($url['query'], $p2);
                    foreach ($p2 as $key=>$val) {
                        if (in_array($key, array(/* add all filters here */self::TO_PRICE_MAX, self::TO_PRICE_MIN))) {
                            if (!isset($p1[$key])) unset($p2[$key]);
                        }
                    }
                    $newParams = array_merge($p2, $p1);
                    $parts['query'] = http_build_query($newParams);
                    $url['query']	= $parts['query'];
                }
                else $url['query'] = $parts['query'];
            }
        }

        // Strips all the applicable sections of the URL
        if(HTTP_URL_STRIP_USER & $flags)		unset($url['user']);
        if(HTTP_URL_STRIP_PASS & $flags)		unset($url['pass']);
        if(HTTP_URL_STRIP_PORT & $flags)		unset($url['port']);
        if(HTTP_URL_STRIP_PATH & $flags)		unset($url['path']);
        if(HTTP_URL_STRIP_QUERY & $flags)		unset($url['query']);
        if(HTTP_URL_STRIP_FRAGMENT & $flags)	unset($url['fragment']);

        // Store the new associative array in $new_url
        $new_url	= $url;

        // Combine the new elements into a string and return it
        return
            ((isset($url['scheme'])) ? $url['scheme'] . '://' : '')
            .((isset($url['user'])) ? $url['user'] . ((isset($url['pass'])) ? ':' . $url['pass'] : '') .'@' : '')
            .((isset($url['host'])) ? $url['host'] : '')
            .((isset($url['port'])) ? ':' . $url['port'] : '')
            .((isset($url['path'])) ? $url['path'] : '')
            .((isset($url['query'])) ? '?' . $url['query'] : '')
            .((isset($url['fragment'])) ? '#' . $url['fragment'] : '')
            ;
    }

}


if (!function_exists('http_build_url'))
{
    define('HTTP_URL_REPLACE',			0x0001);	// Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH',		0x0002);	// Join relative paths
    define('HTTP_URL_JOIN_QUERY', 		0x0004);	// Join query strings
    define('HTTP_URL_STRIP_USER', 		0x0008);	// Strip any user authentication information
    define('HTTP_URL_STRIP_PASS',		0x0010);	// Strip any password authentication information
    define('HTTP_URL_STRIP_PORT',		0x0020);	// Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH',		0x0040);	// Strip complete path
    define('HTTP_URL_STRIP_QUERY',		0x0080);	// Strip query string
    define('HTTP_URL_STRIP_FRAGMENT',	0x0100);	// Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_AUTH',		HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS);
    define('HTTP_URL_STRIP_ALL', 		HTTP_URL_STRIP_AUTH | HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);
}