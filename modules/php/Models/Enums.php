<?php

namespace ROG\Models;

enum MAIN_ACTION: string
{
  case BUILD      = 'build';
  case SAIL       = 'sail';
  case DELIVER    = 'deliver';
}
