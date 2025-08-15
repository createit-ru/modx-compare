<?php

namespace Compare\Model;

use msProduct;

class ComparedProduct
{
    private msProduct $product;

    private bool $loaded = false;

    function __construct(msProduct $product)
    {
        $this->product = $product;
    }

    public function load()
    {
        // for the future
        // ...
        $this->loaded = true;
    }


    /**
     * @param string $key
     * @return mixed
     */
    public function getFieldValue(string $key)
    {
        if (!$this->loaded) {
            $this->load();
        }

        $v = $this->product->get($key);
        if (is_array($v) && count($v) == 1) {
            return reset($v);
        }
        return $v;
    }

    public function toArray(): array
    {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->product->toArray();
    }
}
