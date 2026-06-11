<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\AutomaPlayer;
use ROG\Models\Meeple;
use ROG\Models\BuildingTile;
use ROG\Models\CustomerCard;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Player;
use ROG\Models\ScenarioType;

trait SailTrait
{
   
  public function argSail()
  { 
    $activePlayer = Players::getActive();
    $possibleSpaces = $this->listPossibleSpacesToSail($activePlayer);
    $canSkipOwnerMarkerId = null;
    $shindoshi6 = Utils::getShindoshiMarker($activePlayer->getId(),CARD_SHINDOSHI_6);
    if(isset($shindoshi6)){
      $canSkipOwnerMarkerId = $shindoshi6->getId();
    }
    $args = [
      'spaces' => $possibleSpaces,
      'canSkipOwner' => $canSkipOwnerMarkerId,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $shipId
   * @param int $riverSpace
   * @param bool $skipOwner (Default false)
   */
  #[PossibleAction]
  public function actSailSelect(
    #[IntParam(name: 's')] int $shipId,
    #[IntParam(name: 'r')] int $riverSpace,
    int $version,
    ?bool $skipOwner = false,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actSailSelect'); 
    self::trace("actSailSelect($shipId,$riverSpace,$skipOwner)");

    $player = Players::getCurrent();
    $this->addStep();

    $args = $this->argSail();
    $possibleSpaces = $args['spaces'];
    $possibleShips = array_keys($possibleSpaces);
    if(!in_array($shipId, $possibleShips)){
      throw new UnexpectedException(20,"You cannot Sail ship $shipId, see : ".json_encode($possibleShips));
    } 
    $possibleShipsDest = $possibleSpaces[$shipId];
    $upriver = false;
    $upspaces = [];
    $markerId = null;
    $skipOwnerMarkerId = null;
    if(isset($possibleShipsDest['upriver']) ){
      $upspaces = $possibleShipsDest['upriver'];
    }
    if(!in_array($riverSpace, $possibleShipsDest)){
      foreach($upspaces as $upspace){
        if($riverSpace == $upspace['space']){
          $markerId = $upspace['markerId'];
          $upriver = true;
          break;
        }
      }
      if(!isset($markerId)){
        throw new UnexpectedException(21,"You cannot Sail to $riverSpace, see : ".json_encode($possibleShipsDest));
      }
    } 
    if($skipOwner){
      if(!isset($args['canSkipOwner'])){
        throw new UnexpectedException(22,"You cannot skip owner rewards now ");
      }
      $skipOwnerMarkerId = $args['canSkipOwner'];
    }

    $ship = Meeples::get($shipId);

    $this->process_Sail($player,$ship,$riverSpace, $upriver, $markerId, $skipOwnerMarkerId);
    
    Utils::playTradersAbilities($player);

    Players::claimMasteries($player);
    
    Stats::inc("nbActionsSail", $player->getId());

    if($this->goToBonusStepIfNeeded($player)) return;
    $this->gamestate->nextState('next');
  }

  public function process_Sail(
    Player $player, 
    Meeple $ship, 
    int $riverSpace, 
    bool $upriver = false, 
    int|null $markerId  = null, 
    int|null $skipOwnerMarkerId  = null, 
  ){
    $shipId = $ship->getId();
    self::trace("process_Sail($shipId,$riverSpace)");
    $fromPosition = $ship->getPosition();
    $ship->setPosition($riverSpace);
    Globals::setLastSailedShip($shipId);
    Globals::setTurnMainActionDone(MAIN_ACTION::SAIL->value);
    Notifications::sail($player,$ship,$riverSpace);
    if($riverSpace < $fromPosition){
      if($upriver){
        Meeples::removeClanMarkerById($player,$markerId);
      }
      else {
        $this->completeJourney($player,$ship);
      }
    }

    $adjacentSpaces = ShoreSpaces::getAdjacentSpaces($riverSpace);
    Game::get()->trace("process_Sail($shipId,$riverSpace) adjacent spaces :".json_encode($adjacentSpaces));

    $players = Players::getAllWithAutoma();
    $playerPatron = $player->getPatron();
    $playerScenario = $player->getScenario();

    Notifications::checkVisitorRewards();
    $nbEmptySpaces = 0;
    foreach($adjacentSpaces as $adjacentSpace){
      $shoreSpace = ShoreSpaces::getShoreSpace($adjacentSpace);
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(!isset($tile)){
        $nbEmptySpaces++;
        Players::giveMoney($player,EMPTY_SPACE_REWARD,$adjacentSpace);
      }
      else {
        $region = $tile->getRegion();
        $clanMarkers = $tile->getMeeples();
        $buildingsOwners = $clanMarkers->map(function(Meeple $meeple){return $meeple->getPId();})->toArray();
        Game::get()->trace("process_Sail($shipId,$riverSpace) building owners are :".json_encode($buildingsOwners));

        if($shoreSpace->type == SHORE_SPACE_IMPERIAL_MARKET){
          if(isset($playerPatron)){
            $playerPatron->abilityOnVisitImperialMarket($player,$region);
          }
        }

        if(isset($playerScenario) ){
          if(ScenarioType::CRAB_1->value == $playerScenario->getType() 
            && in_array(AUTOMA_PLAYER_ID,$buildingsOwners)
          ){
            //Scenario RULE : When Crab sails, Crab does not receive visitor rewards from buildings owned by Seishin. (Even if it’s a building that Crab and Seishin both own together by both having clan markers on it).
            continue;
          }
        }

        //Visitor rewards
        $rewards = $tile->visitorReward;
        foreach($rewards->entries as $reward){
          //noble 1 Ongoing Ability :if market AND royal ship: replace reward resource type by a choice
          if(MEEPLE_TYPE_SHIP_ROYAL == $ship->getType() 
            && BUILDING_TYPE_MARKET == $tile->getBuildingType() 
            && in_array($reward->type,[RESOURCE_TYPE_SILK,RESOURCE_TYPE_POTTERY,RESOURCE_TYPE_RICE])
            && Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_1)
          ){
            Globals::addBonus($player,BONUS_TYPE_CHOICE);
          }
          else {
            $reward->rewardPlayer($player,$region,$tile);
          }
        }
      }
    }
    //SAVE UPDATED PLAYER datas
    $players[$player->getId()] = $player;
    
    Notifications::checkOwnerRewards();
    if(isset($skipOwnerMarkerId)){
      Meeples::removeClanMarkerById($player,$skipOwnerMarkerId);
      Notifications::skipOpponentOwnerRewards($player);
    }
    $ownBuilding = false;
    $opponentBuilding = false;
    foreach($adjacentSpaces as $adjacentSpace){
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(isset($tile)){
        $clanMarkers = $tile->getMeeples();
        $region = $tile->getRegion();
        //Owner rewards : sometimes 2 owners (or 2 times the same)
        foreach($tile->ownerReward->entries as $reward){
          foreach($clanMarkers as $clanMarker){
            $owner = $players[$clanMarker->getPId()];
            $ownBuilding = $ownBuilding || $clanMarker->getPId() == $player->getId();
            $opponentBuilding = $opponentBuilding || $clanMarker->getPId() != $player->getId();
            if($clanMarker->getPId() != $player->getId() && isset($skipOwnerMarkerId)){
              //Skip opponent rewards
              continue;
            }
            $ownerScenario = $owner->getScenario();
            if( isset($ownerScenario) ){
              if(ScenarioType::CRAB_1->value == $ownerScenario->getType() 
                && $player instanceof AutomaPlayer
              ){
                //Scenario RULE : When Seishin sails: Crab with Scenario does not receive owner rewards (even if it’s a building that Crab and Seishin both own together by both having clan markers on it).
                continue;
              }
            }

            $reward->rewardPlayer($owner,$region,$tile);
            //SAVE UPDATED PLAYER datas
            $players[$clanMarker->getPId()] = $owner;
          }
        }
      }
    }

    if(MEEPLE_TYPE_SHIP_ROYAL == $ship->getType()){
      Notifications::checkRoyalShipAbilities();
      //noble 2 Ongoing Ability : +1 coin for empty space
      if(Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_2)){
        Players::giveMoney($player,EMPTY_SPACE_REWARD * $nbEmptySpaces);
      }
      //noble 3 Ongoing Ability : gain influence in adjacent regions
      if(Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_3)){
        $regions = ShoreSpaces::getAdjacentRegions($riverSpace);
        foreach($regions as $regionNear){
          Players::gainInfluence($player,$regionNear,NB_INLUENCE_NOBLE_3);
        }
      }
      //noble 4 Ongoing Ability : +1 point IF 1 or more opponent buildings
      if($opponentBuilding && Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_4)){
        $player->addPoints(NB_POINTS_NOBLE_4);
      }
      //noble 6 Ongoing Ability : +1 point IF 1 or more owned buildings
      if($ownBuilding && Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_6)){
        $player->addPoints(NB_POINTS_NOBLE_6);
      }
    }
    
    if(isset($playerPatron)){
      $playerPatron->scoreWhenSail($player,$ownBuilding,$opponentBuilding,$ship);
      $playerPatron->addBonuses($player);
    }

  } 

  /**
   * @param Player $player
   * @return array  ['shipId' => ['positions'],'shipId' => ['positions']]
   */
  public function listPossibleSpacesToSail($player)
  { 
    $nbMoves = $player->getDie();
    $nbDieFaces = count(DIE_FACES);
    $indexDieFace = array_search($nbMoves,DIE_FACES);
    $possibleSpaces = [];
    $boats = Meeples::getBoats($player->getId());
    $upriver = false;
    $shindoshi1 = Utils::getShindoshiMarker($player->getId(),CARD_SHINDOSHI_1);
     if(isset($shindoshi1)){
      //we may go upriver
      $upriver = true;
      $markerId = $shindoshi1->getId();
    }
    foreach($boats as $boat){
      
      //Basically 1 face only
      $playableDieFaces = [$nbMoves];

      if(MEEPLE_TYPE_SHIP_ROYAL == $boat->getType()){
        if(Cards::hasPlayerDeliveredOrder($player->getId(), CARD_NOBLE_5)){
          //Royal Ship: +1/-1 space -> wrong ! it is +1/-1 DIE FACE (so there is a difference for 1 and 6)
          $playableDieFaces[] = DIE_FACES[Utils::positive_modulo($indexDieFace +1, $nbDieFaces)];
          $playableDieFaces[] = DIE_FACES[Utils::positive_modulo($indexDieFace -1, $nbDieFaces)];
        }
      }
      foreach($playableDieFaces as $playableDieFace){
        //ship position is between 1 and NB_RIVER_SPACES, and comes back at 1 after completing the journey
        $diePosition = $boat->getPosition() + $playableDieFace -1;
        $possibleSpaces[$boat->getId()][] = $diePosition % NB_RIVER_SPACES +1;

        $diePositionUpRiver = $boat->getPosition() - $playableDieFace -1;
        if($upriver && $diePositionUpRiver >= 0){
          //we may go upriver but not farther than space 1
          $possibleSpaces[$boat->getId()]['upriver'][] = [
            'space' => $diePositionUpRiver % NB_RIVER_SPACES +1,
            'markerId' => $markerId,
          ];
        }
      }

    }
    return $possibleSpaces;
  }

  
  /**
   * completing the journey - add bonuses to select
   * @param Player $player
   * @param Meeple $ship
   */
  public function completeJourney(Player &$player,Meeple $ship)
  {
    Notifications::reachRiverEnd($player,$ship);
    if($player instanceof AutomaPlayer){
      $player->addPoints(NB_POINTS_FOR_AUTOMA_COMPLETE_JOURNEY);
    }
    else {
      Globals::addBonus($player,BONUS_TYPE_MONEY_OR_GOOD);
    }

    Tiles::removeLastInBuildingRow();

    foreach(MERCHANT_TYPES as $merchantType){
      if(Cards::hasPlayerDeliveredOrder($player->getId(),$merchantType)){
        CustomerCard::playOngoingAbility($player,$merchantType);
      }
    }
  }

}
