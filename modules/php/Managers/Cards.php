<?php

namespace ROG\Managers;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Models\Card;
use ROG\Models\ClanPatronCard;
use ROG\Models\CustomerCard;
use ROG\Models\Player;

/* Class to manage all the cards */

class Cards extends \ROG\Helpers\Pieces
{
  protected static $table = 'cards';
  protected static $prefix = 'card_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['player_id', 'type', 'subtype'];
  protected static $autoreshuffle = true;
  protected static $autoreshuffleCustom = [CARD_LOCATION_DECK => CARD_LOCATION_DISCARD];

  protected static function cast($row)
  {
    $type = isset($row['type']) ? $row['type'] : null;
    $subtype = isset($row['subtype']) ? $row['subtype'] : null;
    switch ($subtype) {
      case CARD_TYPE_CUSTOMER:
        $data = self::getCustomerCardsTypes()[$type];
        return new CustomerCard($row, $data);
      case CARD_TYPE_CLAN_PATRON:
        $data = self::getClanPatronCardsTypes()[$type];
        return new ClanPatronCard($row, $data);
    }
    $data = [];
    return new Card($row, $data);
  }

  /**
   * @param int $currentPlayerId Id of current player loading the game
   * @return array all cards visible by this player
   */
  public static function getUiData($currentPlayerId)
  {
    $privateCards = self::getPlayerHandOrders($currentPlayerId);

    return self::getInLocation(CARD_LOCATION_DELIVERED)
      ->merge($privateCards)
      ->map(function ($card) {
        return $card->getUiData();
      })
      ->toArray();
  } 
 
  /**
   * @param int $pId
   * @param string $location (optional)
   * @return int number of ALL CARDS owned by that player and in that $location,
   *   or ALL CARDS owned by that player if location not given
   */
  public static function countPlayerCards($pId, $location = null)
  {
    return self::getFilteredQuery($pId, $location)->count();
  }
  
  /**
   * @param int $pId
   * @param int $customerType
   * @return int 
   */
  public static function countDeliveredCardsByCustomerType($pId, $customerType)
  {
    $cardTypes = self::getCardsTypesByCustomer($customerType);

    return self::DB()->wherePlayer($pId)
      ->where(self::$prefix.'location', CARD_LOCATION_DELIVERED)
      ->whereIn('type', $cardTypes)
      ->count();
  }
  
