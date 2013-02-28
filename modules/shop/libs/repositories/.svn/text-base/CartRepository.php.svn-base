<?php

/**
 * Handles database operations for the shopping Cart
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class CartRepository extends BaseRepository
{

    //region Cart operations
    public function insertCart(Cart &$cart)
    {
        if ($cart->cart_srl) throw new ShopException('A srl must NOT be specified for the insert operation!');
        $cart->cart_srl = getNextSequence();
        return $this->query('insertCart', get_object_vars($cart));
    }

    public function updateCart(Cart $cart)
    {
        if (!is_numeric($cart->cart_srl)) throw new ShopException('You must specify a srl for the updated cart');
        return $this->query('updateCart', get_object_vars($cart));
    }

    public function deleteCarts(array $cart_srls)
    {
        return $this->query('deleteCarts', array('cart_srls' => $cart_srls));
    }

    public function deleteCartsByModule($module_srl)
    {
        return $this->query('deleteCarts', array('module_srl' => $module_srl));
    }
    //endregion


    //region CartProducts operations
    public function insertCartProduct($cart_srl, $product_srl, $quantity=1, array $extraParams=array())
    {
        $params = array('cart_srl' => $cart_srl, 'product_srl' => $product_srl, 'quantity' => $quantity);
        $extraParams = array_merge($params, $extraParams);
        return $this->query('insertCartProduct', $extraParams);
    }

    public function getCartProducts($cart_srl, array $product_srls=null)
    {
        $params = array('cart_srl' => $cart_srl);
        if ($product_srls) $params['product_srls'] = $product_srls;
        return $this->query('getCartProducts', $params, true);
    }

    public function getCartProduct($cart_srl, $product)
    {
        if ($product instanceof SimpleProduct) {
            if (!$product->isPersisted()) {
                throw new ShopException('Product is not persisted');
            }
            $product_srl = $product->product_srl;
        }
        elseif (is_numeric($product)) $product_srl = $product;
        $out = $this->getCartProducts($cart_srl, array($product_srl));
        return empty($out->data) ? null : $out->data[0];
    }

    public function deleteCartProducts($cart_srl, array $product_srls=null)
    {
        $params = array('cart_srl' => $cart_srl);
        if ($product_srls) $params['product_srls'] = $product_srls;
        return $this->query('deleteCartProducts', $params);
    }

    public function updateCartProduct($cart_srl, $product_srl, $quantity, array $extraParams=array())
    {
        $params = array('cart_srl' => $cart_srl, 'product_srl' => $product_srl, 'quantity' => $quantity);
        $params = array_merge($params, $extraParams);
        return $this->query('updateCartProduct', $params);
    }
    //endregion


    /**
     * This returns a cart object corresponding for the input parameters or creates a new cart
     * @return Cart|null
     */
    public function getCart($module_srl=null, $cart_srl=null, $member_srl=null, $session_id=null, $create=false)
    {
        $params = self::uid($module_srl, $cart_srl, $member_srl, $session_id);
        $output = $this->query('getCart', $params, 'Cart');
        if (empty($output->data)) {
            if ($create) return $this->getNewCart($module_srl, $member_srl, $session_id);
            return null;
        }
        return $output->data[0];
    }

    /**
     * Returns an array necessary for selecting the cart object (Unique Identification of a cart row)
     *
     * @return array sufficient data for cart identification (for the select query)
     * @throws Exception Invalid input
     */
    public static function uid($module_srl = null, $cart_srl = null, $member_srl = null, $session_id = null)
    {
        if (is_numeric($cart_srl)) return array('cart_srl' => $cart_srl);
        if (is_numeric($member_srl)) {
            if (is_numeric($module_srl)) {
                $a = array('member_srl' => $member_srl, 'module_srl' => $module_srl);
                return $a;
            }
            throw new ShopException('Count not identify cart by member_srl (module_srl needed)');
        }
        if ($session_id) {
            if (is_numeric($module_srl)) return array(
                'session_id' => $session_id,
                'module_srl' => $module_srl
            );
            throw new ShopException('Count not identify cart by session_id (module_srl needed)');
        }
        throw new ShopException('Invalid input for cart identification');
    }

    public function getNewCart($module_srl, $member_srl = null, $session_id = null, $items = 0)
    {
        if (!$session_id) $session_id = session_id();
        $cart = new Cart(array(
            'module_srl' => $module_srl,
            'member_srl' => $member_srl,
            'session_id' => $session_id,
            'items'      => $items
        ));
        $cart->save();
        return $cart;
    }

    public function countCartProducts(Cart $cart, $sumQuantities=false)
    {
        $params = self::uid($cart->module_srl, $cart->cart_srl, $cart->member_srl, $cart->session_id);
        $what = ($sumQuantities ? 'total' : 'count');
        $rez = $this->query('getCartCount', $params)->data->$what;
        return $rez ? $rez : 0;
    }

}