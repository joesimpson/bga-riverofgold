<?php
namespace ROG\Core;
use RiverOfGold;

/*
 * Game: a wrapper over table object to allow more generic modules
 */
class Game
{
  public static function get()
  {
    return RiverOfGold::get();
  }
}
