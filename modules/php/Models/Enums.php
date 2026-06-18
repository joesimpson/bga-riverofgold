<?php

namespace ROG\Models;

use ROG\Helpers\EnumUtilsTrait;

enum MAIN_ACTION: string
{
  use EnumUtilsTrait;
  
  case BUILD      = 'build';
  case SAIL       = 'sail';
  case DELIVER    = 'deliver';
}

/**
 * Actions to be played before MAIN_ACTION 
 */
enum BEFORE_ACTION: string
{
  use EnumUtilsTrait;
  
  case SWAP_BOATS     = 'SWAP_BOATS';
  case MOVE_BUILDING  = 'MOVE_BUILDING';
}

enum TURN_ACTION: string
{
  use EnumUtilsTrait;
  
  case DIVINE_CYCLING     = 'DIVINE_CYCLING';
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