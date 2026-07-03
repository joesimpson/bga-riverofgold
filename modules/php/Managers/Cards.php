<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Models\AutomaActionCard;
use ROG\Models\Card;
use ROG\Models\CityCard;
use ROG\Models\ClanPatronCard;
use ROG\Models\CustomerCard;
use ROG\Models\Player;
use ROG\Models\ScenarioCard;
use ROG\Models\ScenarioType;

/* Class to manage all the cards */

class Cards extends \ROG\Helpers\Pieces
{
  protected static $table = 'cards';
  protected static $prefix = 'card_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['player_id', 'type', 'subtype','resources','card_played'];
  protected static $autoreshuffle = true;
  protected static $autoreshuffleCustom = [CARD_LOCATION_DECK => CARD_LOCATION_DISCARD];
  protected static $autoreshuffleListener = array( 'obj' => self::class, 'method' => 'reshuffleDeck' );

  protected static function reshuffleDeck($fromLocation)
  {
    $deckSize = Cards::countInLocation(CARD_LOCATION_DECK);
    $discardSize = Cards::countInLocation(CARD_LOCATION_DISCARD);
    Notifications::reshuffleDeck($deckSize, $discardSize);
  }

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
      case CARD_TYPE_AUTOMA_ACTION:
        $data = AutomaCards::getAutomaActionCardsTypes()[$type];
        return new AutomaActionCard($row, $data);
      case CARD_TYPE_SCENARIO:
        $data = Cards::getScenarioCardsTypes()[$type];
        return new ScenarioCard($row, $data);
      case CARD_TYPE_CITY:
        $data = CityCards::getCityCardsTypes()[$type];
        return new CityCard($row, $data);
    }
    $data = [];
    return new Card($row, $data);
  }

  /**
   * @param int $currentPlayerId Id of current player loading the game
   * @return array all cards visible by this player
   */
  public static function getUiData(int $currentPlayerId): array
  {
    $privateCards = self::getPlayerHandOrders($currentPlayerId);
    $privateCityCards = CityCards::getPlayerHand($currentPlayerId);

    return self::getInLocation(CARD_LOCATION_DELIVERED)
      ->merge(self::getInLocationOrdered(CARD_LOCATION_MAP_REGION."%"))
      ->merge(self::getInLocation(CARD_CLAN_LOCATION_ASSIGNED))
      ->merge(self::getInLocation(CARD_SCENARIO_LOCATION_ASSIGNED))
      ->merge(self::getInLocationOrdered(CARD_AUTOMA_LOCATION_PLAYED))
      ->merge($privateCards)
      ->merge($privateCityCards)
      ->map(function ($card) {
        return $card->getUiData();
      })
      ->toArray();
  } 
 
  /**
   * Get specific piece by id
   */
  public static function get($id, $raiseExceptionIfNotEnough = true) : ?Card
  {
    return parent::get($id, $raiseExceptionIfNotEnough);
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
  
  public static function countPlayerHiddenDeliveredCustomers(int $playerId) : int
  {
    return self::getFilteredQuery($playerId, CARD_LOCATION_DELIVERED_HIDDEN)->count();
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
  public static function refreshHands(array $players)
  {
    foreach ($players as $pid => $player) {
      if(isset($player['is_automa']) && $player['is_automa']) continue;
      $hand = Cards::getPlayerHandOrders($pid);
      $hand = $hand->merge(CityCards::getPlayerHand($pid));
      Notifications::refreshHand($pid,$hand);
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
  
  public static function getPlayerHiddenDeliveredCustomers(int $pId)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_DELIVERED_HIDDEN)->get();
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

  /** REVEAL and DELIVER Customer cards */
  public static function deliverHiddenCards(Player $player)
  {
    $player_id = $player->getId();
    Game::get()->trace("deliverHiddenCards($player_id)");
    $cards = Cards::getPlayerHiddenDeliveredCustomers($player_id);
    foreach($cards as $customerCard){
      $customerCard->setLocation(CARD_LOCATION_DELIVERED);
      Notifications::deliver($player,$customerCard);
      switch($customerCard->getCustomerType()){
        case CUSTOMER_TYPE_ELDER:
          //marker will be used for score computation
          Meeples::addClanMarkerOnElderSpace($player,$customerCard->getRegion());
          break;
      }
    }
  }
  
  public static function createCustomerCardAsDelivered(Player $player,int $cardType) : CustomerCard
  {
    $elt = [
      'type' => $cardType,
      'subtype' => CARD_TYPE_CUSTOMER,
      'location' => CARD_LOCATION_DELIVERED,
      'player_id' => $player->getId(),
    ];
    $card = self::singleCreate($elt);
    Notifications::deliver($player,$card);
    return $card;
  }
  
  public static function setupDeliveredCustomer(Player &$player,int $cardType) : CustomerCard
  {

    $cardIds = Cards::getIdsByTypes(CARD_TYPE_CUSTOMER,[$cardType ]);
    if(count($cardIds) > 0){
      //MOVE CREATED CARD
      $cardId = $cardIds[0];
      $card = Cards::get($cardId);
      $previousLocation = $card->getLocation();
      $previousPlayerId = $card->getPId();
      $card->setLocation(CARD_LOCATION_DELIVERED);
      $card->setPId($player->getId());
      Notifications::deliver($player,$card,$previousLocation);
      //replace card in previous location (player hands) + notify :
      if($previousLocation == CARD_LOCATION_HAND){
        $previousPlayer = Players::get($previousPlayerId);
        Cards::drawCardsToHand($previousPlayer,1);
      }
    }
    else {
      //CREATE CARD (when type is not used)
      $card = Cards::createCustomerCardAsDelivered($player,$cardType );
    }
    $card->playDeliveryAbility($player);
    return $card;
  }
  
  public static function setupCustomersInRegions(array $cardTypes) : CustomerCard
  {
    $foundTypes = [];
    $cardIds = Cards::getIdsByTypes(CARD_TYPE_CUSTOMER,$cardTypes);
    if(count($cardIds) > 0){
      $cards = Cards::getMany($cardIds);
      foreach($cards as $cardId => $card){
        $foundTypes[] = $card->getType();
        $previousLocation = $card->getLocation();
        $previousPlayerId = $card->getPId();
        $region = $card->getRegion();
        $card->setLocation(CARD_LOCATION_MAP_REGION.$region);
        Notifications::placeCustomerOnRegion($card,$region);
        //replace card in previous location (player hands) + notify :
        if($previousLocation == CARD_LOCATION_HAND){
          $previousPlayer = Players::get($previousPlayerId);
          Cards::drawCardsToHand($previousPlayer,1);
        }
      }
    }

    foreach($cardTypes as $cardType){
      if(in_array($cardType, $foundTypes)) continue;
      $region = Cards::getCustomerRegionFromType($cardType);
      $card = Cards::createCustomerCardInRegion($cardType, $region );
    }
    return $card;
  }

  public static function createCustomerCardInRegion(int $cardType, int $region) : CustomerCard
  {
    $elt = [
      'type' => $cardType,
      'subtype' => CARD_TYPE_CUSTOMER,
      'location' => CARD_LOCATION_MAP_REGION.$region,
    ];
    $card = self::singleCreate($elt);
    Notifications::placeCustomerOnRegion($card,$region);
    return $card;
  }
  
  public static function reshuffleCustomersWithout1Type(int $removedCustomerType, int $nbCustomerTypes = 5)
  {
    Game::get()->trace("reshuffleCustomersWithout1Type($removedCustomerType,$nbCustomerTypes)");
    $customerTypes = Globals::getCustomerTypes();
    $typeAlreadyUsed = in_array($removedCustomerType,$customerTypes);
    if($typeAlreadyUsed){
      if(count($customerTypes) == ($nbCustomerTypes +1) ){
        //case 1 : Type was played with 5 other types => ok
      }
      else if(count($customerTypes) == $nbCustomerTypes ){
        //case 2 : Type was played with 4 other types => add 1 random DIFFeRENT
        $availableTypes = array_diff( ALL_CUSTOMER_TYPES, $customerTypes);
        $newType = array_rand(array_flip($availableTypes),1);
        Cards::createCardsByCustomer($newType);
        $customerTypes[] = $newType;
      }
      
      $customerKey = array_search($removedCustomerType, $customerTypes);
      unset($customerTypes[$customerKey]);
    }
    else {
      //case 3 : Type was NOT played with 6 other types => remove 1 random !
      $oldType = array_rand(array_flip($customerTypes),1);
      Cards::removeCardsByCustomer($oldType);
      $customerKey = array_search($oldType, $customerTypes);
      unset($customerTypes[$customerKey]);
    }
    
    Cards::shuffle(CARD_LOCATION_DECK);
    $customerTypes = array_values($customerTypes);
    Globals::setCustomerTypes($customerTypes);
    Notifications::initCustomersDeck($customerTypes);
    Cards::reshuffleDeck(null);

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
  public static function drawCardsToHand(Player $player,int $nbCards)
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
  
  public static function drawCardsToDeliver(Player $player,int $nbCards) : Collection 
  {
    Game::get()->trace("drawCardsToDeliver($nbCards)");
    $cards = self::pickForLocation($nbCards, CARD_LOCATION_DECK, CARD_LOCATION_DELIVERED,0,true);
    foreach($cards as $card){
      //$card->setPId($player->getId());
      //Notifications::deliver($player,$card);
      Game::get()->processDeliver($player,$card, null, true);
    }
    //Empty deck will be rare but not impossible
    $missingNb = $nbCards - $cards->count();
    Game::get()->trace("drawCardsToDeliver($nbCards) -> $missingNb are missing");
    if($missingNb>0) Notifications::missingCards($player,$missingNb);
    return $cards;
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

    //Discard from draft all other patrons of same clan because 2 players cannot have same clan
    $cards = Cards::getInLocation(CARD_CLAN_LOCATION_DRAFT)->filter(function ($c) use ($card) {
              return $card->getClan() == $c->getClan(); 
            });
    foreach($cards as $other){
      $other->setLocation(CARD_CLAN_LOCATION_DISCARD);
    }

    $card->abilityOnAssign($player);
  }

  public static function assignScenario(Player $player, ScenarioCard $card)
  {
    $card->setLocation(CARD_SCENARIO_LOCATION_ASSIGNED);
    $card->setPId($player->getId());
    Notifications::giveScenarioCard($player, $card);
    //Apply direct changes now or wait until all players colors are known in player setup ?
    $card->setupChangesBeforePlayerSetup();
  }
  
  /**
   * @param ScenarioType $type
   * @return ScenarioCard if assigned in this game
   */
  public static function getAssignedScenario(ScenarioType $type) : ScenarioCard |null
  {
    return self::DB()
      ->where('type', $type->value)
      ->where('subtype', CARD_TYPE_SCENARIO)
      ->where(self::$prefix.'location', CARD_SCENARIO_LOCATION_ASSIGNED)
      ->get()
      ->first();
  }
  
  public static function getAssignedScenarios() : Collection
  {
    return self::DB()
      ->where('subtype', CARD_TYPE_SCENARIO)
      ->where(self::$prefix.'location', CARD_SCENARIO_LOCATION_ASSIGNED)
      ->get();
  }
  
  public static function getScenario(Player $player) : ScenarioCard |null
  {
    return self::DB()->wherePlayer($player->getId())
      ->where('subtype', CARD_TYPE_SCENARIO)
      ->where(self::$prefix.'location', CARD_SCENARIO_LOCATION_ASSIGNED)
      ->get()
      ->first();
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

      if(Utils::isGameWithScenarios()){
        //LET Player choose between ALL CLAN PATRONS
        self::moveAllInLocation($deck,CARD_CLAN_LOCATION_DRAFT);

        $deckScenarios = CARD_SCENARIO_LOCATION_DECK.$clan_id;
        self::shuffle($deckScenarios);
        self::pickOneForLocation($deckScenarios, CARD_SCENARIO_LOCATION_DRAFT);
      }
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
  
    if(Utils::isGameWithScenarios()){
      $deckScenarios = self::getInLocation(CARD_SCENARIO_LOCATION_DECK.$player->getClan());
      foreach ($deckScenarios as $scenario) {
        $scenario->setLocation(CARD_SCENARIO_LOCATION_DRAFT);
        $scenario->setPId($player->getId());
      }
    }
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

    if(Utils::isGameWithScenarios()){
      foreach (self::getScenarioCardsTypes() as $type => $card) {
        $cards[] = [
          'location' => CARD_SCENARIO_LOCATION_DECK. $card['clan'],
          'type' => $type,
          'subtype' => CARD_TYPE_SCENARIO,
        ];
      }
    }

    self::create($cards);
    self::shuffle(CARD_LOCATION_DECK);
    
    AutomaCards::setupNewGame($players, $options);
    CityCards::setupNewGame($players, $options);
  }
 
  public static function getIdsByTypes(int $subType,array $cardsTypes) : array
  {
    return self::DB()->select([self::$prefix.'id'])
      ->where( 'subtype', $subType)
      ->whereIn( 'type', $cardsTypes)
      ->get()
      ->getIds();
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
  
  public static function createCardsByCustomer(int $customerType){
    $cards = [];
    foreach (Cards::getCustomerCardsTypes() as $type => $card) {
      if(!in_array($card['customerType'],[$customerType])) continue;
      $cards[] = [
        'location' => CARD_LOCATION_DECK,
        'type' => $type,
        'subtype' => CARD_TYPE_CUSTOMER,
      ];
    }
    Cards::create($cards);
  }
  
  public static function removeCardsByCustomer(int $customerType)
  {
    $cardTypes = Cards::getCardsTypesByCustomer($customerType);

    $cards = self::DB()
      ->where('subtype', CARD_TYPE_CUSTOMER)
      ->whereIn('type', $cardTypes)
      ->get();
    foreach($cards as $card){
      //NO Notif if used before cards are dealt
      self::DB()->delete($card->getId());
    }
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
  
  public static function getCustomerRegionFromType(int $type): int{
    $customerCardDatas = self::getCustomerCardsTypes()[$type];
    return $customerCardDatas['region'];
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
        'title' => Cards::getCustomerTypeName($t[0]),
        'desc' => '',
        'cost' => $t[2],
      ];
    };
    return [
      // 30 unique CUSTOMER cards
      CARD_ARTISAN_1 => $f([CUSTOMER_TYPE_ARTISAN, REGION_1,  [RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_ARTISAN_2 => $f([CUSTOMER_TYPE_ARTISAN, REGION_2,  [RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_ARTISAN_3 => $f([CUSTOMER_TYPE_ARTISAN, REGION_3,  [RESOURCE_TYPE_SILK=>2],]), 
      CARD_ARTISAN_4 => $f([CUSTOMER_TYPE_ARTISAN, REGION_4,  [RESOURCE_TYPE_SILK=>2],]), 
      CARD_ARTISAN_5 => $f([CUSTOMER_TYPE_ARTISAN, REGION_5,  [RESOURCE_TYPE_RICE=>2],]), 
      CARD_ARTISAN_6 => $f([CUSTOMER_TYPE_ARTISAN, REGION_6,  [RESOURCE_TYPE_RICE=>2],]), 
      CARD_ELDER_1 => $f([CUSTOMER_TYPE_ELDER, REGION_1 ,     [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_ELDER_2 => $f([CUSTOMER_TYPE_ELDER, REGION_2 ,     [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1,],]), 
      CARD_ELDER_3 => $f([CUSTOMER_TYPE_ELDER, REGION_3 ,     [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_ELDER_4 => $f([CUSTOMER_TYPE_ELDER, REGION_4 ,     [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1,],]), 
      CARD_ELDER_5 => $f([CUSTOMER_TYPE_ELDER, REGION_5 ,     [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_ELDER_6 => $f([CUSTOMER_TYPE_ELDER, REGION_6 ,     [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2,],]), 
      CARD_MERCHANT_1 => $f([CUSTOMER_TYPE_MERCHANT, REGION_1,[RESOURCE_TYPE_POTTERY=>3],]), 
      CARD_MERCHANT_2 => $f([CUSTOMER_TYPE_MERCHANT, REGION_2,[RESOURCE_TYPE_RICE=>3],]), 
      CARD_MERCHANT_3 => $f([CUSTOMER_TYPE_MERCHANT, REGION_3,[RESOURCE_TYPE_SILK=>3],]), 
      CARD_MERCHANT_4 => $f([CUSTOMER_TYPE_MERCHANT, REGION_4,[RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1],]), 
      CARD_MERCHANT_5 => $f([CUSTOMER_TYPE_MERCHANT, REGION_5,[RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MERCHANT_6 => $f([CUSTOMER_TYPE_MERCHANT, REGION_6,[RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2],]), 
      CARD_MONK_1 => $f([CUSTOMER_TYPE_MONK, REGION_1,        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MONK_2 => $f([CUSTOMER_TYPE_MONK, REGION_2,        [RESOURCE_TYPE_SILK=>3,RESOURCE_TYPE_RICE=>2],]), 
      CARD_MONK_3 => $f([CUSTOMER_TYPE_MONK, REGION_3,        [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MONK_4 => $f([CUSTOMER_TYPE_MONK, REGION_4,        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>3],]), 
      CARD_MONK_5 => $f([CUSTOMER_TYPE_MONK, REGION_5,        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_MONK_6 => $f([CUSTOMER_TYPE_MONK, REGION_6,        [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_1 => $f([CUSTOMER_TYPE_NOBLE, REGION_1,      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_2 => $f([CUSTOMER_TYPE_NOBLE, REGION_2,      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_3 => $f([CUSTOMER_TYPE_NOBLE, REGION_3,      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],]), 
      CARD_NOBLE_4 => $f([CUSTOMER_TYPE_NOBLE, REGION_4,      [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2],]), 
      CARD_NOBLE_5 => $f([CUSTOMER_TYPE_NOBLE, REGION_5,      [RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],]), 
      CARD_NOBLE_6 => $f([CUSTOMER_TYPE_NOBLE, REGION_6,      [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],]), 
      //V2 : 30 new cards
      31 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_1,    [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_RICE=>2,],]), 
      32 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_2,    [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_SILK=>2,],]), 
      33 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_3,    [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_RICE=>2,],]), 
      34 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_4,    [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_POTTERY=>2,],]), 
      35 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_5,    [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_POTTERY=>2,],]), 
      36 => $f([CUSTOMER_TYPE_MAGISTRATE, REGION_6,    [RESOURCE_TYPE_MONEY=>5, RESOURCE_TYPE_SILK=>2,],]), 
      CARD_SMUGGLER_1 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_1,  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>2,    ],]), 
      CARD_SMUGGLER_2 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_2,  [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_SMUGGLER_3 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_3,  [RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_SMUGGLER_4 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_4,  [RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      CARD_SMUGGLER_5 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_5,  [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      CARD_SMUGGLER_6 => $f([CUSTOMER_TYPE_SMUGGLER, REGION_6,  [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    ],]), 
      CARD_SHINDOSHI_1 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_1,   [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      CARD_SHINDOSHI_2 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_2,   [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_SHINDOSHI_3 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_3,   [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      CARD_SHINDOSHI_4 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_4,   [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_SHINDOSHI_5 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_5,   [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    ],]),
      CARD_SHINDOSHI_6 => $f([CUSTOMER_TYPE_SHINDOSHI, REGION_6,   [RESOURCE_TYPE_SUN=>1,    RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    ],]),
      49 => $f([CUSTOMER_TYPE_SPY, REGION_1,    [RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>3,                           ],]), 
      50 => $f([CUSTOMER_TYPE_SPY, REGION_2,    [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      51 => $f([CUSTOMER_TYPE_SPY, REGION_3,    [RESOURCE_TYPE_RICE=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      52 => $f([CUSTOMER_TYPE_SPY, REGION_4,    [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      53 => $f([CUSTOMER_TYPE_SPY, REGION_5,    [RESOURCE_TYPE_SILK=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      54 => $f([CUSTOMER_TYPE_SPY, REGION_6,    [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_TRADER_1 => $f([CUSTOMER_TYPE_TRADER, REGION_1,    [RESOURCE_TYPE_SILK=>2,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_TRADER_2 => $f([CUSTOMER_TYPE_TRADER, REGION_2,    [RESOURCE_TYPE_RICE=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      CARD_TRADER_3 => $f([CUSTOMER_TYPE_TRADER, REGION_3,    [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>1,    RESOURCE_TYPE_POTTERY=>2, ],]), 
      CARD_TRADER_4 => $f([CUSTOMER_TYPE_TRADER, REGION_4,    [RESOURCE_TYPE_SILK=>3,    RESOURCE_TYPE_POTTERY=>1,                           ],]), 
      CARD_TRADER_5 => $f([CUSTOMER_TYPE_TRADER, REGION_5,    [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_RICE=>2,    RESOURCE_TYPE_POTTERY=>1, ],]), 
      CARD_TRADER_6 => $f([CUSTOMER_TYPE_TRADER, REGION_6,    [RESOURCE_TYPE_SILK=>1,    RESOURCE_TYPE_POTTERY=>3,                           ],]), 
      
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
  
  /**
   * @return array of all the different types of Scenario Cards
   */
  public static function getScenarioCardsTypes()
  {
    $f = function ($t) {
      return [
        'clan' => $t[0],
        'difficulty' => $t[1],
        'name' => $t[2],
      ];
    };
    return [
      // 8 unique (only 1/clan for now )
      ScenarioType::CRAB_1    ->value  => $f([CLAN_CRAB    , 2, '', ]), 
      ScenarioType::MANTIS_1  ->value  => $f([CLAN_MANTIS  , 2, '', ]), 
      ScenarioType::CRANE_1   ->value  => $f([CLAN_CRANE   , 4, '', ]), 
      ScenarioType::SCORPION_1->value  => $f([CLAN_SCORPION, 3, '', ]), 
      ScenarioType::PHOENIX_1 ->value  => $f([CLAN_PHOENIX , 4, '', ]), 
      ScenarioType::LION_1    ->value  => $f([CLAN_LION    , 4, '', ]),
      ScenarioType::DRAGON_1  ->value  => $f([CLAN_DRAGON  , 1, '', ]),  
      ScenarioType::UNICORN_1 ->value  => $f([CLAN_UNICORN , 3, '', ]),  

    ];
  }
}