  /**
   * Return all HAND cards of this player
   * @param int $pId
   * @return Collection
   */
  public static function getPlayerHandOrders($pId)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_HAND)->get();
  }
  
  /**
   * @param int $pId
   * @return Collection
   */
  public static function getPlayerFutureHandOrders($pId)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_WAIT_FOR_HAND)->get();
  }
  public static function refreshHands($players)
  {
    foreach ($players as $pid => $player) {
      Notifications::refreshHand($pid,Cards::getPlayerHandOrders($pid));
    }
  }
  /**
   * Return all delivered cards of this player
   * @param int $pId
   * @return Collection
   */
  public static function getPlayerDeliveredOrders($pId)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_DELIVERED)->get();
  }

  /**
   * @param Player $player
   * @param int $nbCards
   */
  public static function prepareCardsToRefillHand($player,$nbCards)
  {
    $cards = self::pickForLocation($nbCards, CARD_LOCATION_DECK, CARD_LOCATION_WAIT_FOR_HAND,0,true);
    foreach($cards as $card){
      $card->setPId($player->getId());
    }
    return $cards;
  }
  
  /**
   * Move a card to a player
   * @param Player $player
   * @param ClanPatronCard $card
   */
  public static function giveClanCardTo($player, $card)
  {
    $card->setLocation(CARD_CLAN_LOCATION_ASSIGNED);
    $card->setPId($player->getId());
    Notifications::giveClanCardTo($player, $card);
  }

  /**
   * Init the face up cards to be drafted with 1 of each clan
   */
  public static function initClanPatronsDraft()
  {
    foreach (CLANS_COLORS as $color => $clan_id) {
      $deck = CARD_CLAN_LOCATION_DECK.$clan_id;
      self::shuffle($deck);
      self::pickOneForLocation($deck, CARD_CLAN_LOCATION_DRAFT);
    }
  }

  /** Creation of the cards */
  public static function setupNewGame($players, $options)
  {
    $cards = [];

    foreach (self::getCustomerCardsTypes() as $type => $card) {
      $cards[] = [
        'location' => CARD_LOCATION_DECK,
        'type' => $type,
        'subtype' => CARD_TYPE_CUSTOMER,
      ];
    }
    
    if(!Globals::isExpansionClansDisabled()){
      foreach (self::getClanPatronCardsTypes() as $type => $card) {
        $cards[] = [
          'location' => CARD_CLAN_LOCATION_DECK. $card['clan'],
          'type' => $type,
          'subtype' => CARD_TYPE_CLAN_PATRON,
        ];
      }
    }

    self::create($cards);
    self::shuffle(CARD_LOCATION_DECK);
  }
 
  /**
   * @param int $customerType the CUSTOMER type to search
   * @return array list of CARD types
   */
  public static function getCardsTypesByCustomer($customerType){
    $types = [];
    $customerCards = self::getCustomerCardsTypes();
    foreach ($customerCards as $type => $customerCard) {
      if($customerType == $customerCard['customerType']){
        $types[] = $type;
      }
    }
    return $types;
  }
  /**
   * @return array of all the different types of Customer Cards
   */
  public static function getCustomerCardsTypes()
  {
    $f = function ($t) {
      return [
        'customerType' => $t[0],
        'region' => $t[1],
        'title' => $t[2],
        'desc' => $t[3],
        'cost' => $t[4],
      ];
    };
    return [
      // 30 unique CUSTOMER cards
      1 => $f([CUSTOMER_TYPE_ARTISAN, REGION_1,  clienttranslate('Artisan')   , clienttranslate('Artisan 1'),  [RESOURCE_TYPE_POTTERY=>2],]), 
      2 => $f([CUSTOMER_TYPE_ARTISAN, REGION_2,  clienttranslate('Artisan')   , clienttranslate('Artisan 2'),  [RESOURCE_TYPE_POTTERY=>2],]), 
      3 => $f([CUSTOMER_TYPE_ARTISAN, REGION_3,  clienttranslate('Artisan')   , clienttranslate('Artisan 3'),  [RESOURCE_TYPE_SILK=>2],]), 
      4 => $f([CUSTOMER_TYPE_ARTISAN, REGION_4,  clienttranslate('Artisan')   , clienttranslate('Artisan 4'),  [RESOURCE_TYPE_SILK=>2],]), 
      5 => $f([CUSTOMER_TYPE_ARTISAN, REGION_5,  clienttranslate('Artisan')   , clienttranslate('Artisan 5'),  [RESOURCE_TYPE_RICE=>2],]), 
      6 => $f([CUSTOMER_TYPE_ARTISAN, REGION_6,  clienttranslate('Artisan')   , clienttranslate('Artisan 6'),  [RESOURCE_TYPE_RICE=>2],]), 
      7 => $f([CUSTOMER_TYPE_ELDER, REGION_1 ,   clienttranslate('Elder')   , clienttranslate('Elder 1'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      8 => $f([CUSTOMER_TYPE_ELDER, REGION_2 ,   clienttranslate('Elder')   , clienttranslate('Elder 2'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1,],]), 
      9 => $f([CUSTOMER_TYPE_ELDER, REGION_3 ,   clienttranslate('Elder')   , clienttranslate('Elder 3'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      10 => $f([CUSTOMER_TYPE_ELDER, REGION_4,   clienttranslate('Elder')   , clienttranslate('Elder 4'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1,],]), 
      11 => $f([CUSTOMER_TYPE_ELDER, REGION_5,   clienttranslate('Elder')   , clienttranslate('Elder 5'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      12 => $f([CUSTOMER_TYPE_ELDER, REGION_6,   clienttranslate('Elder')   , clienttranslate('Elder 6'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      13 => $f([CUSTOMER_TYPE_MERCHANT, REGION_1,clienttranslate('Merchant')   , clienttranslate('Merchant 1'),[RESOURCE_TYPE_POTTERY=>3],]), 
      14 => $f([CUSTOMER_TYPE_MERCHANT, REGION_2,clienttranslate('Merchant')   , clienttranslate('Merchant 2'),[RESOURCE_TYPE_RICE=>3],]), 
      15 => $f([CUSTOMER_TYPE_MERCHANT, REGION_3,clienttranslate('Merchant')   , clienttranslate('Merchant 3'),[RESOURCE_TYPE_SILK=>3],]), 
      16 => $f([CUSTOMER_TYPE_MERCHANT, REGION_4,clienttranslate('Merchant')   , clienttranslate('Merchant 4'),[RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1],]), 
      17 => $f([CUSTOMER_TYPE_MERCHANT, REGION_5,clienttranslate('Merchant')   , clienttranslate('Merchant 5'),[RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      18 => $f([CUSTOMER_TYPE_MERCHANT, REGION_6,clienttranslate('Merchant')   , clienttranslate('Merchant 6'),[RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2],]), 
      19 => $f([CUSTOMER_TYPE_MONK, REGION_1,    clienttranslate('Monk')   , clienttranslate('Monk 1'),        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      20 => $f([CUSTOMER_TYPE_MONK, REGION_2,    clienttranslate('Monk')   , clienttranslate('Monk 2'),        [RESOURCE_TYPE_SILK=>3,RESOURCE_TYPE_RICE=>2],]), 
      21 => $f([CUSTOMER_TYPE_MONK, REGION_3,    clienttranslate('Monk')   , clienttranslate('Monk 3'),        [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],]), 
      22 => $f([CUSTOMER_TYPE_MONK, REGION_4,    clienttranslate('Monk')   , clienttranslate('Monk 4'),        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>3],]), 
      23 => $f([CUSTOMER_TYPE_MONK, REGION_5,    clienttranslate('Monk')   , clienttranslate('Monk 5'),        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      24 => $f([CUSTOMER_TYPE_MONK, REGION_6,    clienttranslate('Monk')   , clienttranslate('Monk 6'),        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      25 => $f([CUSTOMER_TYPE_NOBLE, REGION_1,   clienttranslate('Noble')   , clienttranslate('Noble 1'),      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2],]), 
      26 => $f([CUSTOMER_TYPE_NOBLE, REGION_2,   clienttranslate('Noble')   , clienttranslate('Noble 2'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      27 => $f([CUSTOMER_TYPE_NOBLE, REGION_3,   clienttranslate('Noble')   , clienttranslate('Noble 3'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],]), 
      28 => $f([CUSTOMER_TYPE_NOBLE, REGION_4,   clienttranslate('Noble')   , clienttranslate('Noble 4'),      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2],]), 
      29 => $f([CUSTOMER_TYPE_NOBLE, REGION_5,   clienttranslate('Noble')   , clienttranslate('Noble 5'),      [RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      30 => $f([CUSTOMER_TYPE_NOBLE, REGION_6,   clienttranslate('Noble')   , clienttranslate('Noble 6'),      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
    ];
  }
  
  
  /**
   * @return array of all the different types of Clan Patron Cards
   */
  public static function getClanPatronCardsTypes()
  {
    $f = function ($t) {
      return [
        'clan' => $t[0],
        'name' => $t[1],
        'ability_name' => $t[2],
        'desc' => $t[3],
      ];
    };
    return [
      // 8 unique Clan Patron cards
      1 => $f([CLAN_CRAB,     'Kaiu Shihobu',   clienttranslate('Master Engineer'),                 '',  ]), 
      2 => $f([CLAN_CRAB,     'Yasuki Taka',    clienttranslate('Wily Trader'),                     '',  ]), 
      3 => $f([CLAN_MANTIS,   'Yoritomo',       clienttranslate('Son of Storms'),                   '',  ]), 
      4 => $f([CLAN_MANTIS,   'Kudaka',         clienttranslate('Priestess of tempest and Tides'),  '',  ]), 
      5 => $f([CLAN_CRANE,    'Daidoji Uji',    clienttranslate('The Iron Crane'),                  '',  ]), 
      6 => $f([CLAN_CRANE,    'Kakita Ryoku',   clienttranslate('Darling of the Courts'),           '',  ]),
      7 => $f([CLAN_SCORPION, 'Shosuro Hyobu',  clienttranslate('Governor of the City of lies'),    '',  ]),  
      8 => $f([CLAN_SCORPION, 'Bayushi Kashiko',clienttranslate('Lady of Whispers'),                '',  ]),  
    ];
  }
}
