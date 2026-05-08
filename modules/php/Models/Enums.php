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