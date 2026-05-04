<?php

namespace ROG\Managers;

use ROG\Core\Game;
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
      ->merge(self::getInLocation(CARD_CLAN_LOCATION_ASSIGNED))
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
   * @param int $pId
   * @param int $region
   * @return int 
   */
  public static function countDeliveredCardsByCustomerRegion($pId, $region)
  {
    $cardTypes = self::getCardsTypesByCustomerRegion($region);

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
   * @deprecated use bonus BONUS_TYPE_REFILL_HAND instead
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
   * @param int $pId
   * @param int $orderType
   * @return bool true if delivery is done
   */
  public static function hasPlayerDeliveredOrder($pId, $orderType)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_DELIVERED,$orderType)->count()>0;
  }

  /**
   * @param Player $player
   * @param int $nbCards
   * @deprecated use bonus BONUS_TYPE_REFILL_HAND instead
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
   * @param Player $player
   * @param int $nbCards
   * @return int $missingNb number of expected cards we cannot draw
   */
  public static function drawCardsToHand($player,$nbCards)
  {
    Game::get()->trace("drawCardsToHand($nbCards)");
    $cards = self::pickForLocation($nbCards, CARD_LOCATION_DECK, CARD_LOCATION_HAND,0,true);
    foreach($cards as $card){
      $card->setPId($player->getId());
      Notifications::giveCardTo($player,$card);
    }
    //Empty deck will be rare but not impossible
    $missingNb = $nbCards - $cards->count();
    Game::get()->trace("drawCardsToHand($nbCards) -> $missingNb are missing");
    if($missingNb>0) Notifications::missingCards($player,$missingNb);
    return $missingNb;
  }
  
  /**
   * @param Player $player
   * @return ClanPatronCard
   */
  public static function getPatron($player)
  {
    return self::DB()->wherePlayer($player->getId())
      ->where(self::$prefix.'location', CARD_CLAN_LOCATION_ASSIGNED)
      ->get()
      ->first();
  }
  /**
   * @param int $patronType
   * @return ClanPatronCard if assigned in this game
   */
  public static function getAssignedPatron($patronType)
  {
    return self::DB()
      ->where('type', $patronType)
      ->where('subtype', CARD_TYPE_CLAN_PATRON)
      ->where(self::$prefix.'location', CARD_CLAN_LOCATION_ASSIGNED)
      ->get()
      ->first();
  }

  /**
   * Move a card to a player
   * @param Player $player
   * @param ClanPatronCard $card
   */
  public static function giveClanCardTo(Player $player, ClanPatronCard $card)
  {
    $card->setLocation(CARD_CLAN_LOCATION_ASSIGNED);
    $card->setPId($player->getId());
    Notifications::giveClanCardTo($player, $card);

    $card->abilityOnAssign($player);
  }

  /**
   * Init the face up cards to be drafted with 1 of each clan
   */
  public static function initClanPatronsDraft()
  {
    $cards = [];
    foreach (CLANS_COLORS as $color => $clan_id) {
      $deck = CARD_CLAN_LOCATION_DECK.$clan_id;
      self::shuffle($deck);
      $cards[] = self::pickOneForLocation($deck, CARD_CLAN_LOCATION_DRAFT);
    }
    //Notify in case of waiting screen
    //Notifications::draftCards(new Collection($cards));
  }
  
  /**
   * Init the face up cards to be choosed between 2 of 1 clan
   * @param Player $player
   */
  public static function initClanPatronsAlternative($player)
  { 
    //Cards::moveAllInLocation(CARD_CLAN_LOCATION_DECK.$clan_id, CARD_CLAN_LOCATION_DRAFT);
    $cards = self::getInLocation(CARD_CLAN_LOCATION_DECK.$player->getClan());
    foreach ($cards as $card) {
      $card->setLocation(CARD_CLAN_LOCATION_DRAFT);
      $card->setPId($player->getId());
    }
    //Notify in case of waiting screen
    //Notifications::draftPlayerCards($player,$cards);
  }

  /** Creation of the cards */
  public static function setupNewGame($players, $options)
  {
    $cards = [];

    $customerTypes = Globals::getCustomerTypes();
    Notifications::initCustomersDeck($customerTypes);
    foreach (self::getCustomerCardsTypes() as $type => $card) {
      if(!in_array($card['customerType'],$customerTypes)) continue;
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
   * @param int $region the CUSTOMER region to search
   * @return array list of CARD types
   */
  public static function getCardsTypesByCustomerRegion($region){
    $types = [];
    $customerCards = self::getCustomerCardsTypes();
    foreach ($customerCards as $type => $customerCard) {
      if($region == $customerCard['region']){
        $types[] = $type;
      }
    }
    return $types;
  }
  
  public static function getCustomerTypeName(int $customerType): string{
    switch($customerType){
      case CUSTOMER_TYPE_ARTISAN    : return clienttranslate('Artisan');
      case CUSTOMER_TYPE_ELDER      : return clienttranslate('Elder');
      case CUSTOMER_TYPE_MERCHANT   : return clienttranslate('Merchant');
      case CUSTOMER_TYPE_MONK       : return clienttranslate('Monk');
      case CUSTOMER_TYPE_NOBLE      : return clienttranslate('Noble');
      case CUSTOMER_TYPE_MAGISTRATE : return clienttranslate('Magistrate');
      case CUSTOMER_TYPE_SMUGGLER   : return clienttranslate('Smuggler');
      case CUSTOMER_TYPE_SHINDOSHI  : return clienttranslate('Shindōshi');
      case CUSTOMER_TYPE_SPY        : return clienttranslate('Spy');
      case CUSTOMER_TYPE_TRADER     : return clienttranslate('Trader');
    }
    return '';
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
      CARD_ARTISAN_1 => $f([CUSTOMER_TYPE_ARTISAN, REGION_1,  Cards::getCustomerTypeName(CUSTOMER_TYPE_ARTISAN), '',  [RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_ARTISAN_2 => $f([CUSTOMER_TYPE_ARTISAN, REGION_2,  Cards::getCustomerTypeName(CUSTOMER_TYPE_ARTISAN), '',  [RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_ARTISAN_3 => $f([CUSTOMER_TYPE_ARTISAN, REGION_3,  Cards::getCustomerTypeName(CUSTOMER_TYPE_ARTISAN), '',  [RESOURCE_TYPE_SILK=>2],]), 
      CARD_ARTISAN_4 => $f([CUSTOMER_TYPE_ARTISAN, REGION_4,  Cards::getCustomerTypeName(CUSTOMER_TYPE_ARTISAN), '',  [RESOURCE_TYPE_SILK=>2],]), 
      CARD_ARTISAN_5 => $f([CUSTOMER_TYPE_ARTISAN, REGION_5,  Cards::getCustomerTypeName(CUSTOMER_TYPE_ARTISAN), '',  [RESOURCE_TYPE_RICE=>2],]), 
      CARD_ARTISAN_6 => $f([CUSTOMER_TYPE_ARTISAN, REGION_6,  Cards::getCustomerTypeName(CUSTOMER_TYPE_ARTISAN), '',  [RESOURCE_TYPE_RICE=>2],]), 
      CARD_ELDER_1 => $f([CUSTOMER_TYPE_ELDER, REGION_1 ,   Cards::getCustomerTypeName(CUSTOMER_TYPE_ELDER)   , '',   [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_ELDER_2 => $f([CUSTOMER_TYPE_ELDER, REGION_2 ,   Cards::getCustomerTypeName(CUSTOMER_TYPE_ELDER)   , '',   [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1,],]), 
      CARD_ELDER_3 => $f([CUSTOMER_TYPE_ELDER, REGION_3 ,   Cards::getCustomerTypeName(CUSTOMER_TYPE_ELDER)   , '',   [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_ELDER_4 => $f([CUSTOMER_TYPE_ELDER, REGION_4 ,   Cards::getCustomerTypeName(CUSTOMER_TYPE_ELDER)   , '',   [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1,],]), 
      CARD_ELDER_5 => $f([CUSTOMER_TYPE_ELDER, REGION_5 ,   Cards::getCustomerTypeName(CUSTOMER_TYPE_ELDER)   , '',   [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_ELDER_6 => $f([CUSTOMER_TYPE_ELDER, REGION_6 ,   Cards::getCustomerTypeName(CUSTOMER_TYPE_ELDER)   , '',   [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_MERCHANT_1 => $f([CUSTOMER_TYPE_MERCHANT, REGION_1,Cards::getCustomerTypeName(CUSTOMER_TYPE_MERCHANT) , '',[RESOURCE_TYPE_POTTERY=>3],]), 
      CARD_MERCHANT_2 => $f([CUSTOMER_TYPE_MERCHANT, REGION_2,Cards::getCustomerTypeName(CUSTOMER_TYPE_MERCHANT) , '',[RESOURCE_TYPE_RICE=>3],]), 
      CARD_MERCHANT_3 => $f([CUSTOMER_TYPE_MERCHANT, REGION_3,Cards::getCustomerTypeName(CUSTOMER_TYPE_MERCHANT) , '',[RESOURCE_TYPE_SILK=>3],]), 
      CARD_MERCHANT_4 => $f([CUSTOMER_TYPE_MERCHANT, REGION_4,Cards::getCustomerTypeName(CUSTOMER_TYPE_MERCHANT) , '',[RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1],]), 
      CARD_MERCHANT_5 => $f([CUSTOMER_TYPE_MERCHANT, REGION_5,Cards::getCustomerTypeName(CUSTOMER_TYPE_MERCHANT) , '',[RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MERCHANT_6 => $f([CUSTOMER_TYPE_MERCHANT, REGION_6,Cards::getCustomerTypeName(CUSTOMER_TYPE_MERCHANT) , '',[RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2],]), 
      CARD_MONK_1 => $f([CUSTOMER_TYPE_MONK, REGION_1,    Cards::getCustomerTypeName(CUSTOMER_TYPE_MONK)   , '',      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MONK_2 => $f([CUSTOMER_TYPE_MONK, REGION_2,    Cards::getCustomerTypeName(CUSTOMER_TYPE_MONK)   , '',      [RESOURCE_TYPE_SILK=>3,RESOURCE_TYPE_RICE=>2],]), 
      CARD_MONK_3 => $f([CUSTOMER_TYPE_MONK, REGION_3,    Cards::getCustomerTypeName(CUSTOMER_TYPE_MONK)   , '',      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MONK_4 => $f([CUSTOMER_TYPE_MONK, REGION_4,    Cards::getCustomerTypeName(CUSTOMER_TYPE_MONK)   , '',      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>3],]), 
      CARD_MONK_5 => $f([CUSTOMER_TYPE_MONK, REGION_5,    Cards::getCustomerTypeName(CUSTOMER_TYPE_MONK)   , '',      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MONK_6 => $f([CUSTOMER_TYPE_MONK, REGION_6,    Cards::getCustomerTypeName(CUSTOMER_TYPE_MONK)   , '',      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_1 => $f([CUSTOMER_TYPE_NOBLE, REGION_1,   Cards::getCustomerTypeName(CUSTOMER_TYPE_NOBLE) , '',      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_2 => $f([CUSTOMER_TYPE_NOBLE, REGION_2,   Cards::getCustomerTypeName(CUSTOMER_TYPE_NOBLE) , '',      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_3 => $f([CUSTOMER_TYPE_NOBLE, REGION_3,   Cards::getCustomerTypeName(CUSTOMER_TYPE_NOBLE) , '',      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_NOBLE_4 => $f([CUSTOMER_TYPE_NOBLE, REGION_4,   Cards::getCustomerTypeName(CUSTOMER_TYPE_NOBLE) , '',      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2],]), 
      CARD_NOBLE_5 => $f([CUSTOMER_TYPE_NOBLE, REGION_5,   Cards::getCustomerTypeName(CUSTOMER_TYPE_NOBLE) , '',      [RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_6 => $f([CUSTOMER_TYPE_NOBLE, REGION_6,   Cards::getCustomerTypeName(CUSTOMER_TYPE_NOBLE) , '',      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      //V2 : 30 new cards
      31 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_1,  Cards::getCustomerTypeName(CUSTOMER_TYPE_MAGISTRATE), '',  [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_RICE=>2,],]), 
      32 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_2,  Cards::getCustomerTypeName(CUSTOMER_TYPE_MAGISTRATE), '',  [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_SILK=>2,],]), 
      33 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_3,  Cards::getCustomerTypeName(CUSTOMER_TYPE_MAGISTRATE), '',  [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_RICE=>2,],]), 
      34 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_4,  Cards::getCustomerTypeName(CUSTOMER_TYPE_MAGISTRATE), '',  [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_POTTERY=>2,],]), 
      35 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_5,  Cards::getCustomerTypeName(CUSTOMER_TYPE_MAGISTRATE), '',  [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_POTTERY=>2,],]), 
      36 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_6,  Cards::getCustomerTypeName(CUSTOMER_TYPE_MAGISTRATE), '',  [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_SILK=>2,],]), 
      37 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_1,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SMUGGLER), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>2,    ],]), 
      38 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_2,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SMUGGLER), '',  [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      39 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_3,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SMUGGLER), '',  [RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      40 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_4,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SMUGGLER), '',  [RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      41 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_5,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SMUGGLER), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      42 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_6,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SMUGGLER), '',  [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    ],]), 
      43 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_1,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SHINDOSHI), '',  [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      44 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_2,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SHINDOSHI), '',  [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      45 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_3,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SHINDOSHI), '',  [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      46 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_4,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SHINDOSHI), '',  [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      47 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_5,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SHINDOSHI), '',  [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    ],]),
      48 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_6,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SHINDOSHI), '',  [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    ],]),
      49 => $f([CUSTOMER_TYPE_SPY, REGION_1,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SPY), '',  [RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>3,                           ],]), 
      50 => $f([CUSTOMER_TYPE_SPY, REGION_2,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SPY), '',  [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      51 => $f([CUSTOMER_TYPE_SPY, REGION_3,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SPY), '',  [RESOURCE_TYPE_RICE=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      52 => $f([CUSTOMER_TYPE_SPY, REGION_4,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SPY), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      53 => $f([CUSTOMER_TYPE_SPY, REGION_5,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SPY), '',  [RESOURCE_TYPE_SILK=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      54 => $f([CUSTOMER_TYPE_SPY, REGION_6,  Cards::getCustomerTypeName(CUSTOMER_TYPE_SPY), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      55 => $f([CUSTOMER_TYPE_TRADER, REGION_1,  Cards::getCustomerTypeName(CUSTOMER_TYPE_TRADER), '',  [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      56 => $f([CUSTOMER_TYPE_TRADER, REGION_2,  Cards::getCustomerTypeName(CUSTOMER_TYPE_TRADER), '',  [RESOURCE_TYPE_RICE=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      57 => $f([CUSTOMER_TYPE_TRADER, REGION_3,  Cards::getCustomerTypeName(CUSTOMER_TYPE_TRADER), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      58 => $f([CUSTOMER_TYPE_TRADER, REGION_4,  Cards::getCustomerTypeName(CUSTOMER_TYPE_TRADER), '',  [RESOURCE_TYPE_SILK=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      59 => $f([CUSTOMER_TYPE_TRADER, REGION_5,  Cards::getCustomerTypeName(CUSTOMER_TYPE_TRADER), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      60 => $f([CUSTOMER_TYPE_TRADER, REGION_6,  Cards::getCustomerTypeName(CUSTOMER_TYPE_TRADER), '',  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_POTTERY=>3,                           ],]), 
      
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
        'abilityName' => $t[2],
        'desc' => $t[3],
      ];
    };
    return [
      // 8 unique Clan Patron cards
      //BEWARE Strings are copied in STATS
      PATRON_MASTER_ENGINEER => $f([CLAN_CRAB,    clienttranslate('Kaiu Shihobu'),   clienttranslate('Master Engineer'),                 '',  ]), 
      PATRON_TRADER         => $f([CLAN_CRAB,     clienttranslate('Yasuki Taka'),    clienttranslate('Wily Trader'),                     '',  ]), 
      PATRON_SON_OF_STORM   => $f([CLAN_MANTIS,   clienttranslate('Yoritomo'),       clienttranslate('Son of Storms'),                   '',  ]), 
      PATRON_PRIESTESS      => $f([CLAN_MANTIS,   clienttranslate('Kudaka'),         clienttranslate('Priestess of Tempests and Tides'),  '',  ]), 
      PATRON_IRON_CRANE     => $f([CLAN_CRANE,    clienttranslate('Daidoji Uji'),    clienttranslate('The Iron Crane'),                  '',  ]), 
      PATRON_DARLING        => $f([CLAN_CRANE,    clienttranslate('Kakita Ryoku'),   clienttranslate('Darling of the Courts'),           '',  ]),
      PATRON_GOVERNOR       => $f([CLAN_SCORPION, clienttranslate('Shosuro Hyobu'),  clienttranslate('Governor of the City of lies'),    '',  ]),  
      PATRON_LADY           => $f([CLAN_SCORPION, clienttranslate('Bayushi Kashiko'),clienttranslate('Lady of Whispers'),                '',  ]),  
      //+8 unique Clan Patron cards
      PATRON_SCION_OF_VOID      => $f([CLAN_PHOENIX,  clienttranslate('Isawa Kaede'),         clienttranslate('Scion of Void'),             '',  ]), 
      PATRON_SCION_OF_EARTH     => $f([CLAN_PHOENIX,  clienttranslate('Isawa Tadaka'),        clienttranslate('Scion of Earth'),            '',  ]), 
      PATRON_REVEREND_SENSEI    => $f([CLAN_LION,     clienttranslate('Akodo Kage'),          clienttranslate('Reverend Sensei'),           '',  ]), 
      PATRON_LIONS_LADY         => $f([CLAN_LION,     clienttranslate('Matsu Tsuko'),         clienttranslate('Lady of Lions'),             '',  ]), 
      PATRON_IMPERIAL_ENVOY     => $f([CLAN_DRAGON,   clienttranslate('Kitsuki Yaruma'),      clienttranslate('Imperial Envoy'),            '',  ]), 
      PATRON_TATTOOED_MONK      => $f([CLAN_DRAGON,   clienttranslate('Togashi Mitsu'),       clienttranslate('Ise Zumi Tattooed Monk'),    '',  ]), 
      PATRON_MAGNATE_SAND_ROAD  => $f([CLAN_UNICORN,  clienttranslate('Ide Tadaji'),          clienttranslate('Magnate of the Sand Road'),  '',  ]), 
      PATRON_MISTRESS_OF_WINDS  => $f([CLAN_UNICORN,  clienttranslate('Shinjo Altansarnai'),  clienttranslate('Mistress of the Five Winds'),'',  ]), 

    ];
  }
}
