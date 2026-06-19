<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

/**
 * ScenarioCard: all utility functions concerning a Scenario Card
 */

class ScenarioCard extends Card
{ 
  
  protected $staticAttributes = [
    ['clan', 'int'],
    ['name', 'string'],
    ['difficulty', 'int'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 

  public function getUiData()
  {
    $data = parent::getUiData();
    unset($data['state']);
    $data['subtype'] = CARD_TYPE_SCENARIO;
    $data['resources'] = $this->getResources();
    $data['played'] = $this->isPlayed();
    return $data;
  }

  /**
   * @return string
   */
  public function getClanName() : string
  {
    return Utils::getClanName($this->getClan());
  } 

  public function setupChangesBeforePlayerSetup()
  {
    switch($this->getType()){//ScenarioType value
      case ScenarioType::CRAB_1->value:
        Tiles::discardStartingBuildings();
        break;
      case ScenarioType::PHOENIX_1->value:
        //RULE : Shuffle all masteries (2player side) into 1 faceup deck.
        Tiles::moveAllInLocation(TILE_LOCATION_MASTERY_CARD,TILE_LOCATION_MASTERY_DECK);
        $masteries = Tiles::getInLocation(TILE_LOCATION_MASTERY_DECK);
        foreach($masteries as $tile){
          //Change tile for 2 player side :
          $oldType = $tile->getType();
          $newType = Tiles::get2PlayerSideMasteryCardType($oldType);
          $tile->setType($newType);
        }
        Tiles::shuffle(TILE_LOCATION_MASTERY_DECK);
        $masteryDeckSize = Tiles::countInLocation(TILE_LOCATION_MASTERY_DECK);
        $mastery = Tiles::pickOneForLocation(TILE_LOCATION_MASTERY_DECK,TILE_LOCATION_MASTERY_CARD);
        Notifications::masteryDeck($masteryDeckSize,$mastery,);
        break;
    }
  }
  
  /**
   * @return bool true if boats are setup by this scenario card
   */
  public function setupCustomBoats(Player &$player,) : bool
  {
    switch($this->getType()){//ScenarioType value
      case ScenarioType::CRANE_1->value:
        //start with 1 ship on the top river starting space
        Meeples::addBoatOnRiverSpace($player,1);
        //Place your second ship and a debt pile of 30 on this scenario card. 
        Players::spendMoney($player,$player->getMoney());
        $ship = Meeples::addBoatOnCard($player,$this,null);
        Notifications::newBoatOnScenarioCard($player,$ship, $this);
        $this->addResource($player, CRANE_DEBT_SIZE, RESOURCE_TYPE_MONEY);
        return true;
    }
    return false;
  }

  public function setupChangesAfterPlayerSetup(Player &$player, Collection $players)
  {
    switch($this->getType()){
      case ScenarioType::MANTIS_1->value:
        //Place a royal ship of an unused player color on the bottom river space.
        $clanToPick = Utils::pickNextAvailableClan($players);
        Globals::setRogueClan($clanToPick['clan']);
        $roguePlayer = Players::roguePlayer();
        Notifications::roguePlayer($roguePlayer);
        $boatPosition = ShoreSpaces::getLastRiverSpace();
        $boat = Meeples::addRoyalShipOnRiverSpace($roguePlayer, $boatPosition,false);
        Notifications::newBoat($player,$boat);
        break;
      case ScenarioType::SCORPION_1->value:
        //Roll a die and add 2 : place a clan marker of an unused clan on each region influence track
        $clanToPick = Utils::pickNextAvailableClan($players);
        Globals::setScorpionEnemy($clanToPick['clan']);
        $virtualPlayer = Players::scorpionEnemy();
        Notifications::virtualPlayer($virtualPlayer);
          
        $influenceMeeples = [];
        foreach (REGIONS as $region){
          $randomPosition = Utils::randomDieFace() + 2;
          $meeple = Meeples::addClanMarkerOnInfluence($virtualPlayer, $region,false);
          $meeple->setPosition($randomPosition);
          $influenceMeeples[] = $meeple;
        }
        Notifications::influenceClanMarkers($virtualPlayer,$influenceMeeples);

        //Start with the Noble from Region 3 in play and gain its rewards. It counts as a customer you delivered to.
        Cards::setupDeliveredCustomer($player,CARD_NOBLE_3 );
        break;
      case ScenarioType::LION_1->value:
        $clanToPick = Utils::pickNextAvailableClan($players);
        Globals::setLionEnemy($clanToPick['clan']);
        $virtualPlayer = Players::lionEnemy();
        Notifications::virtualPlayer($virtualPlayer);
        break;
    }
  }
  
  /**
   * @return array $possibleActions : list of actions to play during the turn
   */
  public function listPossibleActions(Player $player,) : array
  {
    $possibleActions = [];
    switch($this->getType()){
      case ScenarioType::PHOENIX_1->value:
        if( !$this->isPlayed()
          && ($player->canSpendResource(RESOURCE_TYPE_SUN,1)) 
          && ( Tiles::countMasteriesInDeck() > 1) 
        ){
          $possibleActions[TURN_ACTION::DIVINE_CYCLING->value] = [];
        }
        break;
    }
    return $possibleActions;
  }

  /**
   * @return bool true if this player has no restriction to deliver cards in region $region
   */
  public function canDeliver(Player $player, int $region) : bool
  {
    $canDeliver = true;
    switch($this->getType()){
      case ScenarioType::SCORPION_1->value:
        //Distrusted: You cannot deliver to customers from regions with a target still present.
        $target = Meeples::getInfluenceMarker(SCORPION_ENEMY_ID,$region);
        $canDeliver = !isset($target);
        break;
    }
    return $canDeliver;
  }

  function abilityOnAutomaBuild(AutomaPlayer $player, BuildingTile $tile, ShoreSpace $shoreSpace){
    switch($this->getType()){
      case ScenarioType::LION_1->value:
        //When Seishin builds, place 1 assassin next to that building.
        $lionEnemy = Players::lionEnemy();
        Meeples::placeAssassinNearShoreSpace($lionEnemy, $shoreSpace->id);
        break;
    }
  }

  function abilityOnGainInfluence(Player $player,int $region,int $fromInfluence, int $toInfluence){
    switch($this->getType()){
      case ScenarioType::SCORPION_1->value:
        // Eliminating Targets: When you gain or lose influence, if your clan marker lands directly on top of a target, remove that target from the game. 
        $target = Meeples::getInfluenceMarker(SCORPION_ENEMY_ID,$region);
        if(isset($target) && $target->getPosition() == $toInfluence){
          Meeples::removeTarget($player, $target);
        }
        break;
    }
  }

  public function abilityOnCompleteJourney(Player &$player,)
  {
    switch($this->getType()){//ScenarioType value
      case ScenarioType::CRANE_1->value:
        $debt = $this->getResource(RESOURCE_TYPE_MONEY);
        if($debt > 0){
          Globals::addBonusWithDatas($player,BONUS_TYPE_MANAGE_DEBT,['card_id'=>$this->getId(), 'bonusQuantity'=>1, ],clienttranslate('Manage debt'));
        }
        break;
    }
  }
  
  public function abilityOnManageResources(Player &$player, int $qty, )
  {
    switch($this->getType()){//ScenarioType value
      case ScenarioType::CRANE_1->value:
        $type = RESOURCE_TYPE_MONEY;
        $debtBefore = $this->getResource($type);
        
        //Remove money from debt card by PAYING from player money
        $this->addResource($player,-$qty,$type);
        $player->giveResource(-$qty,$type);

        $debt = $this->getResource($type);
        if($qty > 0 && $debt <= 15){
          //DEBT 15 or less : Gain your second ship (if not yet placed). Place it on the middle river starting space.
          $ship = Meeples::getBoatOnCard($this);
          if(isset($ship)){
            $ship->setLocation(MEEPLE_LOCATION_RIVER);
            $ship->setPosition(STARTING_BOATS_SPACES[1]);
            Notifications::newBoat($player,$ship);
          }
        }

        if($qty > 0 && $debt <= 0){
          //DEBT 0 FIRST TIME : Gain 2 influence in each region, 
          Notifications::removeDebt($player,$this);
          foreach(REGIONS as $region){
            Players::gainInfluence($player, $region, 2);
          }
          // then gain all influence track rewards you have reached this game again.
          foreach(REGIONS as $region){
            $meeple = Meeples::getInfluenceMarker($player->getId(),$region);
            Players::gainInfluenceTrackRewards($player,$region, 0, $meeple->getPosition() );
          }
        }
        
        //Interest: For each 5 of debt remaining on this card, add 1 to your debt
        $interests = intval($debt / 5);
        $this->addResource($player,$interests,RESOURCE_TYPE_MONEY);
        break;
    }
  }

  
  public function beforeScoring(Player $player)
  {
    switch($this->getType()){
      case ScenarioType::SCORPION_1->value:
        //Remove your clan marker from each region influence track with a target still present. You do not score points for influence in these regions
        foreach(REGIONS as $region){
          $target = Meeples::getInfluenceMarker(SCORPION_ENEMY_ID,$region);
          if(isset($target)){
            Meeples::removeInfluenceClanMarker($player,$region);
          }
        }
        break;
    }
  }

  /**
   * @return bool true if scenario is completed
   */
  public function checkEndConditions() : bool
  {
    $checked = false;

    switch($this->getType()){
      case ScenarioType::CRAB_1->value:
        $nbPlayerBuildings = Meeples::countPlayerBuildings($this->getPId());
        $nbSeishinBuildings = Meeples::countPlayerBuildings(AUTOMA_PLAYER_ID);
        $checked = $nbSeishinBuildings < $nbPlayerBuildings;
        break;
      case ScenarioType::MANTIS_1->value:
        $ship = Meeples::getRogueShip();
        $checked = (!isset($ship));
        break;
      case ScenarioType::CRANE_1->value:
        $debt = $this->getResource(RESOURCE_TYPE_MONEY);
        $checked = ($debt <= 0);
        break;
      case ScenarioType::SCORPION_1->value:
        $checked = true;
        break;
      case ScenarioType::PHOENIX_1->value:
        $playerMasteries = Meeples::countPlayerMasteries($this->getPId());
        $seishinMasteries = Meeples::countPlayerMasteries(AUTOMA_PLAYER_ID);
        $checked = ($playerMasteries > $seishinMasteries);
        break;
    }
    return $checked;
  }

  
  public function formatNameForNotif() : array
  {
    return [
        'log'=> '${clan_icon}${clan_name} Scenario',
        'args'=> [
          'clan_id' => $this->getClan(),
          'clan_icon' => '',
          'clan_name' => $this->getClanName(),
          'i18n' => ['clan_name'],
          'preserve' => ['clan_id'],
        ]
      ];
  }
}
