<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\AutomaPlayer;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Player;
use ROG\Models\ScenarioType;
use ROG\Models\SHORE_SIDE;
use ROG\Models\ShoreSpace;

trait BuildTrait
{
   
  public function argBuild()
  { 
    self::trace("argBuild().. ");
    $activePlayer = Players::getActive();
    $possibleSpaces = $this->listPossibleSpacesToBuild($activePlayer);
    $possibleTiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_ROW)->getIds();
    $markerForEraTiles = null;
    $shindoshi3 = Utils::getShindoshiMarker($activePlayer->getId(),CARD_SHINDOSHI_3);
    if(isset($shindoshi3)){
      self::trace("argBuild().. Shindoshi 3 may be used");
      $markerForEraTiles = $shindoshi3->getId();
      $nextEra1Card = Tiles::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1);
      $nextEra2Card = Tiles::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2);
      if(isset($nextEra1Card)) $possibleTiles[] = $nextEra1Card->getId();
      if(isset($nextEra2Card)) $possibleTiles[] = $nextEra2Card->getId();
    }
    $args = [
      'spaces' => $possibleSpaces,
      'tiles' => $possibleTiles,
      'markerForEraTiles' => $markerForEraTiles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $position
   * @param int $tileId
   */
  #[PossibleAction]
  public function actBuildSelect(
    #[IntParam(name: 'p')] int $position,
    #[IntParam(name: 't')] int $tileId,
    int $version,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actBuildSelect'); 
    self::trace("actBuildSelect($position,$tileId)");

    $player = Players::getCurrent();
    $pId = $player->id;
    $this->addStep();

    $args = $this->argBuild();
    $possibleSpaces = $args['spaces'];
    $possibleSpacesIds = $possibleSpaces->map(function($space) {return $space->id;})->toArray();
    if(!in_array($position, $possibleSpacesIds)){
      throw new UnexpectedException(10,"You cannot build on $position, see ids: ".json_encode($possibleSpacesIds));
    }
    $shoreSpace = ShoreSpaces::getShoreSpace($position); 
    $tile = Tiles::get($tileId);
    $possibleTiles = $args['tiles'];
    if(!in_array($tileId,$possibleTiles)){
      throw new UnexpectedException(12,"You cannot build tile $tileId, see ids: ".json_encode($possibleTiles));
    }
    $previousLocation = $tile->getLocation();
    if(in_array($previousLocation, [TILE_LOCATION_BUILDING_DECK_ERA_1,TILE_LOCATION_BUILDING_DECK_ERA_2] )){
      $markerForEraTiles = $args['markerForEraTiles'];
      if(isset($markerForEraTiles)){
        Meeples::removeClanMarkerById($player,$markerForEraTiles);
        $player->giveResource(NB_FAVOR_WITH_SHINDOSHI_3,RESOURCE_TYPE_SUN);
      }
    }
    $previousPosition = $tile->getPosition();
    $playerPatron = $player->getPatron();

    $cost = $this->buildingCost($player,$shoreSpace);
    Players::spendMoney($player,$cost);

    if(isset($playerPatron)){
      Meeples::removePlayerMarkersOnShoreSpace($position,$player,$tile,$playerPatron);
    }
    Meeples::removeResourcesOnShoreSpace($shoreSpace,$player,);

    $tile->setLocation(TILE_LOCATION_BUILDING_SHORE);
    $tile->setPosition($position);

    $adjacentRiverSpaces = ShoreSpaces::getUniqueAdjacentRiverSpaces([$position]);

    Notifications::build($player,$tile,$previousPosition,$previousLocation);
    Stats::inc("nbActionsBuild", $player->getId());

    if(BUILDING_ROW_END == $previousPosition){
      Players::gainDivineFavor($player,BUILDING_ROW_END_FAVOR);
    }

    Meeples::addClanMarkerOnShoreSpace($tile,$player);
    Globals::setLastBuiltTile($tileId);
    Globals::setLastBuiltLocationOrigin($previousLocation);
    Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);
    
    Utils::playTradersAbilities($player);
    
    if(isset($playerPatron)){
      $playerPatron->scoreWhenBuild($player,$shoreSpace);
      $playerPatron->addBonuses($player);
    }

    Players::gainInfluence($player,$shoreSpace->region,$tile->getBonus());
    Utils::moveRogueShipFrom($player,$adjacentRiverSpaces);
    Players::claimMasteries($player);
    
    if($this->goToBonusStepIfNeeded($player)) return;
    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return Collection of ShoreSpace
   */
  public function listPossibleSpacesToBuild(Player $player) : Collection
  { 
    $playerPatron = $player->getPatron();
    $region = $player->getDie();
    $possibleSpaces = new Collection();
    $emptySpaces = ShoreSpaces::getEmptySpaces($region);
    
    // master engineer can build in every regions !
    if(isset($playerPatron) && PATRON_MASTER_ENGINEER == $playerPatron->getType()){
      foreach (REGIONS as $otherRegion){
        if($otherRegion == $region) continue;
        $emptySpaces = array_merge($emptySpaces,ShoreSpaces::getEmptySpaces($otherRegion));
      }
    }

    $scenarioToBuildOnLeft = Cards::getAssignedScenario(ScenarioType::CRAB_1);
    if(isset($scenarioToBuildOnLeft)){
      if($scenarioToBuildOnLeft->getPId() == $player->getId()){
        //CAN ONLY BUILD ON LEFT SIDE
        $emptySpaces = ShoreSpaces::filterBySide($emptySpaces,SHORE_SIDE::LEFT);
      }
      else if($player instanceof AutomaPlayer){
        //CAN ONLY BUILD ON RIGHT SIDE
        $emptySpaces = ShoreSpaces::filterBySide($emptySpaces,SHORE_SIDE::RIGHT);
      } 
      //ELSE OTHER PLAYERS don't have conditions
    } 

    foreach($emptySpaces as $key => $spaceId){
      $space = ShoreSpaces::getShoreSpace($spaceId);
      if($this->canBuildOnSpace($player,$space)){
        //update cost for UI
        $space->cost = $this->buildingCost($player,$space);
        $possibleSpaces->append($space);
      }
    }
    return $possibleSpaces;
  }
  
  /**
   * @param Player $player
   * @param ShoreSpace $space
   * @return bool 
   */
  public function canBuildOnSpace(Player $player,ShoreSpace $space)
   : bool
  { 
    $cost = $this->buildingCost($player,$space);
    if($cost > $player->getMoney() && !($player instanceof AutomaPlayer)) {
      return false;
    }
    $meeples = Meeples::getInLocation(MEEPLE_LOCATION_SHORE."{$space->id}");
    foreach($meeples as $meeple){
      if(isset($meeple)){
        $meepleOwner = $meeple->getPId();
        if(isset($meepleOwner) && ($meepleOwner != $player->getId())) return false;
      }
    }

    return true;
  }

  /**
   * @param Player $player
   * @param ShoreSpace $space
   * @return int
   */
  public function buildingCost(Player $player,ShoreSpace $space)
    : int
  { 
    $cost = $space->cost;
    $region = $space->region;
    $artisanMarker = Meeples::getMarkerOnArtisanSpace($player->getId(),$region);
    if(isset($artisanMarker)){
      $cost = max(0, $cost - ARTISAN_COST_REDUCTION);
    }
    return $cost;
  }
}
