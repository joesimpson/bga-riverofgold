<?php

namespace ROG\Models;

use ROG\Managers\CitySpaces;
use ROG\Managers\Meeples;

class ScoringCityTile extends Tile
{
  
  protected $staticAttributes = [
    ['region', 'int'],
    ['scoreByElement', 'int'],
    ['scoredElement', 'int'],
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  }

  public function getUiData() : array
  {
    $data = parent::getUiData();
    $data['subtype'] = TILE_TYPE_CITY_SCORING;
    unset($data['pId']);
    $data['pos'] = $this->getState();
    unset($data['state']);
    return $data;
  }
  
  public function getCitySpace() : ?CityTileSpace
  {
    return CitySpaces::getCitySpace($this->getState(), CITY_COLUMN_TILES);
  }
  
  public function computeScore(Player $player) : int
  {

    $score = 0;
    $base = $this->getScoreByElement();
    switch($this->getScoredElement()){
      case SCORING_CITY_TYPE::BUILDING->value:
        $score = $base * Meeples::countPlayerBuildings($player->getId());
        break;
      case SCORING_CITY_TYPE::TRADE_GOOD     ->value: 
        $nbGoods = $player->getResource(RESOURCE_TYPE_SILK) 
                 + $player->getResource(RESOURCE_TYPE_RICE)
                 + $player->getResource(RESOURCE_TYPE_POTTERY);
        $score = $base * $nbGoods;
        break;
      case SCORING_CITY_TYPE::PORT           ->value: 
        $score = $base * Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_PORT);
        break;
      case SCORING_CITY_TYPE::NOTHING        ->value: 
        $score = $base;
        break;
      case SCORING_CITY_TYPE::MARKET         ->value: 
        $score = $base * Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MARKET);
        break;
      case SCORING_CITY_TYPE::IMPERIAL_FLOWER->value: 
        // 4 points for each imperial flower they have reached on influence tracks
        foreach (REGIONS as $region){
          if($player->getInfluence($region) >= NB_INLUENCE_FLOWER){
            $score += $base;
          }
        }
        break;
      case SCORING_CITY_TYPE::SHRINE         ->value:
        $score = $base * Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_SHRINE);
        break;
      case SCORING_CITY_TYPE::SUN            ->value: 
        $score = $base * $player->getResource(RESOURCE_TYPE_SUN);
        break;
      case SCORING_CITY_TYPE::MONEY          ->value: 
        $nbKokuLots = intval( $player->getResource(RESOURCE_TYPE_MONEY) / 5 );
        $score = $base * $nbKokuLots;
        break;
      case SCORING_CITY_TYPE::DELIVERIES     ->value: 
        $score = $base * $player->getNbDeliveredCustomers();
        break;
      case SCORING_CITY_TYPE::MANOR          ->value:
        $score = $base * Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MANOR);
        break;
      case SCORING_CITY_TYPE::MASTERIES      ->value: 
        $score = $base * Meeples::countPlayerMasteries($player->getId());
        break;
    }
    $player->addPoints($score);
    return $score;
  }
}
