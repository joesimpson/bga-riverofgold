<?php

/**
 *------
  * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * RiverOfGold implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * River of Gold game options description
 * 
 * NB : 11/2023 new JSON format you can generate it from this file with PHP : 
 * call the debug function from chat :
 *    debugJSON()
 *
 */

namespace ROG;

//if placed at root folder
//require_once 'modules/php/constants.inc.php';
//Else near constants :
require_once 'constants.inc.php';

$game_options = [

  OPTION_EXPANSION_CLANS => array(
    'name' => 'Clans Patrons Mini Expansion',    
    'values' => [
      OPTION_EXPANSION_CLANS_OFF => [
        'name' => 'Disabled', 
        'description' => 'No specific clans for players', 
      ],
      OPTION_EXPANSION_CLANS_ON => [
        'name' => 'Enabled', 
        'description' => 'Clans Patrons give powerful new unique abilities to each clan', 
        'tmdisplay' => 'Clans Patrons',
      ],
    ],
    'default' => OPTION_EXPANSION_CLANS_OFF,
     
  ), 

];


$game_preferences = [

  PREF_PLAYER_PANEL_DETAILS => [
    'name' => totranslate('Player panel details'),
    'needReload' => false,
    'values' => [
      PREF_PLAYER_PANEL_DETAILS_FULL => [ 'name' => totranslate('Detailed') ],
      PREF_PLAYER_PANEL_DETAILS_COMPACT => [ 'name' => totranslate('Compact')],
    ],
    "default"=> PREF_PLAYER_PANEL_DETAILS_FULL,
    'attribute' => 'rog_panel_details',
  ],
];
