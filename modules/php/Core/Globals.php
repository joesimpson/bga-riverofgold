<?php

namespace ROG\Core;

use ROG\Core\Game;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Models\Player;

/*
 * Globals
 */

class Globals extends \ROG\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'turn' => 'int',
    'era' => 'int',
    //save player whose turn is playing, even when others may take decisions
    'turnPlayer' => 'int',
    'firstPlayer' => 'int',
    //save player who ended the game
    'endPlayer' => 'int',

    'lastBuiltTile' => 'int',
    'lastBuiltLocationOrigin' => 'str',
    'lastSailedShip' => 'int',
    
    'turnMainActionDone' => 'str',
    
    //Trade is possible in many states, thus we need to keep a trace of the previous state
    'stateBeforeTrade' => 'int',

    'currentBonus' => 'int',
    'currentBonusDatas' => 'obj',
    //array of Bonuses to be earned by selection of something -> moved to player table
    //'bonuses' => 'obj',

    'endScoring' => 'obj',
    'customerTypes' => 'obj',
    'regionCustomTracks' => 'obj',

    //Datas for automa player
    'automaActive' => 'bool',
    'automaClan' => 'int',
    'automaScore' => 'int',
    'automaDie' => 'int',

    //Undo log module
    'choices' => 'int',

    // Game options
    'optionClanPatrons' => 'int', 
    'optionSeishin' => 'int', 
    'optionTracks' => 'int', 
    'optionCustomers' => 'int', 
    'optionImperialMarkets' => 'int', 

  ];
 
  /*
   * Setup new game
   */
  public static function setupNewGame($players, $options)
  {
    self::setTurn(0);
    self::setEra(1);
    //self::setBonuses([]);
    self::setCurrentBonus(null);
    self::setCurrentBonusDatas(null);
    self::setStateBeforeTrade(null);

    self::setEndPlayer(null);
    self::setEndScoring([]);
    self::setLastBuiltTile(null);
    self::setLastBuiltLocationOrigin(null);
    self::setLastSailedShip(null);
    self::setTurnMainActionDone(null);

    $nbPlayers = count($players);
    foreach($players as $pId => $player){
      self::setFirstPlayer($pId);
      self::setTurnPlayer($pId);
      break;
    }
    self::setAutomaClan(null);
    self::setAutomaActive(false);
    self::setAutomaScore(0);
    self::setAutomaDie(null);

    //              --------------------------------------------
    //GAME OPTIONS  --------------------------------------------
    //              --------------------------------------------

    $optionClans = OPTION_EXPANSION_CLANS_OFF;
    Utils::updateDataFromArray($options,OPTION_EXPANSION_CLANS,$optionClans);
    self::setOptionClanPatrons($optionClans);
    
    $optionSeishin = OPTION_SEISHIN_OFF;
    //displaycondition for 1 or 2p only :
    if(in_array($nbPlayers,[1,2])){
      Utils::updateDataFromArray($options,OPTION_SEISHIN,$optionSeishin);
    }
    Globals::setOptionSeishin($optionSeishin);

    $regionTracks = null;
    $optionTracks = OPTION_TRACKS_OFF;
    Utils::updateDataFromArray($options,OPTION_TRACKS,$optionTracks);
    if($optionTracks == OPTION_TRACKS_CUSTOM) {
      //PICK 6 RANDOM 
      $tracksToAssign = array_keys(CUSTOM_REGION_TRACKS);
      shuffle($tracksToAssign);
      foreach (REGIONS as $region){
        $trackIndex = array_shift($tracksToAssign);
        $regionTracks[$region] = $trackIndex;
      }
    }
    self::setOptionTracks($optionTracks);
    self::setRegionCustomTracks($regionTracks);

    $optionCustomers = OPTION_CUSTOMERS_BASE;
    Utils::updateDataFromArray($options,OPTION_CUSTOMERS,$optionCustomers);
    $customerTypes = [];
    switch($optionCustomers){
      case OPTION_CUSTOMERS_BASE:
      default:
        $customerTypes = [ 
          CUSTOMER_TYPE_ARTISAN, 
          CUSTOMER_TYPE_ELDER, 
          CUSTOMER_TYPE_MERCHANT, 
          CUSTOMER_TYPE_MONK, 
          CUSTOMER_TYPE_NOBLE, 
        ];
        break;
      case OPTION_CUSTOMERS_TRADEFAVOR:
        $customerTypes = [ 
          CUSTOMER_TYPE_ARTISAN, 
          CUSTOMER_TYPE_MONK, 
          CUSTOMER_TYPE_NOBLE, 
          CUSTOMER_TYPE_SMUGGLER, 
          CUSTOMER_TYPE_SHINDOSHI, 
          CUSTOMER_TYPE_TRADER, 
        ];
        break;
      case OPTION_CUSTOMERS_BUILDING_INFLUENCE:
        $customerTypes = [ 
          CUSTOMER_TYPE_ARTISAN, 
          CUSTOMER_TYPE_ELDER, 
          CUSTOMER_TYPE_MERCHANT, 
          CUSTOMER_TYPE_MAGISTRATE, 
          CUSTOMER_TYPE_SMUGGLER, 
          CUSTOMER_TYPE_TRADER, 
        ];
        break;
      case OPTION_CUSTOMERS_INTOCITY:
        $customerTypes = [ 
          CUSTOMER_TYPE_ELDER, 
          CUSTOMER_TYPE_MERCHANT, 
          CUSTOMER_TYPE_MONK, 
          CUSTOMER_TYPE_NOBLE, 
          CUSTOMER_TYPE_MAGISTRATE, 
          CUSTOMER_TYPE_SPY, 
        ];
        break;
      case OPTION_CUSTOMERS_NIGHT_MONKS:
        $customerTypes = [ 
          CUSTOMER_TYPE_MONK, 
          CUSTOMER_TYPE_MAGISTRATE, 
          CUSTOMER_TYPE_SMUGGLER, 
          CUSTOMER_TYPE_SHINDOSHI, 
          CUSTOMER_TYPE_SPY, 
          CUSTOMER_TYPE_TRADER, 
        ];
        break;
      case OPTION_CUSTOMERS_RANDOM:
        $customerTypes = array_rand(array_flip(ALL_CUSTOMER_TYPES), 6);
        break;
    }
    self::setOptionCustomers($optionCustomers);
    Globals::setCustomerTypes($customerTypes);
    
    $optionImperialMarkets = OPTION_MARKETS_BASE;
    Utils::updateDataFromArray($options,OPTION_MARKETS,$optionImperialMarkets);
    Globals::setOptionImperialMarkets($optionImperialMarkets);
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
    Stats::inc("turns_number");
    //self::setBonuses([]);
    //TODO JSA players resetBonus
    self::setCurrentBonus(null);
  }

  /**
   * @param Player $player
   * @param int $type
   * @param string $typeText (optional)
   * @param bool $sendNotif (optional)
   */
  public static function addBonus(&$player, $type, $typeText = '', $sendNotif = true)
  {
    $bonuses = $player->getBonuses();
    $bonuses[] = $type;
    $player->setBonuses($bonuses);
    if($sendNotif) Notifications::addBonus($player, $type, $typeText);
  }
  
  public static function addBonusWithDatas(Player &$player,int $type,array $datas,string $typeText = '',bool $sendNotif = true)
  {
    $bonuses = $player->getBonuses();
    $datasToSave = $datas;
    //$datasToSave['type'] = $type;
    $nextKey = 1;
    if(isset($bonuses) && array_key_exists('datas',$bonuses) && array_key_exists($type,$bonuses['datas']) ){
      $nextKey = max(array_keys($bonuses['datas'][$type])) +1;
    }
    $bonuses['datas'][$type][$nextKey] = $datasToSave;
    $player->setBonuses($bonuses);
    if($sendNotif) Notifications::addBonus($player, $type, $typeText,$datas['bonusQuantity']);
  }
  /**
   * Remove bonus from player pending list while we treat the bonus
   * @param Player $player
   * @param int $type
   * @param ?int $bonusKey (Optional) key to remove in 'datas' array
   * @return array|int|null removed datas
   */
  public static function removeBonus(Player $player, int $type,?int $bonusKey = null) : array|int|null
  {
    $bonuses = $player->getBonuses();
    if(isset($bonusKey)){
      if(!array_key_exists('datas',$bonuses)) return null;
      if(!array_key_exists($type,$bonuses['datas'])) return null;
      //$old = ['type' =>$type, 'datas' => $bonuses['datas'][$type][$bonusKey]];
      $old = $bonuses['datas'][$type][$bonusKey];
      unset($bonuses['datas'][$type][$bonusKey]);
      //REmove datas if empty :
      if(empty($bonuses['datas'][$type])) unset($bonuses['datas'][$type]);
      if(empty($bonuses['datas'])) unset($bonuses['datas']);
    }
    else {
      $key = array_search($type,$bonuses);
      if(!isset($key)) return null;
      $old = $bonuses[$key];
      unset($bonuses[$key]);
    }
    $player->setBonuses($bonuses);
    return $old;
  }
  
  /**
   * @return bool
   */
  public static function isLastTurnTriggered()
  {
    $endingPlayer = self::getEndPlayer();
    return isset($endingPlayer) && $endingPlayer >0;
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
