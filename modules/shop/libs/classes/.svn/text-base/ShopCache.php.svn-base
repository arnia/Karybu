<?php
// florin, 9/30/12 6:29 PM
class ShopCache implements ArrayAccess
{
    private $data = array();

    public function has($key)
    {
		return array_key_exists($key, $this->data);
    }
    public function get($key)
    {
        return $this->has($key) ? $this->data[$key] : null;
    }
    public function set($key, $value, $overwrite=true)
    {
        if ($this->has($key)) {
            if (!$overwrite) {
                throw new ShopException("Cache key '$key' already exists");
            }
        }
        return $this->data[$key] = $value;
    }
    public function add($value)
    {
        $randomKey = $this->randString(10);
        $this->data[$randomKey] = $value;
        return $randomKey;
    }
    public function remove($key)
    {
        if (!$this->has($key)) return false;
        $val = $this->data[$key];
        unset($this->data[$key]);
        return $val;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            //$last_inserted_key = end(array_keys($item->cache))
            $this->add($value);
        }
        else $this->set($offset, $value);
    }
    public function offsetExists($offset) {
        return $this->has($offset);
    }
    public function offsetUnset($offset) {
        $this->remove($offset);
    }
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    private function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }
        return $str;
    }
}
