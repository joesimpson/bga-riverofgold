<?php

namespace ROG\Models;

use ROG\Helpers\EnumUtilsTrait;

enum MAIN_ACTION: string
{
  use EnumUtilsTrait;
  
  case BUILD      = 'build';
  case SAIL       = 'sail';
  case DELIVER    = 'deliver';
  case ADVANCE    = 'advance';
}

/**
 * Actions to be played before MAIN_ACTION 
 */
enum BEFORE_ACTION: string
{
  use EnumUtilsTrait;
  
  case SWAP_BOATS     = 'SWAP_BOATS';
  case MOVE_BUILDING  = 'MOVE_BUILDING';
  case GAIN_INFLUENCE     = 'B_GAIN_INFLUENCE';
  case TRADE_FOR_RESOURCES= 'TRADE_FOR_RESOURCES';
  case BUILDING_REWARD    = 'B_BUILDING_REWARD';
}

enum TURN_ACTION: string
{
  use EnumUtilsTrait;
  
  case DIVINE_CYCLING     = 'DIVINE_CYCLING';
}

enum AFTER_ACTION: string
{
  use EnumUtilsTrait;
  
  case GAIN_INFLUENCE     = 'A_GAIN_INFLUENCE';
  case BUILDING_REWARD    = 'A_BUILDING_REWARD';
}

enum AutomaActionType: int
{
  use EnumUtilsTrait;
  
  case SAIL_HIGHER    = 1;
  case SAIL_LOWER     = 2;
  case DELIVER        = 3;
  case BUILD          = 4;
  case ADVANCE_CITY   = 5;

}

enum ScenarioType: int
{
  use EnumUtilsTrait;

  case CRAB_1      = 1;
  case MANTIS_1    = 2;
  case CRANE_1     = 3;
  case SCORPION_1  = 4;
  case PHOENIX_1   = 5;
  case LION_1      = 6;
  case DRAGON_1    = 7;
  case UNICORN_1   = 8;

}

enum SHORE_SIDE: string
{
  use EnumUtilsTrait;

  case LEFT   = 'LEFT';
  case RIGHT  = 'RIGHT';
}

enum SCORING_CITY_TYPE: string
{
  use EnumUtilsTrait;

  case BUILDING        = 'BUILDING';
  case TRADE_GOOD      = 'TRADE_GOOD';
  case PORT            = 'PORT';
  case NOTHING         = '';
  case MARKET          = 'MARKET';
  case IMPERIAL_FLOWER = 'IMPERIAL_FLOWER';
  case SHRINE          = 'SHRINE';
  case SUN             = 'SUN';
  case MONEY           = 'MONEY';
  case DELIVERIES      = 'DELIVERIES';
  case MANOR           = 'MANOR';
  case MASTERIES       = 'MASTERIES';
}

enum CITY_CARD_TYPE: int
{
  use EnumUtilsTrait;

  case BRIBERY        = 1;
  case OFFLOAD        = 2;
  case BLACK_MARKET   = 3;
  case SHARED_CLI     = 4;
  case SHARED_ENG     = 5;
  case SHARED_ENV     = 6;
  case CARTEL         = 7;
  
  case SUMMONS        = 8;
  case FULL_STOR      = 9;
  case KIMONO_DRESS   = 10;
  case NIGHT_MARKET   = 11;
  case CALL_TO_PORT   = 12;
  case SHRINE_PIL     = 13;
  case OPPORTUNIST    = 14;
  case TEAHOUSE       = 15;
  case SAKE_BREW      = 16;
  case TRAVEL_TRO     = 17;
}

enum CITY_CARD_EFFECT: string
{
  use EnumUtilsTrait;

  case REVEAL    = 'REVEAL';
  case PREDICT   = 'PREDICT';
  case END       = 'END';
}

enum BonusBuildingRewardChoice: int
{
  use EnumUtilsTrait;

  case OWNER     = 1;
  case VISITOR   = 2;
}