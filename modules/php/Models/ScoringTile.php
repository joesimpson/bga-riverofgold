<?php

namespace ROG\Models;

use ROG\Core\Game;

/*
 * ScoringTile: all utility functions concerning a scoring tile
 */

class ScoringTile extends Tile
{
  
  protected $staticAttributes = [
    ['nbPlayers', 'obj'],
    ['scores', 'obj'],
    //Will be null (empty array in Model) or an int :
    ['checkSpacesBetween', 'obj'],
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  }
  

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['pos'] = $this->getRegion();
    $data['subtype'] = TILE_TYPE_SCORING;
    $data['maxSpaces'] = $this->getMaxSpaces();
    unset($data['checkSpacesBetween']);
    unset($data['pId']);
    unset($data['state']);
    unset($data['nbPlayers']);
    return $data;
  }
  
  /**
   * @return ?int
   */
  public function getMaxSpaces()
  {
    $maxSpaces = $this->getCheckSpacesBetween();
    if(!isset($maxSpaces)) return null;
    if(is_int($maxSpaces)) return $maxSpaces;
    return null;
  }

  /**
   * @return int
   */
  public function getRegion()
  {
    //state will be in [0,1,2,3,4,5] after shuffle :
    return $this->getState() +1;
  }
  /**
   * @param int $playerPosition to compare to others
   * @param array $opponentPositions
   * @return int score for this player
   */
  public function computeScore($playerPosition,$opponentPositions)
  {
    Game::get()->trace("ScoringTile.computeScore() : $playerPosition, ".json_encode($opponentPositions));
    $nbBetterPositions = 0;
    $nbSamePositions = 0;
    $bestOpponent = 0;

    //Minimum influence to score : 1
    if($playerPosition < 1 ) return 0;

    foreach($opponentPositions as $opponentPosition){
      if($opponentPosition > $playerPosition){
        $nbBetterPositions++;
        $bestOpponent = max($bestOpponent, $opponentPosition);
      }
      else if($opponentPosition == $playerPosition) $nbSamePositions++;
    }
    
    $scoresToGive = $this->getScores();
    if($nbBetterPositions > count($scoresToGive) ) return 0;

    //RULE : 2 Player regional Tiles
    $maxSpaces = $this->getMaxSpaces();
    $assignSecondPlace = ( !isset($maxSpaces)
       || ($bestOpponent - $playerPosition) <= $maxSpaces
      );
    Game::get()->trace("ScoringTile.computeScore()...maxSpaces=".json_encode($maxSpaces).", assignSecondPlace=".json_encode($assignSecondPlace));

    if($nbSamePositions == 0){
      //LOOK FOR EACH Scored position matching lonely player 
      foreach($scoresToGive as $scorePos => $score){
        if($nbBetterPositions == $scorePos){
          if(!$assignSecondPlace && $scorePos ==1) break;
          return $score;
        }
      }
    }
    else {
      //Some places are tied
      $tieScoreTotal = 0;
      $tiedPositions = 0;
      $nbPositionsToSplit = $nbSamePositions +1;
      foreach($scoresToGive as $scorePos => $score){
        //Sart looking after better places :
        if($nbBetterPositions > $scorePos) continue;
        //Stop looking after enough tied positions :
        if($tiedPositions >= $nbPositionsToSplit ) break;
        $tieScoreTotal += $score;
        $tiedPositions++;
      }
      return round($tieScoreTotal / $nbPositionsToSplit,0,PHP_ROUND_HALF_DOWN);
    }

    return 0;
  }
}
