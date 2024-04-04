<?php

namespace ROG\Core;

use ROG\Core\Game;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;

/*
 * Globals
 */

class Globals extends \ROG\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'turn' => 'int',
    'era' => 'int',
    'firstPlayer' => 'int',
    
    //Trade is possible in many states, thus we need to keep a trace of the previous state
    'stateBeforeTrade' => 'int',

    'currentBonus' => 'int',
    //array of Bonuses to be earned by selection of something -> moved to player table
    //'bonuses' => 'obj',


    //Undo log module
    'choices' => 'int',

    // Game options
    'optionClanPatrons' => 'int', 

  ];
 
  /*
   * Setup new game
   */
  public static function setupNewGame($players, $options)
  {
    self::setTurn(0);
    self::setEra(0);
    //self::setBonuses([]);
    self::setCurrentBonus(null);
    self::setStateBeforeTrade(null);

    foreach($players as $pId => $player){
      self::setFirstPlayer($pId);
      break;
    }

    //              --------------------------------------------
    //GAME OPTIONS  --------------------------------------------
    //              --------------------------------------------

    self::setOptionClanPatrons($options[OPTION_EXPANSION_CLANS]);
  }

  /**
   * @return bool
   */
  public static function isExpansionClansDisabled()
  {
    $option = self::getOptionClanPatrons();
    return OPTION_EXPANSION_CLANS_OFF == $option;
  }

  /**
   * @return bool
   */
  public static function isExpansionClansDraft()
  {
    $option = self::getOptionClanPatrons();
    return OPTION_EXPANSION_CLANS_DRAFT == $option;
  }
  /**
   * @return bool
   */
  public static function isExpansionClansAlternative()
  {
    $option = self::getOptionClanPatrons();
    return OPTION_EXPANSION_CLANS_ALTERNATIVE == $option;
  }
   
  /**
   * Setup new game turn
   */
  public static function setupNewTurn()
  {
    self::incTurn(1);
    //self::setBonuses([]);
    //TODO JSA players resetBonus
    self::setCurrentBonus(null);
  }

  /**
   * @param Player $player
   * @param int $type
   */
  public static function addBonus($player, $type)
  {
    //TODO JSA manage opponent choice 
    $bonuses = $player->getBonuses();
    $bonuses[] = $type;
    $player->setBonuses($bonuses);
  }
  /**
   * @param Player $player
   * @param int $type
   */
  public static function removeBonus($player, $type)
  {
    $bonuses = $player->getBonuses();
    $key = array_search($type,$bonuses);
    if(!isset($key)) return;
    unset($bonuses[$key]);
    $player->setBonuses($bonuses);
  }

  //////////////////////////////////////////////////////////////////////////////////////

  protected static $table = 'global_variables';
  protected static $primary = 'name';
  protected static function cast($row)
  {
    $val = json_decode(\stripslashes($row['value']), true);
    return self::$variables[$row['name']] == 'int' ? ((int) $val) : $val;
  }

  /*
   * Fetch all existings variables from DB
   */
  protected static $data = [];
  public static function fetch()
  {
    // Turn of LOG to avoid infinite loop (Globals::isLogging() calling itself for fetching)
    $tmp = self::$log;
    self::$log = false;

    foreach (self::DB()
        ->select(['value', 'name'])
        ->get(false)
      as $name => $variable) {
      if (\array_key_exists($name, self::$variables)) {
        self::$data[$name] = $variable;
      }
    }
    self::$initialized = true;
    self::$log = $tmp;
  }

  /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
  public static function create($name)
  {
    if (!\array_key_exists($name, self::$variables)) {
      return;
    }

    $default = [
      'int' => 0,
      'obj' => [],
      'bool' => false,
      'str' => '',
    ];
    $val = $default[self::$variables[$name]];
    self::DB()->insert(
      [
        'name' => $name,
        'value' => \json_encode($val),
      ],
      true
    );
    self::$data[$name] = $val;
  }

  /*
   * Magic method that intercept not defined static method and do the appropriate stuff
   */
  public static function __callStatic($method, $args)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    if (preg_match('/^([gs]et|inc|is)([A-Z])(.*)$/', $method, $match)) {
      // Sanity check : does the name correspond to a declared variable ?
      $name = strtolower($match[2]) . $match[3];
      if (!\array_key_exists($name, self::$variables)) {
        throw new \InvalidArgumentException("Property {$name} doesn't exist");
      }

      // Create in DB if don't exist yet
      if (!\array_key_exists($name, self::$data)) {
        self::create($name);
      }

      if ($match[1] == 'get') {
        // Basic getters
        return self::$data[$name];
      } elseif ($match[1] == 'is') {
        // Boolean getter
        if (self::$variables[$name] != 'bool') {
          throw new \InvalidArgumentException("Property {$name} is not of type bool");
        }
        return (bool) self::$data[$name];
      } elseif ($match[1] == 'set') {
        // Setters in DB and update cache
        $value = $args[0];
        if (self::$variables[$name] == 'int') {
          $value = (int) $value;
        }
        if (self::$variables[$name] == 'bool') {
          $value = (bool) $value;
        }

        self::$data[$name] = $value;
        self::DB()->update(['value' => \addslashes(\json_encode($value))], $name);
        return $value;
      } elseif ($match[1] == 'inc') {
        if (self::$variables[$name] != 'int') {
          throw new \InvalidArgumentException("Trying to increase {$name} which is not an int");
        }

        $getter = 'get' . $match[2] . $match[3];
        $setter = 'set' . $match[2] . $match[3];
        return self::$setter(self::$getter() + (empty($args) ? 1 : $args[0]));
      }
    }
    return undefined;
  }
}
