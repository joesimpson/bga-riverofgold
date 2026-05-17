<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Utils;
use ROG\Models\ClanPatronCard;
use ROG\Models\MasteryCard;
use ROG\Models\Player;
use ROG\Models\Tile;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \ROG\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    return new \ROG\Models\Player($row);
  }

  /**
   * @param array $players
   * @return Collection $players
   */
  public static function setupNewGame($players, $options)
  {
    // Create players
    $gameInfos = Game::get()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    shuffle($colors);//Shuffle for cases where color matters
    $query = self::DB()->multipleInsert(['player_id', 'player_color',  'player_name', 'player_clan','resources']);

    $values = [];
    $k =0;
    $forceAllBlack = Globals::isExpansionClansDraft();
    foreach ($players as $pId => $player) {
      $color = array_shift($colors);
      //Force BLACK at setup if color is selected by players !
      if($forceAllBlack){
        $color = '000000';
        $player_clan = null;
      }
      else {
        $player_clan = CLANS_COLORS[$color];
      }

      //Set first resources
      $initialMoney = $k + 7;
      $initialResources = [
        RESOURCE_TYPE_SILK => 1,
        RESOURCE_TYPE_POTTERY => 1,
        RESOURCE_TYPE_RICE => 1,
        RESOURCE_TYPE_MOON => 3,
        RESOURCE_TYPE_SUN => 2,
        RESOURCE_TYPE_MONEY => $initialMoney,
      ];

      $values[] = [$pId, $color, $player['player_name'], 
        $player_clan,
        json_encode($initialResources),
      ];
      $k++;
    }
    $query->values($values);

    $playersObjects = self::getAll();
    //Don't use player pref for color when color has POWER !, or disable it at all times to keep a correct 'player_clan' value
    if($gameInfos['favorite_colors_support'] && Globals::isExpansionClansDisabled()){
      Game::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);

      //Reload DAO 
      $playersObjects = self::getAll();
      foreach ($playersObjects as $pId => $player) {
        //$color = Players::getColor($pId);
        $color = $player->getColor(); 
        if(array_key_exists($color,CLANS_COLORS)){
          $player->setClan(CLANS_COLORS[$color] );
          Notifications::newPlayerColor($player);
        }
      }
    }
    Game::get()->reloadPlayersBasicInfos();
    
    return $playersObjects;
  }

  /**
   * @param Collection $players Players
   * @param int $turn
   */
  public static function setupNewTurn($players,$turn)
  {
    Game::get()->trace("setupNewTurn($turn)");

    $players = $players->filter(function ($player) { 
      return $player->getZombie() ==0 && $player->getEliminated() == 0;
    });
    
  }

  /**
   * @param int $pId
   * @param int $score score to add to current score
   */
  public static function incPlayerScore($pId, $score)
  {
    Game::get()->trace("incPlayerScore($pId, $score)");

    return self::DB()
      ->inc(['player_score' => $score])
      ->wherePlayer($pId)
      ->run();
  }
  
  public static function getUpdatedPlayerScore(int $pId) : int
  {
    Game::get()->trace("getUpdatedPlayerScore($pId)");

    return self::DB()
      ->select(['player_score'])
      ->wherePlayer($pId)
      ->get()->first()
      ->getScore();
  }

  public static function getActiveId()
  {
    return Game::get()->getActivePlayerId();
  }

  public static function getCurrentId($bReturnNullIfNotLogged = false)
  {
    return (int) Game::get()->getCurrentPId($bReturnNullIfNotLogged);
  }

  public static function getAll()
  {
    return self::DB()->get(false);
  }

  /*
   * get : returns the Player object for the given player ID
   */
  public static function get($pId = null): Player
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  /**
   * @return Player
   */
  public static function getActive()
  {
    return self::get();
  }

  /**
   * @return Player
   */
  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public static function getNextId($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return $table[$pId];
  }
  
  /**
   * @param int $player_id
   * @return Player
   */
  public static function getNextPlayerNotEliminated($player_id)
  {
    $nextPlayer_id = Players::getNextId($player_id);
    $nextPlayer = Players::get($nextPlayer_id);
    if(isset($nextPlayer) 
      && $nextPlayer->getZombie() != 1 && $nextPlayer->getEliminated() == 0
    ){
      return $nextPlayer;
    }
    return self::getNextPlayerNotEliminated($nextPlayer_id);
  }
  
  /**
   * @param int $player_id
   * @return Player (null if no player match, looking at this player first)
   */
  public static function getNextPlayerWithBonusToChoose($player_id)
  {
    $nextPlayerTable = Game::get()->getNextPlayerTable();
    $k=0;
    $current_id = null;
    while($k<count($nextPlayerTable )){
      if(!isset($current_id)){
        //Start by looking at current player
        $nextPlayer_id = $player_id;
      }
      else {
        $nextPlayer_id = $nextPlayerTable[$current_id];
      }
      $nextPlayer = Players::get($nextPlayer_id);
      if(isset($nextPlayer) 
        && $nextPlayer->getZombie() != 1 && $nextPlayer->getEliminated() == 0
      ){
        $bonuses = $nextPlayer->getBonuses();
        if(isset($bonuses) && count($bonuses)>0) return $nextPlayer;
      }
      //ELSE continue loop
      $current_id = $nextPlayer_id;
      $k++;
    }
    return null;
  }

  /**
   * @param int $pId
   * @return String color : the up-to date player color
   */
  public static function getColor($pId)
  {
    return self::DB()->wherePlayer($pId)
      ->get()
      ->first()->getPlayerColor();
  }

  /*
   * Return the number of players
   */
  public static function count()
  {
    return self::DB()->count();
  }

  /*
   * getUiData : get all ui data of all players
   */
  public static function getUiData($pId)
  {
    return self::getAll()
      ->map(function ($player) use ($pId) {
        return $player->getUiData($pId);
      })
      ->toAssoc();
  }

  /**
   * Get current turn order according to first player variable
   */
  public static function getTurnOrder($firstPlayer = null)
  {
    $firstPlayer = $firstPlayer ?? Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = self::getNextId($p);
    } while ($p != $firstPlayer);
    return $order;
  }

  /**
   * This allow to change active player
   */
  public static function changeActive($pId)
  {
    Game::get()->gamestate->changeActivePlayer($pId);
  }

  /**
   * Sets player datas related to turn number $turn
   * @param array $player_ids
   * @param int $turn
   */
  public static function startTurn($player_ids,$turn)
  {
    foreach($player_ids as $player_id){
      $player = self::get($player_id);
      $player->startTurn($turn);
    }
  }
  
  /**
   * @param Player $player 
   * @param int $money 
   * @param int $fromShoreSpace (Optional)
   */
  public static function giveMoney(&$player,$money, $fromShoreSpace = null){
    $pId = $player->getId();
    //self::DB()->inc(['money' => $money], $pId);
    $player->giveResourceFromShoreSpace($money,RESOURCE_TYPE_MONEY,$fromShoreSpace);
    //Notifications::giveMoney($player,$money);
  }
  
  /**
   * @param Player $player 
   * @param int $money 
   */
  public static function spendMoney($player,$money){
    $pId = $player->getId();
    if($player->getMoney() < $money){
      //Should not happen
      throw new UnexpectedException(404,"Not enough money to spend");
    }
    //self::DB()->inc(['money' => 0-$money], $pId);
    $player->giveResource(0-$money,RESOURCE_TYPE_MONEY,false);
    Notifications::spendMoney($player,$money);
  }
  
  /**
   * @param Player $player  (! will be modified with score and resources)
   * @param int $region 
   * @param int $amount 
   * @param ClanPatronCard $influencePatron (optional) clan patron that leads to this gain
   * @return bool true if player needs to do another choice  (! Currently not updated if recursive calls ! may return false even if a embedded call makes it true) -> read the current bonuses after the calls if you need to know whether a bonus choice is there or not
   */
  public static function gainInfluence(Player &$player,int $region,int $amount,?ClanPatronCard $influencePatron = null){
    $pid = $player->getId();
    Game::get()->trace("gainInfluence($pid, $region,$amount),". json_encode($influencePatron));
    if($amount == 0) return;
    $meeple = Meeples::getInfluenceMarker($pid,$region);
    $currentInfluence = $meeple->getPosition(); 
    $newInfluence = min(NB_MAX_INLFUENCE,$currentInfluence + $amount);
    $meeple->setPosition($newInfluence);
    Notifications::gainInfluence($player,$region,$amount,$newInfluence,$meeple,$influencePatron);

    //////////////////////////////////////////////////////////////////
    //influence depends on Scorpion clan patron
    $playerPatron = $player->getPatron();
    //Lady of Whispers jump on other used spaces, so it is +1 move per used space
    if(isset($playerPatron) && PATRON_LADY == $playerPatron->getType()){
      $betterPlayersSpacesOnPath = Meeples::countUsedSpacedOnInfluenceTrack($player->getId(),$region,$currentInfluence,$amount);
      $amount2 = $betterPlayersSpacesOnPath;
      if($amount2>0) {
        //Recursive CALL ! because we want a cascade triggering the same gains
        Players::gainInfluence($player,$region,$amount2,$playerPatron);
      }
    }
    //If another player has the Governor, THEY get 3 points when we pass them
    $patronGovernor = Cards::getAssignedPatron(PATRON_GOVERNOR);
    if(isset($patronGovernor) && $patronGovernor->getPId()!= $player->getId() ){
      //CHECK this is ANOTHER PLAYER
      $playerGovernor = Players::get($patronGovernor->getPId());
      $governorInfluence = $playerGovernor->getInfluence($region);
      if($governorInfluence >= 1 && $governorInfluence >= $currentInfluence && $governorInfluence < $newInfluence){
        $playerGovernor->addPoints(NB_POINTS_GOVERNOR,false);
        Notifications::scorePatron($playerGovernor,NB_POINTS_GOVERNOR,$patronGovernor);
      }
    }
    //////////////////////////////////////////////////////////////////
    
    //Earn bonus on track :
    $goToBonusChoice = false;
    $bonuses = Utils::getInfluenceTrackRewards($region);
    foreach($bonuses as $influence => $bonusList){ foreach($bonusList as $bonus){
      if($influence > $currentInfluence && $influence<=$newInfluence){
        //if this position is new, let's win bonus
        $bonusQuantity = $bonus['n'];
        $bonusType = $bonus['type'];
        if(BONUS_TYPE_POINTS == $bonusType){
          $player->addPoints($bonusQuantity);
        }
        else if(in_array($bonusType,[
            BONUS_TYPE_INF_SELECT_REGION,
            BONUS_TYPE_MULTITRADE_2,
            BONUS_TYPE_MULTITRADE_3,
          ])
        ){
          Globals::addBonusWithDatas($player,$bonusType,['region'=>$region,'bonusQuantity'=>$bonusQuantity]);
          $goToBonusChoice = true;
        }
        else if(in_array($bonusType,[
            BONUS_TYPE_CHOICE,
          ])
        ){
          Globals::addBonus($player,$bonusType);
          $goToBonusChoice = true;
        }
        else {
          $player->giveResource($bonusQuantity,$bonusType);
        }
      }
    }}
    return $goToBonusChoice;
  }
  
  /**
   * @param Player $player 
   * @param int $amount 
   */
  public static function gainDivineFavor(&$player,$amount){
    if($amount == 0) return;
    $current = $player->getResource(RESOURCE_TYPE_SUN); 
    $max = $player->getResource(RESOURCE_TYPE_MOON); 
    $new = min($max,$current + $amount);
    $player->giveResource($new - $current,RESOURCE_TYPE_SUN);

  }
  
  public static function claimNotScoreMasteries(Player &$player){
    Game::get()->trace("claimNotScoreMasteries()");
    $masteriesTypesAboutScore = [MASTERY_TYPE_LIGHTNING];
    Players::claimMasteriesSubset($player,$masteriesTypesAboutScore,false);
  }
  public static function claimScoreMasteries(Player &$player){
    Game::get()->trace("claimScoreMasteries()");
    $masteriesTypesAboutScore = [MASTERY_TYPE_LIGHTNING];
    Players::claimMasteriesSubset($player,$masteriesTypesAboutScore,true);
  }

  /**
   * @param Player $player
   * @param array $masteriesTypes : list of mastery tiles types
   * @param bool $include (default true) : filter on given types or on different types
   * 
   */
  public static function claimMasteriesSubset(Player &$player, array $masteriesTypes, bool $include = true){
    Game::get()->trace("claimMasteriesSubset($include)...".json_encode($masteriesTypes));
    if($include && count($masteriesTypes) <1) return ;
    
    $masteryCards = Tiles::getMasteryToClaim()
                    ->merge(Tiles::getMasteryReserved($player));
    foreach ($masteryCards as $tile) {
      if($include && !in_array($tile->scoringType,$masteriesTypes)){
        continue;
      }
      if(!$include && in_array($tile->scoringType,$masteriesTypes)){
        continue;
      }
      self::claimMastery($player,$tile);
    }
  }
  /**
   * check each mastery card to check if player can claim it
   * @param Player $player 
   */
  public static function claimMasteries(Player &$player){
    Game::get()->trace("claimMasteries()");
    Players::claimNotScoreMasteries($player);
    Players::claimScoreMasteries($player);
  }
  
  /**
   * check this mastery card to check if player can claim it
   * @param Player $player 
   * @param MasteryCard $tile 
   */
  public static function claimMastery(Player &$player, MasteryCard $tile){
    $pId = $player->getId();
    $tileId = $tile->getId();
    Game::get()->trace("claimMastery($pId, $tileId)"); 

    $clanMarkers = $tile->getMeeples();
    $claimGains = null;
    $playerPatron = $player->getPatron();
    if(isset($playerPatron) && PATRON_SCION_OF_EARTH == $playerPatron->getType()){
      $claimGains = SCION_OF_EARTH_GAINS;
    }

    foreach($clanMarkers as $clanMarker){
      //if already claimed by this player, exit
      if($clanMarker->getPId() == $pId) return;
    }
    
    //if already claimed by others, exit
    $nbClaimed = count($clanMarkers);
    if($nbClaimed >= count($tile->scores) ){
      if(!isset($claimGains)) return;
      //else if there are claimGains we need to check claim anyway
    }

    $claim = false;
    switch($tile->scoringType){
      //----------------------------------------------------------------------
      case MASTERY_TYPE_AIR: //1 of 3 customers
        $countCustomers = 0;
        foreach (ALL_CUSTOMER_TYPES as $customer){
          if($player->getNbDeliveredCustomerByType($customer) >= 1) $countCustomers++;
          if($countCustomers >= NB_CUSTOMERS_FOR_AIR){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_FIRE: //2 of same customer
        foreach (ALL_CUSTOMER_TYPES as $customer){
          if($player->getNbDeliveredCustomerByType($customer) >= NB_CUSTOMERS_FOR_FIRE){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_COURTS://flower in 1 region
        foreach (REGIONS as $region){
          if($player->getInfluence($region) >= NB_INLUENCE_FLOWER){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_WATER: //1 of each building
        $claim = true;
        foreach (BUILDING_TYPES as $bType){
          if(Meeples::countPlayerBuildings($player->getId(),$bType) < 1){
            $claim = false;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_VOID://influence in each region
        $claim = true;
        foreach (REGIONS as $region){
          if($player->getInfluence($region) < NB_INLUENCE_VOID){
            $claim = false;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_EARTH: //3 of 1 building
        foreach (BUILDING_TYPES as $bType){
          if(Meeples::countPlayerBuildings($player->getId(),$bType) >= NB_BUILDINGS_WATER){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_WAVES: //7 unique river spaces near player buildings
        $shoreSpaces = Tiles::getPlayerBuildingTilesShoreSpace($pId);
        $riverSpaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($shoreSpaces);
        if(count($riverSpaces) >= NB_SPACES_FOR_MASTERY_WAVES){
          $claim = true;
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_SUN_MOON: //>=5 favor
        if($player->getResource(RESOURCE_TYPE_SUN) >= NB_FAVOR_FOR_MASTERY_SUN_MOON){
          $claim = true;
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_LIGHTNING: // score >= 30 points
        $playerScore = Players::getUpdatedPlayerScore($pId);
        if($playerScore >= NB_POINTS_FOR_MASTERY_LIGHTNING){
          $claim = true;
        }
        else {
          Game::get()->trace("claimMastery(MASTERY_TYPE_LIGHTNING) score $playerScore is not enough "); 
        }
        break;
      //----------------------------------------------------------------------
    }

    if($claim){
      $claimPosition = $nbClaimed + 1;
      if($nbClaimed >= count($tile->scores) ){//if no more spaces available
        $nextPlaceScore = 0;
      } else {//default 
        $nextPlaceScore = $tile->scores[$claimPosition - 1];
      }
      $meeple = Meeples::addClanMarkerOnMasteryCard($tile,$player,$claimPosition);
      $player->addPoints($nextPlaceScore,false);
      Notifications::claimMasteryCard($player,$nextPlaceScore,$tile,$meeple);
      Players::claimBonus($player, $claimGains, $playerPatron);
    }

  }
  
  public static function claimBonus(Player &$player, ?array $claimGains, ?ClanPatronCard $playerPatron,){
    if(!isset($claimGains)) return;
    if(isset($playerPatron)){
      Notifications::activePatron($player,$playerPatron);
    }

    $checkOtherMasteries = [];
    foreach($claimGains as $type => $amount){
      switch($type){
        case RESOURCE_TYPE_MOON: 
        case RESOURCE_TYPE_SUN: 
          $player->giveResource($amount,$type);
          $checkOtherMasteries = [MASTERY_TYPE_SUN_MOON];
          break;
        case BONUS_TYPE_POINTS:
          $player->addPoints($amount);
          break;
      }
    }
    Players::claimMasteriesSubset($player,$checkOtherMasteries);
  }
  
  /**
   * @return bool true when every expected player has played the last turn
   */
  public static function everyOnePlayedLastTurn(){
    return self::countRemainingPlayers() == 0;
  }
  
  /**
   * @return int number of players we expect to play a turn
   */
  public static function countRemainingPlayers(){
    $players = Players::getAll();
    $counter = 0;
    foreach($players as $player){
      if($player->getZombie() == 1 || $player->getEliminated() == 1) continue;
      if(!$player->isLastTurnPlayed()) $counter++;
    }
    return $counter;
  }
}

