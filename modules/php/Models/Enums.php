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