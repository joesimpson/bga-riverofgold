<?php
namespace ROG\Helpers;

class Collection extends \ArrayObject
{
    public function getIds() : array
    {
        return array_keys($this->getArrayCopy());
    }

    /**
     * @return bool true is collection is empty
     */
    public function isEmpty()
    {
        return count($this->toArray()) == 0;
    }
    public function empty()
    {
        return empty($this->getArrayCopy());
    }

    public function first()
    {
        $arr = $this->toArray();
        return isset($arr[0]) ? $arr[0] : null;
    }

    public function rand()
    {
        $arr = $this->getArrayCopy();
        $key = array_rand($arr, 1);
        return $arr[$key];
    }

    public function toArray() : array
    {
        return array_values($this->getArrayCopy());
    }

    public function toAssoc() : array
    {
        return $this->getArrayCopy();
    }

    public function map($func) : Collection
    {
        return new Collection(array_map($func, $this->toAssoc()));
    }

    public function merge($arr) : Collection
    {
        return new Collection($this->toAssoc() + $arr->toAssoc());
    }

    public function reduce($func, $init)
    {
        return array_reduce($this->toArray(), $func, $init);
    }

    public function filter($func) : Collection
    {
        return new Collection(array_filter($this->toAssoc(), $func));
    }

    public function limit($n) : Collection
    {
        return new Collection(array_slice($this->toAssoc(), 0, $n, true));
    }

    public function includes($t) : bool
    {
        return in_array($t, $this->getArrayCopy());
    }

    public function ui() : array
    {
        return $this->map(function ($elem) {
            return $elem->getUiData();
        })->toArray();
    }

    public function uiAssoc() : array
    {
        return $this->map(function ($elem) {
            return $elem->getUiData();
        })->toAssoc();
    }
    
    /**
     * @return array alleged datas to [ id => type]
     */
    public function uiAssocLight() : array
    {
        return $this->map(function ($elem) {
            return $elem->getLightUiData();
        })->toAssoc();
    }
}
