<?php

namespace ROG\Models;

enum MAIN_ACTION: string
{
  case BUILD      = 'build';
  case SAIL       = 'sail';
  case DELIVER    = 'deliver';
}

/**
 * Actions to be played before MAIN_ACTION 
 */
enum BEFORE_ACTION: string
{
  case SWAP_BOATS     = 'SWAP_BOATS';
  case MOVE_BUILDING  = 'MOVE_BUILDING';
}

enum AutomaActionType: int
{
  case SAIL_HIGHER    = 1;
  case SAIL_LOWER     = 2;
  case DELIVER        = 3;
  case BUILD          = 4;
  case ADVANCE_CITY   = 5;

}

enum ScenarioType: int
{
  case CRAB_1      = 1;
  case MANTIS_1    = 2;
  case CRANE_1     = 3;
  case SCORPION_1  = 4;
  case PHOENIX_1   = 5;
  case LION_1      = 6;
  case DRAGON_1    = 7;
  case UNICORN_1   = 8;

}