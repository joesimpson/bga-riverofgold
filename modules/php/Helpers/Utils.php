<?php
namespace ROG\Helpers;

abstract class Utils extends \APP_DbObject
{
    public static function filter(&$data, $filter)
    {
        $data = array_values(array_filter($data, $filter));
    }

    public static function die($args = null)
    {
        if (is_null($args)) {
            throw new \BgaVisibleSystemException(
                implode('<br>', self::$logmsg)
            );
        }
        throw new \BgaVisibleSystemException(json_encode($args));
    }

    /**
     * @param int $num1 
     * @param int $num2
     * @return int
     */
    public static function positive_modulo($num1,$num2)
    {
        $r = $num1 % $num2;
        if ($r < 0)
        {
            $r += abs($num2);
        }
        return $r;
    }

    ////////////////////////////////////////////////////////////////
    //////// GAME SPECIFIC
    ////////////////////////////////////////////////////////////////
    ///**
    // * @param int region
    // * @return array of int 
    // * 
    // * Examples : 
    // * 1 -> [6,2],
    // * 2 -> [1,3],
    // * 3 -> [2,4],
    // * 4 -> [3,5],
    // * 5 -> [4,6],
    // * 6 -> [5,1],
    // */
    //public static function getAdjacentRegions($region)
    //{
    //    $regions = [];
    //    //$nbRegions = count(REGIONS);
    //    //$regions[] = ($region - 1) % $nbRegions +1;
    //    //$regions[] = ($region + 1) % $nbRegions +1
    //    switch($region){
    //        //Maybe we cannot consider other side of board as adjacent
    //        case 1: return [2];
    //        case 2: return [1,3];
    //        case 3: return [2,4];
    //        case 4: return [3,5];
    //        case 5: return [4,6];
    //        //Maybe we cannot consider other side of board as adjacent
    //        case 6: return [5];
    //    }
    //    return $regions;
    //}
    
}
