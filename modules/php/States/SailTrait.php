<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\Meeple;
use ROG\Models\BuildingTile;
use ROG\Models\CustomerCard;

trait SailTrait
{
   
  public function argSail()
  { 
    $activePlayer = Players::getActive();
    $possibleSpaces = $this->listPossibleSpacesToSail($activePlayer);
    $args = [
      'spaces' => $possibleSpaces,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $shipId
   * @param int $riverSpace
   */
  public function actSailSelect($shipId,$riverSpace)
  { 
    self::checkAction('actSailSelect'); 
    self::trace("actSailSelect($shipId,$riverSpace)");

    $player = Players::getCurrent();
    $this->addStep();

    $possibleSpaces = $this->listPossibleSpacesToSail($player);
    $possibleShips = array_keys($possibleSpaces);
    if(!in_array($shipId, $possibleShips)){
      throw new UnexpectedException(20,"You cannot Sail ship $shipId, see : ".json_encode($possibleShips));
    } 
    $possibleShipsDest = $possibleSpaces[$shipId];
    if(!in_array($riverSpace, $possibleShipsDest)){
      throw new UnexpectedException(21,"You cannot Sail to $riverSpace, see : ".json_encode($possibleShipsDest));
    } 
    $ship = Meeples::get($shipId);
    $fromPosition = $ship->getPosition();
    $ship->setPosition($riverSpace);
    Notifications::sail($player,$ship,$riverSpace);
    if($riverSpace < $fromPosition){
      $this->completeJourney($player,$ship);
    }

    $adjacentSpaces = ShoreSpaces::getAdjacentSpaces($riverSpace);
    self::trace("actSailSelect($shipId,$riverSpace) adjacent spaces :".json_encode($adjacentSpaces));

    $players = Players::getAll();

    Notifications::checkVisitorRewards();
    $nbEmptySpaces = 0;
    foreach($adjacentSpaces as $adjacentSpace){
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(!isset($tile)){
        $nbEmptySpaces++;
        Players::giveMoney($player,EMPTY_SPACE_REWARD);
      }
      else {
        $region = $tile->getRegion();

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
            $reward->rewardPlayer($player,$region);
          }
        }
      }
    }
    
    Notifications::checkOwnerRewards();
    $ownBuilding = false;
    $opponentBuilding = false;
    foreach($adjacentSpaces as $adjacentSpace){
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(isset($tile)){
        $clanMarkers = $tile->getMeeples();
        //Owner rewards : sometimes 2 owners (or 2 times the same)
        foreach($tile->ownerReward->entries as $reward){
          foreach($clanMarkers as $clanMarker){
            $owner = $players[$clanMarker->getPId()];
            $reward->rewardPlayer($owner,$region);
            $ownBuilding = $ownBuilding || $clanMarker->getPId() == $player->getId();
            $opponentBuilding = $opponentBuilding || $clanMarker->getPId() != $player->getId();
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
    
    $playerPatron = $player->getPatron();
    if(isset($playerPatron)){
      $playerPatron->scoreWhenSail($player,$ownBuilding,$opponentBuilding);
      $playerPatron->addBonuses($player);
    }

    Players::claimMasteries($player);
    
    Stats::inc("nbActionsSail", $player->getId());

    if($this->goToBonusStepIfNeeded($player)) return;
    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array  ['shipId' => ['positions'],'shipId' => ['positions']]
   */
  public function listPossibleSpacesToSail($player)
  { 
    $nbMoves = $player->getDie();
    $possibleSpaces = [];
    $boats = Meeples::getBoats($player->getId());
    foreach($boats as $boat){
      //ship position is between 1 and NB_RIVER_SPACES, and comes back at 1 after completing the journey
      $diePosition = $boat->getPosition() + $nbMoves -1;
      $possibleSpaces[$boat->getId()][] = $diePosition % NB_RIVER_SPACES +1;
      if(MEEPLE_TYPE_SHIP_ROYAL == $boat->getType()){
        //Royal Ship: +1/-1 space
        if(Cards::hasPlayerDeliveredOrder($player->getId(), CARD_NOBLE_5)){
          $possibleSpaces[$boat->getId()][] = ($diePosition +1) % NB_RIVER_SPACES +1;
          $possibleSpaces[$boat->getId()][] = ($diePosition -1) % NB_RIVER_SPACES +1;
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
  public function completeJourney($player,$ship)
  {
    Notifications::reachRiverEnd($player,$ship);
    Globals::addBonus($player,BONUS_TYPE_MONEY_OR_GOOD);

    Tiles::removeLastInBuildingRow();

    foreach(MERCHANT_TYPES as $merchantType){
      if(Cards::hasPlayerDeliveredOrder($player->getId(),$merchantType)){
        CustomerCard::playOngoingMerchantAbility($player,$merchantType);
      }
    }
  }

}
